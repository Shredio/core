<?php declare(strict_types = 1);

namespace Shredio\Core\Intl;

enum Language: string
{

	case Czech = 'cs';
	case English = 'en';
	case Polish = 'pl';
	case German = 'de';
	case French = 'fr';
	case Spanish = 'es';
	case Russian = 'ru';
	case Bulgarian = 'bg';
	case Italian = 'it';
	case Romanian = 'ro';
	case Finnish = 'fi';
	case Swedish = 'sv';
	case Danish = 'da';
	case Portuguese = 'pt';
	case Hungarian = 'hu';
	case Estonian = 'et';
	case Latvian = 'lv';
	case Lithuanian = 'lt';
	case Dutch = 'nl';
	case Slovenian = 'sl';
	case Turkish = 'tr';

	private const Emoji = [
		'cs' => '🇨🇿',
		'en' => '🇬🇧',
		'pl' => '🇵🇱',
		'de' => '🇩🇪',
		'fr' => '🇫🇷',
		'es' => '🇪🇸',
		'ru' => '🇷🇺',
		'bg' => '🇧🇬',
		'it' => '🇮🇹',
		'ro' => '🇷🇴',
		'fi' => '🇫🇮',
		'sv' => '🇸🇪',
		'da' => '🇩🇰',
		'pt' => '🇵🇹',
		'hu' => '🇭🇺',
		'et' => '🇪🇪',
		'lv' => '🇱🇻',
		'lt' => '🇱🇹',
		'nl' => '🇳🇱',
		'sl' => '🇸🇮',
		'tr' => '🇹🇷',
	];

	private const EnglishNames = [
		'cs' => 'Czech',
		'en' => 'English',
		'pl' => 'Polish',
		'de' => 'German',
		'fr' => 'French',
		'es' => 'Spanish',
		'ru' => 'Russian',
		'bg' => 'Bulgarian',
		'it' => 'Italian',
		'ro' => 'Romanian',
		'fi' => 'Finnish',
		'sv' => 'Swedish',
		'da' => 'Danish',
		'pt' => 'Portuguese',
		'hu' => 'Hungarian',
		'et' => 'Estonian',
		'lv' => 'Latvian',
		'lt' => 'Lithuanian',
		'nl' => 'Dutch',
		'sl' => 'Slovenian',
		'tr' => 'Turkish',
	];

	private const Names = [
		'cs' => 'Čeština',
		'en' => 'English',
		'pl' => 'Polski',
		'de' => 'Deutsch',
		'fr' => 'Français',
		'es' => 'Español',
		'ru' => 'Русский',
		'bg' => 'български език',
		'it' => 'Italiano',
		'ro' => 'Română',
		'fi' => 'Suomalainen',
		'sv' => 'Svenska',
		'da' => 'Dansk',
		'pt' => 'Português',
		'hu' => 'Magyar',
		'et' => 'Eesti',
		'lv' => 'Lietuvių kalba',
		'lt' => 'Latviešu',
		'nl' => 'Nederlands',
		'sl' => 'Slovinski',
		'tr' => 'Türkçe',
	];

	private const Locale = [
		'cs' => 'cs_CZ',
		'pl' => 'pl_PL',
		'de' => 'de_DE',
		'fr' => 'fr_FR',
		'es' => 'es_ES',
		'ru' => 'ru_RU',
		'bg' => 'bg_BG',
		'it' => 'it_IT',
		'ro' => 'ro_RO',
		'fi' => 'fi_FI',
		'sv' => 'sv_SE',
		'da' => 'da_DK',
		'pt' => 'pt_PT',
		'hu' => 'hu_HU',
		'et' => 'et_EE',
		'lv' => 'lv_LV',
		'lt' => 'lt_LT',
		'nl' => 'nl_NL',
		'sl' => 'sl_SI',
		'tr' => 'tr_TR',
	];

	public function getName(): string
	{
		return self::Names[$this->value];
	}

	public function getEnglishName(): string
	{
		return self::EnglishNames[$this->value];
	}

	public function getEmoji(): string
	{
		return self::Emoji[$this->value];
	}

	public function getLocale(): string
	{
		return self::Locale[$this->value] ?? 'en_US';
	}

	public static function typecast(string $value): self
	{
		return self::from($value);
	}

	public static function typecastNullable(?string $value): ?self
	{
		return $value === null ? null : self::from($value);
	}

}
