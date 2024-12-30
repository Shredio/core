<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

interface ErrorPayload
{

	/**
	 * @return mixed[]
	 */
	public function toArray(bool $debugMode = false): array;

}
