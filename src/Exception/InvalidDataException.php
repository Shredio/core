<?php declare(strict_types = 1);

namespace Shredio\Core\Exception;

use RuntimeException;
use Shredio\Core\Payload\ErrorsPayload;
use Shredio\Core\Payload\InternalErrorPayload;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;

/**
 * Exception thrown when invalid data is detected.
 */
final class InvalidDataException extends RuntimeException implements HttpException
{

	public function __construct(
		private readonly ExtraAttributesException $previous,
	)
	{
		parent::__construct(
			sprintf('Extra values: %s', implode(', ', $this->previous->getExtraAttributes())),
			400,
			$this->previous,
		);
	}

	public function getHttpCode(): int
	{
		return 400;
	}

	public function getPayload(): ErrorsPayload
	{
		return new ErrorsPayload([
			InternalErrorPayload::fromThrowable($this->previous, extra: ['internal' => ['extraAttributes' => $this->previous->getExtraAttributes()]]),
		]);
	}

}
