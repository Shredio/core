<?php declare(strict_types = 1);

namespace Tests\Validator\Attribute;

use PHPUnit\Framework\TestCase;
use Shredio\Core\Validator\Attribute\Choice;

final class ChoiceTest extends TestCase
{

	public function testNamedArgumentsTriggerNoDeprecation(): void
	{
		$deprecations = [];
		set_error_handler(static function (int $level, string $message) use (&$deprecations): bool {
			$deprecations[] = $message;

			return true;
		}, E_USER_DEPRECATED);

		try {
			$choice = new Choice(choices: ['draft', 'published'], choicesInMessage: true);
		} finally {
			restore_error_handler();
		}

		$this->assertSame([], $deprecations);
		$this->assertSame(['draft', 'published'], $choice->choices);
		$this->assertSame('choice.messageWithChoices', $choice->message);
	}

}
