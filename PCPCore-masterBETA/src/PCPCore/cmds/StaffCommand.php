<?php

declare(strict_types=1);

namespace PCPCore\cmds;

use PCPCore\Core;
use pocketmine\command\{Command, CommandSender, PluginCommand};
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
class StaffCommand extends PluginCommand{
  
  /** @var Core */
  private $plugin;
  
  public function __construct($name, Core $plugin){
    parent::__construct($name, $plugin);
    $this->setDescription("Show you the staff list.");
    $this->plugin = $plugin;
  }
  
  public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
    if(!$sender instanceof Player){
      $sender->sendMessage(TF::RED . "Please use this ingame.");
      return false;
    }
    
     $form = $this->plugin->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $sender, array $data){});
        
        $form->setTitle(TF::BOLD . "PCP Staffs");
        $form->addLabel(file_get_contents($this->plugin->getDataFolder() . "staffs.txt"));
        $form->sendToPlayer($sender);
        return true;
        }                       
  }