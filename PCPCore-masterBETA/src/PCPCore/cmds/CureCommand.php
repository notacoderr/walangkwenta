<?php

declare(strict_types=1);

namespace CorePCP\cmds;

use CorePCP\Core;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\Player;

class CureCommand extends PluginCommand{
    /** @var Core */
    private $plugin;
    
    public function __construct($name, Core $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Cure yourself");
        $this->plugin = $plugin;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) return false;
        if($sender->hasPermission("pcp.cure")){
            $sender->sendMessage("§l§8(a!§8)§r§7 You have been cured!");
            $sender->setHealth(20);
            $sender->setFood(20);
            }
        }else{
            $sender->sendMessage(Core::PERM_RANK);
            return false;
        }
        return true;
    }
}
