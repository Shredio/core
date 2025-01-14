<?php declare(strict_types = 1);

namespace Shredio\Core\Rest;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\Http\Message\ResponseInterface;

/**
 * @template T of object
 */
final class RestOperationBuilder
{

	/** @var callable(int $guardMode, mixed[] $options): ResponseInterface */
	private readonly mixed $runner;

	/** @var array<string, mixed> */
	private array $options = [];

	/**
	 * @param 'create'|'read'|'update'|'delete' $type
	 * @param callable(int $guardMode, mixed[] $options): ResponseInterface $runner
	 */
	public function __construct(
		private readonly string $type,
		callable $runner,
		private int $guardMode,
	)
	{
		$this->runner = $runner;
	}

	public function validationMode(bool $enabled = true): self
	{
		if (!in_array($this->type, ['create', 'update'], true)) {
			throw new LogicException('Validation mode is only available for create and update operations.');
		}

		$this->options['validationMode'] = $enabled;

		return $this;
	}

	/**
	 * @param callable(T $entity, EntityManagerInterface $em): void $callback
	 */
	public function beforeFlush(callable $callback): self
	{
		$this->options[RestOperations::BeforeFlush] = $callback;

		return $this;
	}

	/**
	 * @param mixed[] $context
	 */
	public function setSerializationContext(array $context): self
	{
		$this->options[RestOperations::SerializationContext] = $context;

		return $this;
	}

	public function setGuardMode(int $guardMode): self
	{
		$this->guardMode = $guardMode;

		return $this;
	}

	public function run(): ResponseInterface
	{
		return ($this->runner)($this->guardMode, $this->options);
	}

}
