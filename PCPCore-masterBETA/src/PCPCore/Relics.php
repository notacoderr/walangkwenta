<?php

namespace PCPCore;

use PCPCore\Core;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class Relics {
  public $main;
  
  construct __parent(Core $main)
  {
      $this->main = $main;
  }
  
  public function foundRelic(Player $player) : void
  {
    if($this->isLucky())
    {
      switch( strtolower($this->getRandomRelic()) )
      {
        case "common": break;
        case "rare": break;
        case "extreme": break;
        case "legendary": break;
        case "mythic": break;
      }
    }
  }
  
  public function openRelic(Player $player, string $rarity) : void
  {
  }
  
  private isLucky() : bool
  {
    return (mt_rand(1, 10) <= 2);
  }
  
  private getRandomRelic() : string
  {
    $relics = (array) $this->main->premyo->getNested("relics");
    $sum = (int) array_sum($relics);
    if($sum > 100)
    {
      $this->main->getLogger()->error("§cRELICS percentage sum is more than 100");
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
