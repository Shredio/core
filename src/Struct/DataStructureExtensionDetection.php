<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

trait DataStructureExtensionDetection
{

	private static ?bool $usePecl = null;

	private static function usePecl(): bool
	{
		return self::$usePecl ??= extension_loaded('ds');
	}

}
