<?php

namespace PCPCore;

use PCPCore\Core;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\inventory\Inventory;
use pocketmine\utils\TextFormat as TF;

class Relics {
	public $main;
  
	construct __parent(Core $main)
	{
		$this->main = $main;
	}
  
  	public function foundRelic(Player $player) : bool
  	{
    		if($this->isLucky())
    		{
			$relic = Item::get(146, 69, 1);
      			switch($this->getRandomRelic())
      			{
				case "tier i": $relic->setLore([ TF::AQUA. "Tier I" ]); break;
				case "tier ii": $relic->setLore([ TF::YELLOW. "Tier II" ]); break;
				case "tier iii": $relic->setLore([ TF::PURPLE. "Tier III" ]); break;
				case "tier iv": $relic->setLore([ TF::DARK_PURPLE. "Tier IV" ]); break;
				case "tier v": $relic->setLore([ TF::BLACK. "Tier V" ]); break;
				default:
					return false;
      			}
			
			if($player->getInventory()->canAddItem($relic))
			{
				$player->getInventory()->addItem($relic);
			} else {
				$player->getLevel()->dropItem(new Vector3($player->getX(), $player->getY(), $player->getZ()), $relic));
			}
			
			return true;
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
		shuffle($items); //this'll shuffle the item array
		$item = $items[0]; //pick the first one in the array
		$i = explode("---", $item); //separates item data and enchantments
		$itemdata = explode(" : ", $i[0]); //item data
		$finalitem = Item::get($itemdata[1], $itemdata[2]); //id & meta
		$finalitem->setCount($itemdata[3]); //count
		$finalitem->setCustomName("$itemdata[0]"); //custom name
		$ench = explode(" : ", $i[1]); //item enchantments
		if(count($ench) > 0)
		{
			foreach($ench as $enchantment)
			{
				$e = explode("*", $enchantment);
				$finalitem = $this->enchantItem($finalitem, $e[0], $e[1]);
			}
		}
		$player->getLevel()->dropItem(new Vector3($player->getX(), $player->getY(), $player->getZ()), $finalitem));
	}
  
	private isLucky() : bool
	{
		return (mt_rand(1, 10) <= 2);
	}
  
	private getRandomRelic() : string
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
	
	private enchantItem(Item $item, int $e, int $lvl) : Item
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
