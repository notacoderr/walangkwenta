<?php

declare(strict_types=1);

namespace PCPCore\tasks;

use PCPCore\Core;
use pocketmine\scheduler\Task;

class BroadcastTask extends Task{
    
    /** @var Core */
    private $plugin;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
    }
    
    public function onRun(int $currentTick){
        $messages = $this->plugin->settings->get("messages");
        $messages = $messages[array_rand($messages)];
        $message = "$messages";
        $message = str_replace("&", "§", $message);
        $message = str_replace("{MAX_PLAYERS}", $this->plugin->getServer()->getMaxPlayers(), $message);
        $message = str_replace("{ONLINE}", count($this->plugin->getServer()->getOnlinePlayers()), $message);
        $this->plugin->getServer()->broadcastMessage($message);
    }
}
