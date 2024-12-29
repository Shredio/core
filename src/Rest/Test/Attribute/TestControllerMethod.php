<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class TestControllerMethod
{

	/**
	 * @param array{ class-string, non-empty-string } $action
	 */
	public function __construct(
		public array $action,
	)
	{
	}

}
