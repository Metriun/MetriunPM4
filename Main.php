<?php

declare(strict_types=1);

namespace Metriun\Metriun;

use Metriun\Metriun\analyzers\MaxPlayersPerDay;
use Metriun\Metriun\analyzers\PlayerCountry;
use Metriun\Metriun\analyzers\PlayersRegistrationsDays;
use Metriun\Metriun\analyzers\ServerLatency;
use Metriun\Metriun\analyzers\SessionAverage;
use Metriun\Metriun\analyzers\TpsPerTime;
use Metriun\Metriun\analyzers\VisitsInDay;
use pocketmine\plugin\PluginBase;

use function method_exists;
use function mkdir;

class Main extends PluginBase {
	private array $analizers = [];

	public function onEnable(): void {
		@mkdir($this->getDataFolder() . "data");

		$this->saveResource("config.yml");
		$this->saveDefaultConfig();

		// Ligar os analizadores.
		$this->loadAnalyzers();

		foreach ($this->analizers as $analizer) {
			if (method_exists($analizer, "init")) {
				$analizer->init();
			}
		}
	}

	public function onDisable(): void {
		foreach ($this->analizers as $analizer) {
			if (method_exists($analizer, "save")) {
				$analizer->save();
			}
		}
	}

	private function loadAnalyzers(): void {
		// Máximo de players por dia.
		if ($this->getConfig()->getNested("max-players-per-day.enable")) {
			$this->analizers[] = new MaxPlayersPerDay($this, $this->getConfig()->getNested("max-players-per-day.chart_token"));
		}

		// Visitantes no dia.
		if ($this->getConfig()->getNested("visits-in-the-day.enable")) {
			$this->analizers[] = new VisitsInDay($this, $this->getConfig()->getNested("visits-in-the-day.chart_token"));
		}

		// TPS por tempo.
		if ($this->getConfig()->getNested("tps-per-time.enable")) {
			$this->analizers[] = new TpsPerTime($this, $this->getConfig()->getNested("tps-per-time.chart_token"), (int) $this->getConfig()->getNested("tps-per-time.send_time"));
		}

		// Tempo de sessão de cada player.
		if ($this->getConfig()->getNested("session-average.enable")) {
			$this->analizers[] = new SessionAverage($this, $this->getConfig()->getNested("session-average.chart_token"));
		}

		// Paises que acessam o servidor
		if ($this->getConfig()->getNested("player-by-country.enable")) {
			$this->analizers[] = new PlayerCountry($this, $this->getConfig()->getNested("player-by-country.chart_token"));
		}

		// Latência do servidor.
		if ($this->getConfig()->getNested("server-latency.enable")) {
			$this->analizers[] = new ServerLatency($this, $this->getConfig()->getNested("server-latency.chart_token"));
		}

		// Latência do servidor.
		if ($this->getConfig()->getNested("player-registration-per-day.enable")) {
			$this->analizers[] = new PlayersRegistrationsDays($this, $this->getConfig()->getNested("player-registration-per-day.chart_token"));
		}
	}
}