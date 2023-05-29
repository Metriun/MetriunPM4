<?php

declare(strict_types=1);

namespace Metriun\Metriun\analyzers;

use Metriun\Metriun\API;
use Metriun\Metriun\Main;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

use function date;

class VisitsInDay implements Listener {
	private ?Config $config;
	private int $peak_players = 0;
	private ?String $chart_token;
	private String $actual_date = "";

	public function __construct(Main $plugin, String $token) {
		// Registrando evento
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

		// Definindos as variaveis.
		$this->config = new Config($plugin->getDataFolder() . "data/VisitsInDay.yml");
		$this->chart_token = $token;
		$this->actual_date = date("d/m/Y");

		// Iniciando a task.
		$plugin->getScheduler()->scheduleRepeatingTask(new VisitsInDayTask($this), 20 * 60 * 30);
	}

	public function save() {
		// Salvar os dados do dia.
		$this->config->set($this->actual_date, $this->peak_players);
		$this->config->save();
	}

	public function init() {
		// Pegar os dados guardados do dia.
		$this->peak_players = $this->config->get($this->actual_date, 0);
	}

	public function onJoin(PlayerJoinEvent $ev): void {
		$this->peak_players++;
	}

	public function sendRequest() {
		API::request([
			$this->actual_date,
			$this->peak_players
		], $this->actual_date, $this->chart_token);
	}
}

class VisitsInDayTask extends Task {
	private bool $_primary = false;

	public function __construct(
		private VisitsInDay $owner) {
		//
	}

	public function onRun(): void {
		if ($this->_primary) {
			$this->owner->save();
			$this->owner->sendRequest();
		} else {
			$this->_primary = true;
		}
	}
}