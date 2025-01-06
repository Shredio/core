<?php declare(strict_types = 1);

namespace Shredio\Core\Validator\Attribute;

use Attribute;
use BackedEnum;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Symfony;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Choice extends Symfony\Choice
{

	public string $message = 'choice.message';
	public string $multipleMessage = 'choice.multipleMessage';
	public string $minMessage = 'choice.min';
	public string $maxMessage = 'choice.max';

	/**
	 * @param mixed[]|string $options
	 * @param mixed[]|null $choices An array of choices (required unless a callback is specified)
	 * @param callable|string|null $callback Callback method to use instead of the choice option to get the choices
	 * @param bool|null $multiple Whether to expect the value to be an array of valid choices (defaults to false)
	 * @param bool|null $strict This option defaults to true and should not be used
	 * @param int<0, max>|null $min Minimum of valid choices if multiple values are expected
	 * @param positive-int|null $max Maximum of valid choices if multiple values are expected
	 * @param string[]|null $groups
	 * @param bool|null $match Whether to validate the values are part of the choices or not (defaults to true)
	 * @param class-string<BackedEnum>|null $enumClass
	 */
	public function __construct(
		array|string $options = [],
		?array $choices = null,
		callable|string|null $callback = null,
		?bool $multiple = null,
		?bool $strict = null,
		?int $min = null,
		?int $max = null,
		?string $message = null,
		?string $multipleMessage = null,
		?string $minMessage = null,
		?string $maxMessage = null,
		?array $groups = null,
		mixed $payload = null,
		?bool $match = null,
		?string $enumClass = null,
		bool $choicesInMessage = false,
	)
	{
		if ($enumClass) {
			if ($choices) {
				throw new InvalidArgumentException('Choices must be null when using enumClass');
			}

			$choices = $this->getChoicesFromEnum($enumClass::cases());
		}

		if ($choicesInMessage) {
			$message = $message ?? 'choice.messageWithChoices';
			$multipleMessage = $multipleMessage ?? 'choice.multipleMessageWithChoices';
		}

		parent::__construct(
			$options,
			$choices,
			$callback,
			$multiple,
			$strict,
			$min,
			$max,
			$message,
			$multipleMessage,
			$minMessage,
			$maxMessage,
			$groups,
			$payload,
			$match,
		);
	}

	/**
	 * @param BackedEnum[] $cases
	 * @return array<string|int>
	 */
	private function getChoicesFromEnum(array $cases): array
	{
		$choices = [];

		foreach ($cases as $case) {
			$choices[] = $case->value;
		}

		return $choices;
	}

	public function validatedBy(): string
	{
		return parent::class . 'Validator';
	}

}
