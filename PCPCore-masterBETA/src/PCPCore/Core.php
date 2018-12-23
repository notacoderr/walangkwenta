<?php

declare(strict_types=1);

namespace PCPCore;

use PCPCore\events\{
    AntiAdvertising, AntiSwearing, CustomPotionEvent, Generate
};
use PCPCore\cmds\{
	FlyCommand, HeadCommand, MaskCommand, TagCommand, RulesCommand, CustomPotion, WildCommand, StaffCommand
};
use PCPCore\tasks\{
	BroadcastTask, ClearLaggTask, MaskTask, SmeltTask
};

use pocketmine\utils\{Config, TextFormat};
use pocketmine\block\{Bedrock, BlockFactory, TNT};

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\{
    PlayerLoginEvent, PlayerDeathEvent
};

use pocketmine\entity\{
    Creature, Entity, Human
};

class Core extends PluginBase{

    public const PERM_RANK = "§l§fP§bC§fP §8»§r §7You don't have permission to use this command!";
	public const PERM_STAFF = "§l§fP§bC§fP §8»§r §7Only staff members can use this command!";
	public const USE_IN_GAME = "§l§fP§bC§fP §8»§r §7Please use this command in-game!";

	public const MASK_DAMAGE_TO_NAME = [
		3 => "Steve Mask",
		4 => "Creeper Mask",
		5 => "Dragon Mask",
		6 => "Rabbit Mask",
		7 => "Witch Mask",
		8 => "Enderman Mask",
		9 => "Chef Mask",
		10 => "Miner Mask",
	];

	public const MASK_DAMAGE_TO_LORE = [
		3 => [
				TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Steve Mask",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "RARITY",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Common",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "ABILITY",
				"Amazing Abilities",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "EFFECT",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Discover these for yourself ^_^",
			],
		4 => [
				TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Creeper Mask",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "RARITY",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Rare",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "ABILITY",
				"Look someone in the eye, and explode! And gain 5 extra health!",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "EFFECT",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Speed II",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Haste II",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Regeneration I",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Gain 5 extra health",
			],
		5 => [
				TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Dragon Mask",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "RARITY",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Legendary",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "ABILITY",
				"Gain many effects",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "EFFECT",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Speed II",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Regeneration I",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Gain 20 extra health",
			],
		6 => [
				TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Rabbit Mask",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "RARITY",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Common",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "ABILITY",
				"Amazing Abilities",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "EFFECT",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Discover these for yourself ^_^",
			],
		7 => [
				TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Witch Mask",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "RARITY",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Common",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "ABILITY",
				"Amazing Abilities",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "EFFECT",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Discover these for yourself ^_^",
			],
		8 => [
				TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enderman Mask",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "RARITY",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Rare",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "ABILITY",
				"Have a chance to look someone in the eye! And teleport!",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "EFFECT",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Speed II",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Haste II",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Night Vision II",
			],
		9 => [
				TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Chef Mask",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "RARITY",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Common",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "ABILITY",
				"You will never go hungry again!",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "EFFECT",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Saturarion",
			],
		10 => [
				TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Miner Mask",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "RARITY",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Common",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "ABILITY",
				"Be able to mine like a drill!",
				"",
				TextFormat::BOLD . TextFormat::GREEN . "EFFECT",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Haste II",
				TextFormat::BOLD . TextFormat::GREEN . " * " . TextFormat::RESET . "Speed I",
			],
	];

	public static $TNT_timeouts = [];

    	/** @var null */
   	private static $instance = null;
	
   	 /** @var Config */
    	public $config, $settings, $getRelics, $relicBlocks = [], $chat = [];

    public static function getInstance() : self{
        return self::$instance;
    }

    public function onEnable() : void{
	$this->relic = new Relics($this);
        // COMMANDS \\
        $this->getServer()->getCommandMap()->registerAll("PCPCore", [
            new FlyCommand("fly", $this),
            new TagCommand("tag", $this),
            new RulesCommand("rules", $this),
            new CustomPotion("potion", $this),
            new WildCommand("wild", $this),
            new MaskCommand("mask", $this),
            new HeadCommand("head", $this),
            new StaffCommand("staffs", $this),
        ]);
        // CONFIGS \\
        @mkdir($this->getDataFolder());
        $this->saveResource("rules.txt");
        $this->saveResource("staffs.txt");
        
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new SmeltTask(), $this);
        $this->economyCheck();
        
        $this->saveResource("broadcast.yml");
        $this->settings = new Config($this->getDataFolder() . "broadcast.yml", Config::YAML);
	$this->saveResource("premyo.yml");
	$this->premyo = new Config($this->getDataFolder() . "premyo.yml", Config::YAML);
	$this->saveResource("relics.yml");
	$this->relics = new Config($this->getDataFolder() . "relics.yml", Config::YAML);
	    
	foreach($this->relics->getNested("chance-from") as $id => $chance)
	{
		$this->relicBlocks[ $id ] = $chance; 
	}

        if(is_numeric($this->settings->get("seconds"))){
            $this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this), $this->settings->get("seconds") * 20);
        }
        // EVENTS \\
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new CustomPotionEvent(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new AntiAdvertising($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new AntiSwearing($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new Generate($this), $this);
        // TASKS \\
        $this->getScheduler()->scheduleRepeatingTask(new MaskTask($this), 20);
        $this->getScheduler()->scheduleRepeatingTask(new ClearLaggTask($this), 20 * 60 * 5);
        // Block overrides -- @Hytlenz
        BlockFactory::registerBlock(new class extends Bedrock {
        	public function getBlastResistance(): float{
				return 38;
			}
		}, true);
        BlockFactory::registerBlock(new class extends TNT {
        	public function onActivate(Item $item, Player $player = \null): bool{
				if($item->getId() === Item::FLINT_STEEL){
					if(isset(Core::$TNT_timeouts[$player->getId()])){
						$diff = time() - Core::$TNT_timeouts[$player->getId()];
						if($diff > 15){
							Core::$TNT_timeouts[$player->getId()] = time();
							$item->useOn($this);
							$this->ignite();
						} else {
							$player->sendMessage("§l§8(§b!§8)" . TextFormat::RESET . TextFormat::RED . " TNT is in cooldown at the moment." . TextFormat::RESET . TextFormat::RED . "\nPlease wait for $diff seconds.");
						}
					} else {
						Core::$TNT_timeouts[$player->getId()] = time();
						$item->useOn($this);
						$this->ignite();
					}
					return true;
				}

				return false;
			}
		}, true);
    }
    
    private function economyCheck() : bool{
        if($this->getConfig()->get("economy") === "on"){
            if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") === null){
                $this->getLogger()->error(TextFormat::RED . "PCPCore disabled! You must enable/install EconomyAPI or turn off economy support in the config!");
                $this->getPluginLoader()->disablePlugin($this);
                return false;
            }
        }elseif($this->getConfig()->get("economy") === "off") return false;
        return true;
    }
}
