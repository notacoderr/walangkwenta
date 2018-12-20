<?php

declare(strict_types=1);

namespace PCPCore;

use PCPCore\Core;

use pocketmine\utils\TextFormat;
use pocketmine\entity\Living;
use pocketmine\event\Listener;
use pocketmine\event\entity\{
    EntityDamageByEntityEvent, EntityDamageEvent, EntityMotionEvent, EntitySpawnEvent
};
use pocketmine\event\player\{
	PlayerChatEvent, PlayerDeathEvent, PlayerInteractEvent, PlayerItemHeldEvent, PlayerRespawnEvent
};

use pocketmine\event\block\BlockBreakEvent;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\scheduler\Task;

use pocketmine\entity\Entity;

use pocketmine\block\Stair;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;


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
		switch( $event->getBlock()->getId() )
		{
			case 1: case 4:
				//dito mo lagay yung sa MythicRelic mo. 1 ata stone tas 4 cobble, di ko matandaan
			break;
			default:
			if($event->getPlayer()->getGamemode != 1) //if not creative mode
			{
				$blockid = $event->getBlock()->getId();
				if(array_key_exists($blockid, $this->plugin->premyo->getNested("breakmoney")))
				{
					$pr = explode( "-", $this->plugin->premyo->getNested("breakmoney." . $blockid) );
					Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($event->getPlayer(), mt_random($pr[0], $pr[1]))
				}
			}
		}
   	}
	
    /*public function onMotion(EntityMotionEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof Living && !$entity instanceof Player){
            $event->setCancelled(true);
        }
    }*/

    public function onRespawn(PlayerRespawnEvent $event) : void{
        $player = $event->getPlayer();
        $title = "§l§cYOU DIED!";
        $subtitle = "§aRespawning...";
        $player->addTitle($title, $subtitle);
    }

	public function onHeld(PlayerItemHeldEvent $ev){
    	$item = $ev->getItem();
    	$player = $ev->getPlayer();
    	if($item->getId() == Item::SKULL){
    		if(isset(Core::MASK_DAMAGE_TO_NAME[$item->getDamage()])){
				$player->sendPopup(Core::MASK_DAMAGE_TO_NAME[$item->getDamage()]);
			}
		}elseif($item->getId() == Item::ENCHANTED_BOOK && $item->getDamage() == 101){
			$player->sendPopup(TextFormat::RESET . TextFormat::YELLOW . "Mask Charm");
		}
	}
	
	public function onDeath(PlayerDeathEvent $ev){
    	$p = $ev->getPlayer();
    	$k = $ev->getPlayer()->getLastDamageCause();
    	if($k instanceof EntityDamageByEntityEvent){
			$k = $k->getDamager();
    		if($k instanceof Player){
    			$head = Item::get(Item::SKULL, mt_rand(50, 100), 1);
    			$head->setCustomName(TextFormat::RESET . TextFormat::AQUA . $p->getName() . "'s Head");
    			$nbt = $head->getNamedTag();
    			$nbt->setString("head", strtolower($p->getName()));
    			$head->setNamedTag($nbt);
    			$k->getInventory()->addItem($head);
				$k->sendMessage("§l§8(§b!§8)" . TextFormat::RESET . TextFormat::GRAY . " You have obtained " . TextFormat::AQUA . $p->getName() . "'s Head.");
				
                 /*$light = new AddEntityPacket();
                 $light->type = 93;
                 $light->eid = Entity::$entityCount++;
                 $light->metadata = array();
                 $light->speedX = 0;
                 $light->speedY = 0;
                 $light->speedZ = 0;
                 $light->x = $p->x;
                 $light->y = $p->y;
                 $light->z = $p->z;
                 $p->dataPacket($light);*/
      
           }
		}
	}
}
