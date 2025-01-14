<?php declare(strict_types = 1);

namespace Shredio\Core\Validator;

interface ValidationGroupProvider
{

	/**
	 * @return string[]|null
	 */
	public function provideValidationGroups(): ?array;

}
