<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Swoole\Runtime;

use Runtime\Swoole\Runtime;
use Runtime\Swoole\ServerFactory;

final class SwooleRuntime extends Runtime
{

	/**
	 * @param mixed[] $options
	 */
	public function __construct(array $options, ?ServerFactory $serverFactory = null)
	{
		parent::__construct($options, $serverFactory ?? new ServerFactoryListener($options));

		if (defined('STDOUT')) {
			fwrite(STDOUT, sprintf(
				'Running server, spawn %s workers, %s max requests.',
					$options['settings']['worker_num'] ?? 'unknown',
					$options['settings']['max_request'] ?? 'unknown'
			) . "\n");
		}
	}

}
