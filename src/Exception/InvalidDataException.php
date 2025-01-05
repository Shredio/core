<?php declare(strict_types = 1);

namespace Shredio\Core\Exception;

use RuntimeException;
use Shredio\Core\Payload\ErrorsPayload;
use Shredio\Core\Payload\InternalErrorPayload;
use Throwable;

/**
 * Exception thrown when invalid data is detected.
 */
final class InvalidDataException extends RuntimeException implements HttpException
{

	/**
	 * @param mixed[] $details
	 */
	public function __construct(
		string $message,
		private readonly Throwable $previous,
		private readonly array $details = [],
	)
	{
		parent::__construct($message, 400, $this->previous);
	}

	public function getHttpCode(): int
	{
		return 400;
	}

	public function getPayload(): ErrorsPayload
	{
		$extra = [];

		if ($this->details) {
			$extra['internal'] = $this->details;
		}

		return new ErrorsPayload([
			InternalErrorPayload::fromThrowable($this->previous, extra: $extra),
		]);
	}

}
