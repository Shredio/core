<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Platform\Exception;

use LogicException;
use Shredio\Core\Bridge\Doctrine\Platform\PlatformFamily;

final class UnsupportedPlatformException extends LogicException
{

	public function __construct(PlatformFamily $family)
	{
		parent::__construct(sprintf('Unsupported platform: %s', $family->value));
	}

}
