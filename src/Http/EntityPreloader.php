<?php declare(strict_types = 1);

namespace Shredio\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface EntityPreloader
{

	/**
	 * @param object[] $entities
	 */
	public function preload(ServerRequestInterface $request, ResponseInterface $response, array $entities): ResponseInterface;

}
