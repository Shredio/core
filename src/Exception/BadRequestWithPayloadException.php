<?php declare(strict_types = 1);

namespace Shredio\Core\Exception;

use Shredio\Core\Payload\ErrorsPayload;

final class BadRequestWithPayloadException extends BadRequestException
{

	public function __construct(
		private readonly ErrorsPayload $errors,
	)
	{
		parent::__construct('Bad request with payload');
	}

	public function getPayload(): ErrorsPayload
	{
		return $this->errors;
	}

}
