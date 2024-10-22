<?php declare(strict_types = 1);

namespace Shredio\Core\Entity\Exception;

use RuntimeException;
use Shredio\Core\Exception\HttpException;
use Throwable;

/**
 * Exception thrown when invalid data is detected.
 */
final class InvalidDataException extends RuntimeException implements HttpException
{

	public function __construct(string $entity, ?Throwable $previous = null)
	{

		parent::__construct(sprintf('Invalid data for entity: %s', $entity), 400, $previous);
	}

	public function getHttpCode(): int
	{
		return 400;
	}

	public function getPayload(): ?array
	{
		return null;
	}

}
