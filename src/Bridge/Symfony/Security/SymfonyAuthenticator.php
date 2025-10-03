<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Security;

use LogicException;
use Shredio\Auth\Entity\InMemoryUserEntity;
use Shredio\Core\Intl\Language;
use Shredio\Core\Security\Token\Token;
use Shredio\Core\Security\TokenProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class SymfonyAuthenticator extends AbstractAuthenticator
{

	/**
	 * @param UserProviderInterface<UserInterface> $userProvider
	 */
	public function __construct(
		private readonly TokenProvider $tokenProvider,
		private readonly UserProviderInterface $userProvider,
		private readonly string $sessionKey = 'sid',
		private readonly string $idKey = 'id',
	)
	{
	}

	public function supports(Request $request): bool
	{
		return $request->cookies->has($this->sessionKey) || $request->headers->has('Authorization');
	}

	public function authenticate(Request $request): Passport
	{
		$str = $this->getTokenInHeader($request);

		if ($str === null) {
			$str = $this->getTokenInCookies($request);
		}

		if ($str === null) {
			throw new CustomUserMessageAuthenticationException('No token provided.');
		}

		$token = $this->tokenProvider->load($str);

		if ($token === null) {
			throw new CustomUserMessageAuthenticationException('Invalid token.');
		}

		$payload = $token->getPayload();

		$id = $payload[$this->idKey] ?? null;

		if (!is_scalar($id)) {
			throw new CustomUserMessageAuthenticationException('Invalid token.');
		}

		return new SelfValidatingPassport(new UserBadge(
			(string) $id,
			$this->loadUser(...),
			$this->getAttributes($token),
		));
	}

	/**
	 * @param array<string, mixed> $attributes
	 */
	private function loadUser(string $identifier, array $attributes): UserInterface
	{
		if (!isset($attributes['roles'])) {
			return $this->userProvider->loadUserByIdentifier($identifier);
		}
		if ($identifier === '') {
			throw new LogicException('User identifier is empty.');
		}

		return new InMemoryUserEntity($identifier, $attributes['roles']); // deprecated
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
	{
		return null;
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
	{
		return null;
	}

	private function getTokenInHeader(Request $request): ?string
	{
		// Authorization header
		$value = $request->headers->get('Authorization', '');

		if (!is_string($value)) {
			return null;
		}

		if (str_starts_with($value, 'Bearer ')) {
			$value = substr($value, 7);
		}

		if ($value !== '') {
			return $value;
		}

		return null;
	}

	private function getTokenInCookies(Request $request): ?string
	{
		$value = $request->cookies->get('sid', '');

		if (is_string($value) && $value !== '') {
			return $value;
		}

		return null;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getAttributes(Token $token): array
	{
		$attributes = [];

		$payload = $token->getPayload();

		if (isset($payload['roles']) && is_array($payload['roles'])) {
			$attributes['roles'] = $payload['roles'];
		}

		if (isset($payload['language']) && is_string($payload['language']) && $lang = Language::tryFrom($payload['language'])) {
			$attributes['language'] = $lang;
		}

		return $attributes;
	}

}
