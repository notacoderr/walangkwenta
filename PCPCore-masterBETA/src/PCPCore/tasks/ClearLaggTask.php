<?php

declare(strict_types=1);

namespace CorePCP\tasks;

use CorePCP\Core;
use pocketmine\utils\TextFormat as TF;
use pocketmine\entity\Human;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ClearLaggTask extends Task{

    /** @var Core */
    private $plugin;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick) : void{
    	$c = 0;
        foreach(Server::getInstance()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
				if(!($entity instanceof Human)){
					$entity->close();
					$c++;
				}
        	}
		}
		Server::getInstance()->broadcastMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::AQUA . "!" . TF::DARK_GRAY . ")" . TF::RESET . TF::GRAY . " Just cleaning a mess, [" . TF::GOLD . $c . TF::GRAY . "] Entities");
    }
}
