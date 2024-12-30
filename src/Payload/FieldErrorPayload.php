<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

readonly class FieldErrorPayload extends FieldWarningPayload implements ErrorPayload
{

	protected function getType(): string
	{
		return 'error';
	}

}
