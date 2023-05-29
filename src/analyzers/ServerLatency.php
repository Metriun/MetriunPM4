<?php

declare(strict_types=1);

namespace Metriun\Metriun\analyzers;

use Metriun\Metriun\API;
use Metriun\Metriun\Main;
use pocketmine\scheduler\Task;

use function count;
use function date;
use function round;

class ServerLatency {
	private ?String $chart_token;

	private ?Main $plugin;

	public function __construct(Main $plugin, String $token) {
		$this->chart_token = $token;
		$this->plugin = $plugin;

		$this->plugin->getScheduler()->scheduleRepeatingTask(new ServerLatencyTask($this), 20 * 60 * 120);
	}

	public function getServerLatency(): int {
		$players = $this->plugin->getServer()->getOnlinePlayers();
		$totalPing = 0;
		$playerCount = count($players);

		foreach ($players as $player) {
			$ping = $player->getNetworkSession()->getPing();
			$totalPing += $ping;
		}

		if ($playerCount > 0) {
			$averagePing = $totalPing / $playerCount;
			return (int) round($averagePing);
		}

		return 0;
	}

	public function sendRequest() {
		$data = date("m/Y");
		$server_latence = $this->getServerLatency();

		API::request([$data, $server_latence], $data, $this->chart_token);
	}
}

class ServerLatencyTask extends Task {
	private bool $_primary = false;

	public function __construct(private ServerLatency $owner) {
	}

	public function onRun(): void {
		if ($this->_primary) {
			$this->owner->sendRequest();
		} else {
			$this->_primary = true;
		}
	}
}