<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Swoole\Runtime;

use Runtime\Swoole\ServerFactory;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Throwable;

final class ServerFactoryListener extends ServerFactory
{

	public function createServer(callable $requestHandler): Server
	{
		return parent::createServer(function (Request $request, Response $response) use ($requestHandler): void {
			try {
				$requestHandler($request, $response);
			} catch (Throwable $exception) {
				fwrite(STDOUT, ((string) $exception) . "\n");

				$response->status(500);
				$response->end();
			}
		});
	}

}
