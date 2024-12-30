<?php declare(strict_types = 1);

namespace Shredio\Core\Translation;

use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class VoidTranslator implements TranslatorInterface
{

	public function __construct(
		private string $locale = 'en',
	)
	{
	}

	/**
	 * @param mixed[] $parameters
	 */
	public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
	{
		return $id;
	}

	public function getLocale(): string
	{
		return $this->locale;
	}

}
