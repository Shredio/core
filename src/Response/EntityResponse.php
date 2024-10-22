<?php declare(strict_types = 1);

namespace Shredio\Core\Response;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class EntityResponse extends Response
{

	private object $entity;

	public function __construct(object $entity)
	{
		$this->entity = $entity;

		parent::__construct(headers: [
			'Content-Type' => 'application/json',
		], body: Stream::create());
	}

	public function getEntity(): object
	{
		return $this->entity;
	}

	/**
	 * @return MessageInterface&ResponseInterface
	 */
	public function withBody(StreamInterface $body): MessageInterface
	{
		return $this->decorate->withBody($body);
	}

	/**
	 * @return MessageInterface&ResponseInterface
	 */
	public function withStringBody(string $body): MessageInterface
	{
		return $this->withBody(Stream::create($body));
	}

}
