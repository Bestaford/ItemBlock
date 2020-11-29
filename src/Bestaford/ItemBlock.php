<?php

namespace Bestaford;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\scheduler\CallbackTask;

class ItemBlock extends PluginBase implements Listener {
	
	public $config;
	public $items;

	public function onEnable() {
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
		$this->items = $this->config->get("items");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerItemHeld(PlayerItemHeldEvent $event) {
		$this->check($event);
	}

	public function onPlayerInteract(PlayerInteractEvent $event) {
		$this->check($event);
	}

	public function onPlayerDropItem(PlayerDropItemEvent $event) {
		$this->check($event);
	}

	public function check($event) {
		$player = $event->getPlayer();
		if(!$player->hasPermission("itemblock.bypass")) {
			$item = $event->getItem();
			$id = $item->getId();
			$damage = $item->getDamage();
			$stringId = $id;
			if($damage != 0) {
				$stringId = $id.":".$damage;
			}
			if(in_array($stringId, $this->items)) {
				$event->setCancelled(true);
				$item = Item::get($id, $damage);
				$this->clear($player, $item);
				$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "clear"), array($player, $item)), 20);
				$player->sendMessage($this->config->get("item_blocked"));
			}
		}
	}

	public function clear($player, $item) {
		if($this->getServer()->getPlayer($player->getName()) !== null) {
			while($player->getInventory()->contains($item)) {
				$player->getInventory()->removeItem($item);
			}
		}
	}
}