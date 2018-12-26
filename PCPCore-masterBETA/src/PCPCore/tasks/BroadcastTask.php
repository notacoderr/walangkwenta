<?php

declare(strict_types=1);

namespace CorePCP\tasks;

use CorePCP\Core;
use pocketmine\scheduler\Task;
use pocketmine\Player;

class BroadcastTask extends Task{
    
    /** @var Core */
    private $plugin;

    public function __construct(Core $plugin, Player $player){
        $this->plugin = $plugin;
        $this->player = $player;
    }
    
    public function onRun(int $currentTick){
        $messages = $this->plugin->settings->get("messages");
        $messages = $messages[array_rand($messages)];
        $message = "$messages";
        $message = str_replace("&", "§", $message);
        $message = str_replace("{max_players}", $this->plugin->getServer()->getMaxPlayers(), $message);
        $message = str_replace("{online}", count($this->plugin->getServer()->getOnlinePlayers()), $message);
        $message = str_replace("{player}", $this->plugin->player->getName(), $message);
        $this->plugin->getServer()->broadcastMessage($message);
    }
}
