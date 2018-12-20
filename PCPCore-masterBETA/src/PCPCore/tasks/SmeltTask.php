<?php

declare(strict_types=1);

namespace PCPCore\tasks;

use PCPCore\Core;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;

class SmeltTask implements Listener{
    public function onBlockBreak(BlockBreakEvent $event) : void{
        $player = $event->getPlayer();
        $inv = $player->getInventory();
        if($event->isCancelled()) return;
        if($player->isCreative()) return;
        switch($event->getBlock()->getId()){
            case Item::GOLD_ORE:
                if(Core::getInstance()->getConfig()->get("auto-smelt") === "on"){
                    if(Core::getInstance()->getConfig()->get("economy") === "on"){
                        foreach($event->getDrops() as $drops) $inv->addItem(Item::get(Item::GOLD_INGOT, 0, 1));
                        Core::getInstance()->getServer()->getPluginManager()->getPlugin("EconomyAPI")->getInstance()->reduceMoney($player, (float)Core::getInstance()->getConfig()->get("auto-smelt-price"));
                        $event->setDrops([]);
                    }else{
                        foreach($event->getDrops() as $drops) $inv->addItem(Item::get(Item::GOLD_INGOT, 0, 1));
                        $event->setDrops([]);
                    }
                }elseif(Core::getInstance()->getConfig()->get("auto-smelt") === "off"){
                    foreach($event->getDrops() as $drops) $inv->addItem($drops);
                    $event->setDrops([]);
                }
                return;
            case Item::IRON_ORE:
                if(Core::getInstance()->getConfig()->get("auto-smelt") === "on"){
                    if(Core::getInstance()->getConfig()->get("economy") === "on"){
                        foreach($event->getDrops() as $drops) $inv->addItem(Item::get(Item::IRON_INGOT, 0, 1));
                        Core::getInstance()->getServer()->getPluginManager()->getPlugin("EconomyAPI")->getInstance()->reduceMoney($player, (float)Core::getInstance()->getConfig()->get("auto-smelt-price"));
                        $event->setDrops([]);
                    }else{
                        foreach($event->getDrops() as $drops) $inv->addItem(Item::get(Item::IRON_INGOT, 0, 1));
                        $event->setDrops([]);
                    }
                }elseif(Core::getInstance()->getConfig()->get("auto-smelt") === "off"){
                    foreach($event->getDrops() as $drops) $inv->addItem($drops);
                    $event->setDrops([]);
                }
                return;
            default:
                if(Core::getInstance()->getConfig()->get("auto-inv") === "on"){
                    if($inv->canAddItem($event->getItem())){
                        foreach($event->getDrops() as $drops) $inv->addItem($drops);
                        $event->setDrops([]);
                    }else{
                        $player->sendMessage(str_replace("&", "§", Core::getInstance()->getConfig()->get("inv-full-message")));
                    }
                }
                return;
        }
    }
}