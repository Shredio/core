<?php declare(strict_types = 1);

namespace Shredio\Core\Exception;

use RuntimeException;
use Shredio\Core\Payload\ErrorsPayload;
use Shredio\Core\Payload\InternalErrorPayload;
use Shredio\Core\Payload\MessageErrorPayload;

final class BadRequestException extends RuntimeException implements HttpException
{

	public function __construct(
		string $message,
		private readonly bool $safe = false,
	)
	{
		parent::__construct($message);
	}

	public function getHttpCode(): int
	{
		return 400;
	}

	public function getPayload(): ErrorsPayload
	{
		if ($this->safe) {
			return new ErrorsPayload([
				new MessageErrorPayload($this->getMessage()),
			]);
		} else {
			return new ErrorsPayload([
				InternalErrorPayload::from([
					'message' => $this->getMessage(),
				]),
			]);
		}
	}

}
