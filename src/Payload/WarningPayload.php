<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

use Symfony\Contracts\Translation\TranslatorInterface;

interface WarningPayload
{

	/**
	 * @return mixed[]
	 */
	public function toArray(TranslatorInterface $translator, bool $debugMode = false): array;

}
