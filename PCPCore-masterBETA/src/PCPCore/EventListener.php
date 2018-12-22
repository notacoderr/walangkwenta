<?php

declare(strict_types=1);

namespace PCPCore;

use PCPCore\Core;

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

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\entity\Entity;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
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
					$player->sendMessage("§l§fP§bC§fP §8»§r §cPlease wait before chatting again!");
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
        switch($item->getId()){
		case Item::ENCHANTED_BOOK:
		if($item->getDamage() == 101){
			$item = Item::get(Item::SKULL, mt_rand(3, 10), 1);
			$item->setCustomName(Core::MASK_DAMAGE_TO_NAME[$item->getDamage()]);
			$item->setLore(Core::MASK_DAMAGE_TO_LORE[$item->getDamage()]);
			$ic = clone $event->getItem();
			$ic->setCount(1);
        	        $player->getInventory()->removeItem($ic);
			$player->getInventory()->addItem($item);
			$player->addTitle(TextFormat::GREEN . TextFormat::BOLD . "Obtained", TextFormat::YELLOW . Core::MASK_DAMAGE_TO_NAME[$item->getDamage()]);
		}
        }
    }
    
    public function onDamage(EntityDamageEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if(!$entity->isCreative() && $entity->getAllowFlight()){
                $entity->setFlying(false);
                $entity->setAllowFlight(false);
                $entity->sendMessage("§l§8(§b!§8)§r §cDisabled Flight since you're in combat.");
            }
        }
    }

	function onBreak(BlockBreakEvent $event) : void
	{
		if (! $event->getPlayer()->isCreative())
		{
			$blockid = $event->getBlock()->getId();
			$blockmeta = $event->getBlock()->getDamage();
			//This is for special stuffs like Relics
			switch( $blockid )
			{
				case 1:
					if(mt_rand(1, 10) <= 2)
					{
						     $name = $event->getPlayer()->getName();
						     $relic = Item::get(54, 101, 1);
						     $relic->setCustomName(TF::RESET . TF::WHITE . "Pocket" . TF::AQUA . " Artifact");
						     $event->getPlayer()->getInventory()->addItem($relic);
						     Server::getInstance()->broadcastMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::AQUA . "!" . TF::DARK_GRAY . ")" . TF::RESET . TF::AQUA .  " $name" . TF::GRAY . " Found a Pocket Artifact!");
					}
				break;
				/*
				case 4: id ata ng cobblestone
					if(mt_rand(1, 10) <= 4)
					{
						
					}
				break;
				*/
			}
			
			if(array_key_exists($blockid. "-". $blockmeta, $this->plugin->premyo->getNested("breakmoney")))
			{
				$pr = explode( "-", $this->plugin->premyo->getNested("breakmoney.". $blockid. "-". $blockmeta) );
				Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($event->getPlayer(), mt_random($pr[0], $pr[1]));
			}
		}
   	}

	function onRespawn(PlayerRespawnEvent $event) : void
	{
		$player = $event->getPlayer();
		$player->addTitle("§l§cYOU DIED!", "§aRespawning...");
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
			$player->sendPopup(TextFormat::RESET . TextFormat::YELLOW . "Mask Charm");
		}
	}
	
	function onTap(BlockPlaceEvent $event){
	$player = $event->getPlayer();
        $item = $event->getItem();
        $damage = $event->getItem()->getDamage();
        $prot = Enchantment::getEnchantment(0);
        $unb = Enchantment::getEnchantment(17);
        $sharp = Enchantment::getEnchantment(9);
        $eff = Enchantment::getEnchantment(15);
        $kb = Enchantment::getEnchantment(12);
        $loot = Enchantment::getEnchantment(14);
        $fire = Enchantment::getEnchantment(13);
        $resp = Enchantment::getEnchantment(6);
        switch($damage) {
            case "101":
            $relic = Item::get(54, 101, 1);
            $item1 = Item::get(310, 0, 1);
            $item1->setCustomName(TF::RESET . TF::LIGHT_PURPLE . "Adaptive" . TF::GRAY . " Helm");
            $item1->addEnchantment(new EnchantmentInstance($prot, 3));
            $item1->addEnchantment(new EnchantmentInstance($unb, 3));
            $item2 = Item::get(311, 0, 1);
            $item2->setCustomName(TF::RESET . TF::LIGHT_PURPLE . "Demons" . TF::GRAY . " Advent");
            $item2->addEnchantment(new EnchantmentInstance($prot, 3));
            $item2->addEnchantment(new EnchantmentInstance($unb, 3));
            $item3 = Item::get(312, 0, 1);
            $item3->setCustomName(TF::RESET . TF::LIGHT_PURPLE . "Ancient" . TF::GRAY . " Leggings");
            $item3->addEnchantment(new EnchantmentInstance($prot, 3));
            $item3->addEnchantment(new EnchantmentInstance($unb, 3));
            $item4 = Item::get(313, 0, 1);
            $item4->setCustomName(TF::RESET . TF::RED . "Rapid" . TF::GRAY . " Boots");
            $item4->addEnchantment(new EnchantmentInstance($prot, 3));
            $item4->addEnchantment(new EnchantmentInstance($unb, 3));
            $sword = Item::get(276, 0, 1);
            $sword->setCustomName(TF::RESET . TF::RED . "Flaming" . TF::GRAY . " Sword");
            $sword->addEnchantment(new EnchantmentInstance($sharp, 3));
            $sword->addEnchantment(new EnchantmentInstance($unb, 3));
	    $sword1 = Item::get(276, 0, 1);
            $sword1->setCustomName(TF::RESET . TF::RED . "The Bloodthirster");
            $sword1->addEnchantment(new EnchantmentInstance($sharp, 3));
            $sword1->addEnchantment(new EnchantmentInstance($unb, 3));
            $pickaxe = Item::get(278, 0, 1);
            $pickaxe->setCustomName(TF::RESET . TF::RED . "Giga" . TF::GRAY . " Drill");
            $pickaxe->addEnchantment(new EnchantmentInstance($eff, 3));
            $pickaxe->addEnchantment(new EnchantmentInstance($unb, 3));
            $axe = Item::get(279, 0, 1);
            $axe->setCustomName(TF::RESET . TF::RED . "Pyro" . TF::GRAY . " Axe");
            $axe->addEnchantment(new EnchantmentInstance($eff, 3));
            $axe->addEnchantment(new EnchantmentInstance($unb, 3));
            $diamond = Item::get(264, 0, 64);
            $iron = Item::get(265, 0, 256);
            $gold = Item::get(266, 0, 128);
            $tobegiven1 = [$item1, $item2, $item3, $item4, $sword, $pickaxe, $axe, $diamond, $iron, $gold, $sword1]; //array1
            $rand1 = mt_rand(0, 11);
            $player->getInventory()->addItem($tobegiven1[$rand1]);
            $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::AQUA . "!" . TF::DARK_GRAY . ")" . TF::RESET . TF::GRAY . " Opening Artifact...");
            $event->setCancelled();
            $player->getInventory()->removeItem($relic);
            break;
	}
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
				$head->setCustomName(TextFormat::RESET . TextFormat::AQUA . $p->getName() . "'s Head");
				$nbt = $head->getNamedTag();
				$nbt->setString("head", strtolower($p->getName()));
				$head->setNamedTag($nbt);
				$k->getInventory()->addItem($head);
				$k->sendMessage("§l§8(§b!§8)" . TextFormat::RESET . TextFormat::GRAY . " You have obtained " . TextFormat::AQUA . $p->getName() . "'s Head");
				
				$eco = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
				$pm = explode( "-", $this->plugin->premyo->getNested("killmoney.victim") );
				$km = explode( "-", $this->plugin->premyo->getNested("killmoney.killer") );
				$eco->addMoney($k, mt_rand($km[0], $km[1])); //killer's added money (min-max)
				$eco->reduceMoney($p, mt_rand($pm[0], $pm[1])); //player/victim's reduced money
			}
		}
	}
}
