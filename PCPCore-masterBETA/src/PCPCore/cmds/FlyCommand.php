<?php

declare(strict_types=1);

namespace PCPCore\cmds;

use PCPCore\Core;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\Player;

class FlyCommand extends PluginCommand{

    /** @var Core */
    private $plugin;

    public function __construct($name, Core $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Enable OR Disable /fly mode!");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) return false;
        if($sender->hasPermission("pcp.fly")){
            if(!$sender->getAllowFlight()){
                $sender->setAllowFlight(true);
                $sender->sendMessage("§8§l(§b!§8)§r §7Your ability to fly has been §l§aENABLED§r§7!");
                //$sender->addTitle("§l","a");
            }else{
                $sender->setAllowFlight(false);
                $sender->setFlying(false);
                $sender->sendMessage("§8§l(§b!§8)§r §7Your ability to fly has been §l§cDISABLED§r§7!");
            }
        }else{
            $sender->sendMessage(Core::PERM_RANK);
            return false;
        }
        return true;
    }
}
