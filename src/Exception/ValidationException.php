<?php declare(strict_types = 1);

namespace Shredio\Core\Exception;

use Exception;
use Shredio\Core\Payload\ErrorsPayload;
use Throwable;

final class ValidationException extends Exception implements HttpException
{

	public function __construct(
		private readonly ErrorsPayload $errors,
		private readonly ?Throwable $previous = null,
	)
	{
		parent::__construct('The given data was invalid.', 422, $this->previous);
	}

	public function getHttpCode(): int
	{
		return 422;
	}

	public function getPayload(): ErrorsPayload
	{
		return $this->errors;
	}

}
