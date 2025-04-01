<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

use Shredio\Core\Environment\AppEnvironment;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ErrorsPayloadProcessor
{

	public function __construct(
		private AppEnvironment $appEnv,
		private TranslatorInterface $translator,
	)
	{
	}

	/**
	 * @return mixed[]
	 */
	public function process(ErrorsPayload $errors, ?bool $debugMode = null): array
	{
		return $errors->toArray($this->translator, $debugMode ?? $this->appEnv->isProduction());
	}

	/**
	 * @return string[]
	 */
	public function processToStrings(ErrorsPayload $errors, ?bool $debugMode = null): array
	{
		$strings = [];

		foreach ($this->process($errors, $debugMode) as $error) {
			if (!isset($error['message'])) {
				continue;
			}

			$strings[] = $error['message'];
		}

		return $strings;
	}

}
