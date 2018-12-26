<?php

declare(strict_types=1);

namespace CorePCP\cmds;

use CorePCP\Core;
use pocketmine\command\{
    Command, CommandSender, PluginCommand
};
use pocketmine\Player;

class GlideCommand extends PluginCommand{

    /** @var Core */
    private $plugin;

    public function __construct($name, Core $plugin){
        parent::__construct($name, $plugin);
        $this->setDescription("Enable or disable /glide mode!");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$sender instanceof Player) return false;
        if($sender->hasPermission("pcp.glide")){
            if(!$sender->getAllowFlight()){
                $sender->setAllowFlight(true);
                $sender->sendMessage("§8§l(§b!§8)§r §7Your ability to glide has been §l§aENABLED§r§7!");
                $sender->addTitle("§l§bGlide","§aEnabled");
            }else{
                $sender->setAllowFlight(false);
                $sender->setFlying(false);
                $sender->sendMessage("§8§l(§b!§8)§r §7Your ability to glide has been §l§cDISABLED§r§7!");
                $sender->addTitle("§l§bGlide", "§cDisabled");
            }
        }else{
            $sender->sendMessage(Core::PERM_RANK);
            return false;
        }
        return true;
    }
}
