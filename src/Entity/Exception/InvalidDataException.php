<?php declare(strict_types = 1);

namespace Shredio\Core\Entity\Exception;

use RuntimeException;
use Shredio\Core\Exception\HttpException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;

/**
 * Exception thrown when invalid data is detected.
 */
final class InvalidDataException extends RuntimeException implements HttpException
{

	public function __construct(ExtraAttributesException $previous)
	{
		parent::__construct(sprintf('Extra values: %s', implode(', ', $previous->getExtraAttributes())), 400, $previous);
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
