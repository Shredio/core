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

	public static function typecast(string $value): self
	{
		return self::from($value);
	}

}
