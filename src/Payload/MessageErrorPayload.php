<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

final readonly class MessageErrorPayload extends MessageWarningPayload implements ErrorPayload
{

	protected function getType(): string
	{
		return 'error';
	}

}
