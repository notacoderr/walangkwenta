<?php

declare(strict_types=1);

namespace PCPCore\cmds;

use PCPCore\Core;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\Player;

class TagCommand extends PluginCommand{

    /** @var Core */
    private $plugin;

    public function __construct($name, Core $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Change your nametag");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) return false;
        if($sender->hasPermission("pcp.tag")){
            if(isset($args[0])){
                if($args[0] == "off"){
                    $sender->setDisplayName($sender->getName());
                    $sender->sendMessage("§l§8(§b!§8)§r §7You tag is off");
                }else{
                    $sender->setDisplayName("#" . $args[0]);
                    $sender->sendMessage("§l§8(§b!§8)§r §r§7Your tag is now§8:§a #" . $args[0]);
                }
            }else{
                $sender->sendMessage("§l§8(§b!§8)§r §l§cUsage§8:§r§7 /tag <name|off>");
                return false;
            }
        }else{
            $sender->sendMessage(Core::PERM_RANK);
            return false;
        }
        return true;
    }
}
