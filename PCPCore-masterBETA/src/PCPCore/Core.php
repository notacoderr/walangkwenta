<?php

declare(strict_types=1);

namespace CorePCP;

use CorePCP\events\{
    AntiAdvertising, AntiSwearing, CustomPotionEvent, Generate
};
use CorePCP\cmds\{
	GlideCommand, HeadCommand, MaskCommand, TagCommand, RulesCommand, CustomPotion, WildCommand, StaffCommand, CureCommand
};
use CorePCP\tasks\{
	BroadcastTask, ClearLaggTask, MaskTask
};

use pocketmine\utils\{Config, TextFormat};

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\{
    PlayerLoginEvent, PlayerDeathEvent
};

class Core extends PluginBase{

    public const PERM_RANK = "§l§8(§c!§8)§r §7You don't have permission to use this command!";
    public const PERM_STAFF = "§l§8(§c!§8)§r §7Only staff members can use this command!";
    public const USE_IN_GAME = "§l§8(§c!§8)§r §7Please use this command in-game!";

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
				TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Steve Mask",
				"",
				TextFormat::RESET . TextFormat::GREEN . "RARITY",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Common",
				"",
				TextFormat::RESET . TextFormat::GREEN . "ABILITY",
				"Amazing Abilities",
				"",
				TextFormat::RESET . TextFormat::GREEN . "EFFECT",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Discover these for yourself ^_^",
			],
		4 => [
				TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Creeper Mask",
				"",
				TextFormat::RESET . TextFormat::GREEN . "RARITY",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Rare",
				"",
				TextFormat::RESET . TextFormat::GREEN . "ABILITY",
				"Look someone in the eye, and explode! And gain 5 extra health!",
				"",
				TextFormat::RESET . TextFormat::GREEN . "EFFECT",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Speed II",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Haste II",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Regeneration I",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Gain 5 extra health",
			],
		5 => [
				TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Dragon Mask",
				"",
				TextFormat::RESET . TextFormat::GREEN . "RARITY",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Legendary",
				"",
				TextFormat::RESET . TextFormat::GREEN . "ABILITY",
				"Gain many effects",
				"",
				TextFormat::RESET . TextFormat::GREEN . "EFFECT",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Speed II",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Regeneration I",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Gain 20 extra health",
			],
		6 => [
				TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Rabbit Mask",
				"",
				TextFormat::RESET . TextFormat::GREEN . "RARITY",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Common",
				"",
				TextFormat::RESET . TextFormat::GREEN . "ABILITY",
				"Amazing Abilities",
				"",
				TextFormat::RESET . TextFormat::GREEN . "EFFECT",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Discover these for yourself ^_^",
			],
		7 => [
				TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Witch Mask",
				"",
				TextFormat::RESET . TextFormat::GREEN . "RARITY",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Common",
				"",
				TextFormat::RESET . TextFormat::GREEN . "ABILITY",
				"Amazing Abilities",
				"",
				TextFormat::RESET . TextFormat::GREEN . "EFFECT",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Discover these for yourself ^_^",
			],
		8 => [
				TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Enderman Mask",
				"",
				TextFormat::RESET . TextFormat::GREEN . "RARITY",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Rare",
				"",
				TextFormat::RESET . TextFormat::GREEN . "ABILITY",
				"Have a chance to look someone in the eye! And teleport!",
				"",
				TextFormat::RESET . TextFormat::GREEN . "EFFECT",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Speed II",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Haste II",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Night Vision II",
			],
		9 => [
				TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Chef Mask",
				"",
				TextFormat::RESET . TextFormat::GREEN . "RARITY",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Common",
				"",
				TextFormat::RESET . TextFormat::GREEN . "ABILITY",
				"You will never go hungry again!",
				"",
				TextFormat::RESET . TextFormat::GREEN . "EFFECT",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Saturarion",
			],
		10 => [
				TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Miner Mask",
				"",
				TextFormat::RESET . TextFormat::GREEN . "RARITY",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Common",
				"",
				TextFormat::RESET . TextFormat::GREEN . "ABILITY",
				"Be able to mine like a drill!",
				"",
				TextFormat::RESET . TextFormat::GREEN . "EFFECT",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Haste II",
				TextFormat::RESET . TextFormat::GREEN . " * " . TextFormat::WHITE . "Speed I",
			],
	];

    	/** @var null */
   	private static $instance = null;
	
   	 /** @var Config */
    	public $config, $settings, $relicBlocks = [], $randRelic = [], $chat = [];

    public static function getInstance() : self{
        return self::$instance;
    }

    public function onEnable() : bool
    {
	$this->relic = new Relics($this);
        // COMMANDS \\
        $this->getServer()->getCommandMap()->registerAll("CorePCP", [
            new FlyCommand("glide", $this),
            new TagCommand("tag", $this),
            new RulesCommand("rules", $this),
            new CustomPotion("potion", $this),
            new WildCommand("wild", $this),
            new MaskCommand("mask", $this),
            new HeadCommand("head", $this),
            new StaffCommand("staffs", $this),
	    new CureCommand("cure", $this),
        ]);
        // CONFIGS \\
        @mkdir($this->getDataFolder());
        $this->saveResource("rules.txt");
        $this->saveResource("staffs.txt");
        
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
	   
	foreach($this->relics->getNested("relics-drop-rate") as $tier => $chance)
	{
		$this->randRelic[ $tier ] = $chance;
	}
	    
	if(array_sum($this->randRelic) > 100)
    	{
		$this->getLogger()->error("§4The sum of §f[relics-drop-rate]§4 is more than 100");
		$this->getPluginLoader()->disablePlugin($this);
		return false;
		
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
	    
	return true;
    }
}
