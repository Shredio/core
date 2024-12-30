<?php declare(strict_types = 1);

namespace Shredio\Core\Payload;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class FieldWarningPayload implements WarningPayload
{

	/**
	 * @param array<string, mixed> $details
	 */
	public function __construct(
		public string|TranslatableInterface $message,
		public string $field,
		public array $details = [],
	)
	{
	}

	protected function getType(): string
	{
		return 'warning';
	}

	/**
	 * @param bool $debugMode
	 * @return mixed[]
	 */
	public function toArray(TranslatorInterface $translator, bool $debugMode = false): array
	{
		$payload = [
			'type' => $this->getType(),
			'message' => is_string($this->message) ? $this->message : $this->message->trans($translator),
			'field' => $this->field,
		];

		if ($this->details) {
			$payload['details'] = $this->details;
		}

		return $payload;
	}

}
