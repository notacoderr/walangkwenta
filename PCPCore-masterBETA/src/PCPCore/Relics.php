<?php

namespace PCPCore;

use PCPCore\Core;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\inventory\Inventory;
use pocketmine\utils\TextFormat as TF;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};

use pocketmine\command\ConsoleCommandSender;

class Relics {
	
	public $main;
  
	public function __construct(Core $main)
	{
		$this->main = $main;
	}
  
  	public function foundRelic(Player $player, int $chance)
  	{
    		if($this->isLucky($chance))
    		{
			$relic = Item::get(450, 69, 1);
      			switch($this->getRandomRelic())
      			{
				case "Tier I": $relic->setLore([ TF::BOLD. TF::AQUA. "Tier I", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				case "Tier II": $relic->setLore([ TF::BOLD. TF::YELLOW. "Tier II", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				case "Tier III": $relic->setLore([ TF::BOLD. TF::LIGHT_PURPLE. "Tier III", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				case "Tier IV": $relic->setLore([ TF::BOLD. TF::DARK_PURPLE. "Tier IV", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				case "Tier V": $relic->setLore([ TF::BOLD. TF::GRAY. "Tier V", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				default:
					return false;
      			}
			$relic->setCustomName("§r§dAncient §7Pocket Relic"); //meh, fuck me
			/*if($player->getInventory()->canAddItem($relic))
			{
				$player->getInventory()->addItem($relic);
			} else {
				$player->getLevel()->dropItem(new Vector3($player->getX(), $player->getY() + 0.05, $player->getZ()), $relic);
			}*/
			return $relic;
    		}
		return;
  	}
  
	public function openRelic(Player $player, Item $item) : void
	{
		$datas = $this->main->relics;
		$itemLore = $item->getlore();
      	 	$lore = (string) TF::clean( $itemLore[0] ); //clean the relic's tier
		$items = (array) $datas->getNested("contains." . $lore . ".items"); //get array of items
		$cmds = (array) $datas->getNested("contains." . $lore . ".cmd"); //get array of commands
		
		//ITEM
		shuffle($items); //this'll shuffle the item array
		$item = $items[0]; //pick the first one in the array
		$cache = explode("---", $item); //separates item data and enchantments
		$itemdata = explode(" : ", $cache[0]); //item data
		$finalitem = Item::get($itemdata[1], $itemdata[2]); //id & meta
		$finalitem->setCount($itemdata[3]); //count
		$finalitem->setCustomName("$itemdata[0]"); //custom name
		if(strlen($cache[1]) > 2)
		{
			$ench = explode(" : ", $cache[1]); //item enchantments
			foreach($ench as $enchantment)
			{
				$e = explode("*", $enchantment);
				$finalitem = $this->enchantItem($finalitem, $e[0], $e[1]);
			}
		}
		$player->getLevel()->dropItem(new Vector3($player->getX(), $player->getY(), $player->getZ()), $finalitem);
		
		//COMMANDS
		if(count($cmds) >=1)
		{
			shuffle($cmds);
			$command = $cmds[0];
			$command = str_replace("%player&", (string) '"'. $player->getName(). '"', $command);
			Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $command);
		}
	}
  
	private function isLucky(int $chance) : bool
	{
		return (mt_rand(1, 10) <= ($chance / 10));
	}
  
	private function getRandomRelic() : string
	{
		$relics = (array) $this->main->relics->getNested("relics-drop-rate");
		$sum = (int) array_sum($relics);
		
    		if($sum > 100)
    		{
			$this->main->getLogger()->error("§cthe sum of [relic chance rate] is more than 100");
			return "";
		}
    
		$rand = mt_rand(1, $sum);
		foreach ($relics as $relic => $chance)
		{
     			$rand -= $chance;
      			if ($rand <= 0)
	    		{
		    		return $relic;
      			}
    		}
  	}
	
	private function enchantItem(Item $item, int $e, int $lvl) : Item
	{
		if($e >= 100)
		{
			//custom ench
		} else {
			$item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($e), $lvl));
		}		
		return $item;
	}
}
