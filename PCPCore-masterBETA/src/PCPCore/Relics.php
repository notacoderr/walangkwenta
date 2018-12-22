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
      			switch( strtolower($this->getRandomRelic()) )
      			{
				case "common": $relic->setLore([ TF::AQUA. "Common" ]); break;
				case "rare": $relic->setLore([ TF::YELLOW. "Rare" ]); break;
				case "exotic": $relic->setLore([ TF::PURPLE. "Exotic" ]); break;
				case "legendary": $relic->setLore([ TF::DARK_PURPLE. "Legendary" ]); break;
				case "mythic": $relic->setLore([ TF::BLACK. "Mythic" ]); break;
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
		$itemLore = $item->getlore();
      	 	$itemLevel = (string) TF::clean( $itemLore[0] );
		//soon.. will give player (a) random item(s) from it's respective rarity
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
}
