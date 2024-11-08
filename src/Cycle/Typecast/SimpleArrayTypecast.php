<?php declare(strict_types = 1);

namespace Shredio\Core\Cycle\Typecast;

use Cycle\ORM\Parser\CastableInterface;
use Cycle\ORM\Parser\UncastableInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class SimpleArrayTypecast implements CastableInterface, UncastableInterface
{

	/** @var mixed[] */
	private array $rules = [];

	/**
	 * @param mixed[] $rules
	 * @return mixed[]
	 */
	public function setRules(array $rules): array
	{
		foreach ($rules as $key => $rule) {
			if ($rule === 'uuid') {
				unset($rules[$key]);

				$this->rules[$key] = $rule;
			}
		}

		return $rules;
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function cast(array $data): array
	{
		foreach ($this->rules as $column => $rule) {
			if (!isset($data[$column])) {
				continue;
			}

			$data[$column] = explode(',', $data[$column]);
		}

		return $data;
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function uncast(array $data): array
	{
		foreach ($this->rules as $column => $rule) {
			if (!isset($data[$column]) || !is_array($data[$column])) {
				continue;
			}

			$data[$column] = implode(',', $data[$column]);
		}

		return $data;
	}

}