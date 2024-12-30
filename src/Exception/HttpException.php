<?php declare(strict_types = 1);

namespace Shredio\Core\Exception;

use Shredio\Core\Payload\ErrorsPayload;
use Throwable;

interface HttpException extends Throwable
{

	public function getHttpCode(): int;

	public function getPayload(): ErrorsPayload;

}
