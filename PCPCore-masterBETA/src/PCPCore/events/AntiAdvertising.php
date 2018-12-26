<?php

declare(strict_types=1);

namespace CorePCP\events;

use CorePCP\Core;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;

class AntiAdvertising implements Listener{

    /** @var Core */
    private $plugin;
    /** @var array */
    private $links;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
        $this->links = [".leet.cc", ".playmc.pe", ".net", ".com", ".us", ".co", ".co.uk", ".ddns", ".ddns.net", ".cf", ".pe", ".me", ".cc", ".ru", ".eu", ".tk", ".gq", ".ga", ".ml", ".org", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9"];
    }

    public function onChat(PlayerChatEvent $event) : void{
        $msg = $event->getMessage();
        $player = $event->getPlayer();
        if(!$player instanceof Player) return;
        if($player->hasPermission("pcp.anti.ads")){
        }else{
            foreach($this->links as $links){
                if(strpos($msg, $links) !== false){
                    $player->sendMessage("§l§8(§c!§8)§r §7Do not §cAdvertise, §7or you might get banned!");
                    $event->setCancelled();
                    return;
                }
            }
        }
    }
}
