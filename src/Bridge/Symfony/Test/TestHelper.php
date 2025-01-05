<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use LogicException;
use PHPUnit\Framework\TestCase;
use Shredio\Core\Security\AccountId;
use Shredio\Core\Test\Authentication\Actor;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

final class TestHelper
{

	private static TestHelper $singleton;

	public static ?KernelInterface $kernel = null;

	private ?Actor $actorToSignIn = null;

	private bool $initialized = false;

	private int $allocateActorId = 3;

	private function __construct(
		private TestCase $context,
	)
	{
	}

	public function reset(): void
	{
		$this->initialized = false;
		$this->actorToSignIn = null;
		$this->allocateActorId = 3;
	}

	public static function getTestBench(KernelInterface $kernel): TestBench
	{
		/** @var TestBench */
		return self::getContainer($kernel)->get('testbench');
	}

	public static function getContainer(KernelInterface $kernel): Container
	{
		try {
			/** @var Container */
			return $kernel->getContainer()->get('test.service_container');
		} catch (ServiceNotFoundException $e) {
			throw new LogicException('Could not find service "test.service_container". Try updating the "framework.test" config to "true".', 0, $e);
		}
	}

	private function check(): void
	{
		if (!$this->initialized) {
			throw new LogicException('TestHelper not initialized, probably setUp was not called');
		}
	}

	public function getActorToSignIn(): ?Actor
	{
		$this->check();

		return $this->actorToSignIn;
	}

	public function createActor(Actor $actor): Actor
	{
		$this->check();

		$actor->setId(AccountId::from($this->allocateActorId++));

		return $actor;
	}

	private function fillActor(Actor $actor): void
	{
		$actor->getAuthorActor()->setId(AccountId::from(1));
		$actor->getSignedActor()?->setId(AccountId::from(2));
	}

	private function setContext(TestCase $context): void
	{
		if ($this->context === $context) {
			return;
		}

		$this->context = $context;
		$this->reset();
	}

	public static function getInstance(TestCase $case): self
	{
		self::$singleton ??= new self($case);
		self::$singleton->setContext($case);

		return self::$singleton;
	}

	public static function getClient(KernelInterface $kernel): KernelBrowser
	{
		try {
			/** @var KernelBrowser $client */
			$client = $kernel->getContainer()->get('test.client');
		} catch (ServiceNotFoundException) {
			if (class_exists(KernelBrowser::class)) {
				throw new \LogicException('You cannot create the client used in functional tests if the "framework.test" config is not set to true.');
			}
			throw new \LogicException('You cannot create the client used in functional tests if the BrowserKit component is not available. Try running "composer require symfony/browser-kit".');
		}

		return $client;
	}

	public function tryFillActor(Actor $actor): void
	{
		if ($actor->isFilled()) {
			return;
		}

		$this->fillActor($actor);
	}

	public function initialize(): void
	{
		if ($this->initialized) {
			throw new LogicException('TestHelper already initialized, probably tearDown was not called');
		}

		$this->actorToSignIn = null;

		foreach ($this->context->providedData() as $data) {
			if ($data instanceof Actor) {
				$this->tryFillActor($data);

				$this->actorToSignIn = $data->getSignedActor();
			}
		}

		$this->initialized = true;
	}

}
