<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 4096)]
final class TestBench
{

	private ?UserInterface $user = null;

	private bool $setUser = false;

	public bool $throwExceptions = false;

	public function __construct(
		private readonly TokenStorageInterface $tokenStorage,
	)
	{
	}

	public function loginUser(?UserInterface $user): void
	{
		$this->user = $user;
		$this->setUser = true;
	}

	public function onKernelRequest(): void
	{
		if ($this->setUser) {
			if ($user = $this->user) {
				$this->tokenStorage->setToken(new TestBrowserToken($user->getRoles(), $user));
			} else {
				$this->tokenStorage->setToken(null);
			}
		}
	}

	public function reset(): void
	{
		$this->setUser = false;
		$this->user = null;
	}

}
