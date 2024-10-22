<?php declare(strict_types = 1);

namespace Shredio\Core\Exception;

use Throwable;

interface HttpException extends Throwable
{

	public function getHttpCode(): int;

	/**
	 * @return mixed[]|null
	 */
	public function getPayload(): ?array;

}
