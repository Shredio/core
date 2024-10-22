<?php declare(strict_types = 1);

namespace Shredio\Core\Response;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

final class EntitiesResponse extends Response
{

	/** @var iterable<object> */
	private iterable $entities;

	/** @var object[] */
	private array $arrayOfEntity;

	/**
	 * @param iterable<object> $entities
	 * @param array<string, string> $headers
	 */
	public function __construct(iterable $entities, array $headers = [])
	{
		$this->entities = $entities;

		$headers['content-type'] = 'application/json';

		parent::__construct(headers: $headers, body: Stream::create());
	}

	/**
	 * @return iterable<object>
	 */
	public function getSource(): iterable
	{
		return $this->entities;
	}

	/**
	 * @return object[]
	 */
	public function getEntities(): array
	{
		return $this->arrayOfEntity ??= is_array($this->entities) ? $this->entities : iterator_to_array($this->entities, false);
	}

	/**
	 * @return MessageInterface&ResponseInterface
	 */
	public function withStringBody(string $body): MessageInterface
	{
		return $this->withBody(Stream::create($body));
	}

}
