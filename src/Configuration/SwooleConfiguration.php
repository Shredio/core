<?php declare(strict_types = 1);

namespace Shredio\Core\Configuration;

use Shredio\Core\Bridge\Swoole\Runtime\SwooleRuntime;

final class SwooleConfiguration
{

	private int $maxRequests = 1;
	private int $maxWorkers = 1;

	public function __construct(
		private readonly string $host,
		private readonly string $port,
	)
	{
	}

	public function setMaxRequests(int|string|bool $maxRequests): self
	{
		if (is_numeric($maxRequests)) {
			$this->maxRequests = (int) $maxRequests;
		}

		return $this;
	}

	public function setMaxWorkers(int|string|bool $maxWorkers): self
	{
		if (is_numeric($maxWorkers)) {
			$this->maxWorkers = (int) $maxWorkers;
		}

		return $this;
	}

	public function configure(): void
	{
		$_SERVER['APP_RUNTIME_OPTIONS'] = [
			'host' => $this->host,
			'port' => $this->port,
			'mode' => SWOOLE_BASE,
			'settings' => [
				'max_request' => $this->maxRequests,
				'worker_num' => $this->maxWorkers,
				// @see https://wiki.swoole.com/en/#/server/setting?id=dispatch_mode
				'dispatch_mode' => 3, // stateless async
				'package_max_length' => 10 * 1024 * 1024,
			],
		];
		$_SERVER['APP_RUNTIME'] = SwooleRuntime::class;
	}

}
