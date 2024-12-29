<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Security;

use Shredio\Core\Security\TokenProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class PasetoAuthenticator extends AbstractAuthenticator
{

	public function __construct(
		private readonly TokenProvider $tokenProvider,
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
		$str = $this->getTokenInRequest($request);

		if ($str === null) {
			throw new CustomUserMessageAuthenticationException('No token provided.');
		}

		$token = $this->tokenProvider->load($str);

		if ($token === null) {
			throw new CustomUserMessageAuthenticationException('Invalid token.');
		}

		$payload = $token->getPayload();

		if (!isset($payload[$this->idKey])) {
			throw new CustomUserMessageAuthenticationException('Invalid token.');
		}

		return new SelfValidatingPassport(new UserBadge($payload[$this->idKey]));
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
	{
		return null;
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
	{
		return null;
	}

	private function getTokenInRequest(Request $request): ?string
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

		// Cookies
		$value = $request->cookies->get('sid', '');

		if (is_string($value) && $value !== '') {
			return $value;
		}

		return null;
	}

}
