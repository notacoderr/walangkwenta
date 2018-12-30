<?php

namespace CorePCP;

use CorePCP\Core;

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
			$tier = $this->getRandomRelic();
      			switch($tier)
      			{
				case "Tier I": $relic->setLore([ TF::BOLD. TF::AQUA. "Tier I", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				case "Tier II": $relic->setLore([ TF::BOLD. TF::YELLOW. "Tier II", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				case "Tier III": $relic->setLore([ TF::BOLD. TF::LIGHT_PURPLE. "Tier III", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				case "Tier IV": $relic->setLore([ TF::BOLD. TF::DARK_PURPLE. "Tier IV", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				case "Tier V": $relic->setLore([ TF::BOLD. TF::GRAY. "Tier V", TF::RESET. TF::WHITE. "Tap anywhere to open!" ]); break;
				default:
					return false;
      			}
			$relic->setCustomName("§r§dVoid §7Relic : §c". $tier);
			Server::getInstance()->broadcastMessage("§l§8(§b!§8)§r§b ". $player->getName(). " §7Found a §dVoid §7Relic : §c". $tier);
			return $relic;
    		}
		return false;
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
		$player->getLevel()->dropItem(new Vector3($player->getX(), $player->getY() + 0.75, $player->getZ()), $finalitem);
		
		//COMMANDS
		
		if(count($cmds) >=1)
		{
			shuffle($cmds);
			$command = $cmds[0];
			$command = str_replace("%player%", '"'. $player->getName(). '"', $command);
			Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $command);
		}
	}
  
	private function isLucky(int $chance) : bool
	{
		return (mt_rand(5, 100) <= $chance);
	}
  
	private function getRandomRelic() : string
	{
		$relics = $this->main->randRelic;
		$rand = mt_rand(1, (int) array_sum($relics));
		foreach ($relics as $relic => $chance)
		{
     			$rand -= $chance;
      			if ($rand <= 0)
	    		{
		    		return $relic;
      			}
    		}
  	}
	
	private function enchantItem(Item $item, $enchId, int $lvl) : Item
	{
		if($enchId >= 100 or is_string($enchId))
		{
			if(($pce = Server::getInstance()->getPluginManager()->getPlugin("PiggyCustomEnchant")) != null)
			{
				$pce->addEnchantment($item, $id, $lvl);
			}
		}
		if($enchId <= 32 && $enchId >= 0)
		{
			$enchantment = Enchantment::getEnchantment((int) $enchId);
			if($enchantment instanceof Enchantment)
			{
				$item->addEnchantment( new EnchantmentInstance($enchantment, $lvl) );
			}
		}
		return $item;
	}
}
