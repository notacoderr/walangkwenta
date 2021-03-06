<?php

declare(strict_types=1);

namespace CorePCP;

use CorePCP\Core;

use pocketmine\utils\TextFormat as TF;
use pocketmine\entity\Living;
use pocketmine\event\Listener;
use pocketmine\event\entity\{
    EntityDamageByEntityEvent, EntityDamageEvent, EntityMotionEvent, EntitySpawnEvent
};
use pocketmine\event\player\{
	PlayerChatEvent, PlayerDeathEvent, PlayerInteractEvent, PlayerItemHeldEvent, PlayerRespawnEvent
};

use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\entity\Entity;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\utils\Random;

class EventListener implements Listener{

    /** @var Core */
    private $plugin;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
    }

    public function onChat(PlayerChatEvent $event) : void{
        $player = $event->getPlayer();
        if(!$player->isOp()){
			if(isset($this->plugin->chat[strtolower($player->getName())])){
				if((time() - $this->plugin->chat[strtolower($player->getName())]) < 5){
					$event->setCancelled();
					$player->sendMessage("§l(§c!§8)§r§7 Please wait before chatting again!");
				} else {
					$this->plugin->chat[strtolower($player->getName())] = time();
				}
			}else{
				$this->plugin->chat[strtolower($player->getName())] = time();
			}
		}
    }

    public function onInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        $item = $event->getItem();
	    
        if($item->getId() == Item::ENCHANTED_BOOK && $item->getDamage() == 101)
	{
		$item = Item::get(Item::SKULL, mt_rand(3, 10), 1);
		$item->setCustomName(Core::MASK_DAMAGE_TO_NAME[$item->getDamage()]);
		$item->setLore(Core::MASK_DAMAGE_TO_LORE[$item->getDamage()]);
		$ic = clone $event->getItem();
		$ic->setCount(1);
        	$player->getInventory()->removeItem($ic);
		$player->getInventory()->addItem($item);
		$player->addTitle(TF::GREEN . TF::BOLD . "Obtained", TF::YELLOW . Core::MASK_DAMAGE_TO_NAME[$item->getDamage()]);
        }

	if($item->getId() == 450 && $item->getDamage() == 69 && !$player->isCreative())
	{
		$this->plugin->relic->openRelic($player, $item);
		$player->getInventory()->setItemInHand(Item::get(0));
	}
    }
    
    public function onDamage(EntityDamageEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if(!$entity->isCreative() && $entity->getAllowFlight()){
                $entity->setFlying(false);
                $entity->setAllowFlight(false);
                $entity->sendMessage("§l§8(§c!§8)§r §cDisabled Flight since you're in combat.");
            }
        }
    }

	function onBreak(BlockBreakEvent $event) : void
	{
		if (!$event->getPlayer()->isCreative())
		{
			$blockid = $event->getBlock()->getId();
			$blockmeta = $event->getBlock()->getDamage();
			if(array_key_exists($blockid. "-". $blockmeta, $this->plugin->premyo->getNested("breakmoney")))
			{
				$pr = explode( "-", $this->plugin->premyo->getNested("breakmoney.". $blockid. "-". $blockmeta) );
				$min = (int) $pr[0];
				$max = (int) $pr[1];
				Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($event->getPlayer(), mt_rand($min, $max));
			}

			if(array_key_exists($blockid, $this->plugin->relicBlocks))
			{
				$chance = $this->plugin->relicBlocks[ $blockid ];
				$relic = $this->plugin->relic->foundRelic($event->getPlayer(), $chance);
				if($relic instanceof Item)
				{
					$arr = $event->getDrops();
					array_push($arr, $relic);
					$event->setDrops($arr);
				}
			}
		}
   	}

	function onRespawn(PlayerRespawnEvent $event) : void
	{
		$player = $event->getPlayer();
		$player->addTitle("§l§cYOU DIED!", "§aTry harder...");
	}
	
	function onHeld(PlayerItemHeldEvent $ev)
	{
		$item = $ev->getItem();
		$player = $ev->getPlayer();
		if($item->getId() == Item::SKULL)
		{
			if(isset(Core::MASK_DAMAGE_TO_NAME[$item->getDamage()]))
			{
				$player->sendPopup(Core::MASK_DAMAGE_TO_NAME[$item->getDamage()]);
			}
		}
		if($item->getId() == Item::ENCHANTED_BOOK && $item->getDamage() == 101)
		{
			$player->sendPopup(TF::RESET . TF::YELLOW . "Mask Charm");
		}
	}
	
	function onPlace(BlockPlaceEvent $event) : void
	{
    	}
	
	function onDeath(PlayerDeathEvent $ev) : void
	{
		$p = $ev->getPlayer();
		$k = $ev->getPlayer()->getLastDamageCause();
    		if($k instanceof EntityDamageByEntityEvent)
		{
			$k = $k->getDamager();
			if($k instanceof Player)
			{
				$head = Item::get(Item::SKULL, mt_rand(50, 100), 1);
				$head->setCustomName(TF::RESET . TF::AQUA . $p->getName() . "'s Head");
				$nbt = $head->getNamedTag();
				$nbt->setString("head", strtolower($p->getName()));
				$head->setNamedTag($nbt);
				$k->getInventory()->addItem($head);
				$k->sendMessage("§l§8(§b!§8)" . TF::RESET . TF::GRAY . " You have obtained " . TF::AQUA . $p->getName() . "'s Head");
				
				$eco = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
				$pm = explode( "-", $this->plugin->premyo->getNested("killmoney.victim") );
				$km = explode( "-", $this->plugin->premyo->getNested("killmoney.killer") );
				$eco->addMoney($k, mt_rand((int) $km[0], (int) $km[1])); //killer's added money (min-max)
				$eco->reduceMoney($p, mt_rand((int) $pm[0], (int) $pm[1])); //player/victim's reduced money
			}
		}
	}
}
