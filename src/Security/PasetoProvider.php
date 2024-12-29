<?php declare(strict_types = 1);

namespace Shredio\Core\Security;

use DateTimeInterface;
use InvalidArgumentException;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version4\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Protocol\Version4;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Rules\ValidAt;
use SensitiveParameter;
use Shredio\Core\Security\Token\PasetoToken;

final class PasetoProvider implements TokenProvider
{

	private SymmetricKey $secret;

	public function __construct(
		#[SensitiveParameter]
		string $secret,
	)
	{
		$this->secret = $this->createSymmetricKeyFromBase64($secret);
	}

	public function load(string $id): ?PasetoToken
	{
		$parser = Parser::getLocal($this->secret, ProtocolCollection::v4())
			->addRule(new ValidAt());

		try {
			$token = $parser->parse($id);
		} catch (PasetoException) {
			return null;
		}

		return new PasetoToken($id, $token->getClaims());
	}

	/**
	 * @param mixed[] $payload
	 */
	public function create(array $payload, ?DateTimeInterface $expiresAt = null): PasetoToken
	{
		$token = Builder::getLocal($this->secret, new Version4())
			->setExpiration($expiresAt)
			->setIssuedAt()
			->setNotBefore();

		foreach ($payload as $key => $value) {
			$token->set($key, $value);
		}

		return new PasetoToken($token->toString(), $payload);
	}

	private function createSymmetricKeyFromBase64(
		#[SensitiveParameter]
		string $secret,
	): SymmetricKey
	{
		$encoded = base64_decode($secret, true);

		if ($encoded === false) {
			throw new InvalidArgumentException('Invalid base64 encoding of paseto secret.');
		}

		return new SymmetricKey($encoded);
	}

}
