<?php

namespace CorePCP\cmds;

use CorePCP\Core;
use pocketmine\utils\TextFormat as C;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class HeadCommand extends PluginCommand {
	public function __construct(string $name, Plugin $owner){
		parent::__construct($name, $owner);
		$this->setDescription("Heads");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){
			$item = $sender->getInventory()->getItemInHand();
			if($item->getNamedTag()->hasTag("head", StringTag::class)){
				$target = $item->getNamedTag()->getString("head");
				$eco = EconomyAPI::getInstance();
				$money = $eco->myMoney($target) * 0.05;
				$eco->reduceMoney($target, $money, true);
				$eco->addMoney($sender, $money, true);
				$sender->sendMessage(C::BOLD . C::DARK_GRAY . "(" . C::AQUA . "!" . C::DARK_GRAY . ")" . C::RESET . C::GRAY . " You got " . C::GOLD . "$" . $money . C::GRAY . " from " . C::AQUA . $target);
				$item->setCount(1);
				$sender->getInventory()->removeItem($item);
			}
		}
	}
}
