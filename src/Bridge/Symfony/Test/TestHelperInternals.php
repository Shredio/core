<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use LogicException;
use Shredio\Core\Security\AccountId;
use Shredio\Core\Struct\LazyValue;
use Shredio\Core\Test\Authentication\Actor;
use Shredio\Core\Test\TestData;

final class TestHelperInternals
{

	private bool $initialized = false;

	private ?Actor $actorToSignIn = null;

	private int $actorId = 3;

	/**
	 * @param LazyValue<TestData|null> $testData
	 */
	public function __construct(
		private readonly TestHelper $helper,
		private readonly LazyValue $testData,
	)
	{
	}

	public function initialize(): void
	{
		if ($this->initialized) {
			throw new LogicException('TestHelper already initialized, probably tearDown was not called');
		}

		$testData = $this->testData->getValue();

		if ($testData) {
			$this->tryFillActor($testData->actor);

			if ($testData->hasGroup('BC')) {
				$this->actorToSignIn = $testData->actor;
			}
		}

		$this->initialized = true;
	}

	public function createActor(Actor $actor): Actor
	{
		$author = $actor->hasAuthor() ? $actor->getAuthorActor() : null;
		$signed = $actor->getSignedActor();

		$author?->setId(AccountId::from($this->actorId++));

		if ($author !== $signed) {
			$signed?->setId(AccountId::from($this->actorId++));
		}

		return $actor;
	}

	public function tryFillActor(Actor $actor): void
	{
		if ($actor->isFilled()) {
			return;
		}

		$this->fillActor($actor);
	}

	public function getActorToSignIn(): ?Actor
	{
		return $this->actorToSignIn;
	}

	private function fillActor(Actor $actor): void
	{
		$actor->getAuthorActor()->setId(AccountId::from(1));
		$actor->getSignedActor()?->setId(AccountId::from(2));
	}

	public function finalize(): void
	{
		$testData = $this->testData->getValue();

		if ($testData) {
			if (!$testData->expectation->used && !$testData->hasGroup('BC')) {
				throw new LogicException('HttpExpectation was not used.');
			}
		}

		$this->helper->reset();
	}

	public function reset(): void
	{
		$this->initialized = false;
		$this->actorToSignIn = null;
		$this->actorId = 3;
	}

}
