<?php declare(strict_types = 1);

namespace Shredio\Core\Pagination;

use ArrayIterator;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * @template TKey
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
final class PaginatedResults implements IteratorAggregate
{

	/**
	 * @param iterable<TKey, TValue> $results
	 */
	public function __construct(
		private iterable $results,
		public readonly ?string $prevLink,
		public readonly ?string $prevPointer,
		public readonly ?string $nextLink,
		public readonly ?string $nextPointer,
		public readonly bool $isLastPage,
	)
	{
	}

	public function injectIntoPsrResponse(ResponseInterface $response): ResponseInterface
	{
		if ($this->nextLink !== null) {
			$response = $response->withHeader('X-Next-Link', $this->nextLink);
		}

		if ($this->nextPointer !== null) {
			$response = $response->withHeader('X-Next-Pointer', $this->nextPointer);
		}

		if ($this->prevLink !== null) {
			$response = $response->withHeader('X-Previous-Link', $this->prevLink);
		}

		if ($this->prevPointer !== null) {
			$response = $response->withHeader('X-Previous-Pointer', $this->prevPointer);
		}

		return $response->withHeader('X-Is-Last-Page', $this->isLastPage ? 'true' : 'false');
	}

	public function getIterator(): Traversable
	{
		if ($this->results instanceof Traversable) {
			return $this->results;
		} else if (is_array($this->results)) {
			return new ArrayIterator($this->results);
		}

		return new ArrayIterator($this->results = iterator_to_array($this->results));
	}

}
