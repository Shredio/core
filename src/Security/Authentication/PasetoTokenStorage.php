<?php declare(strict_types = 1);

namespace Shredio\Core\Security\Authentication;

use DateTimeImmutable;
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
use Throwable;

final readonly class PasetoTokenStorage implements TokenStorage
{

	private SymmetricKey $key;

	public function __construct(
		#[SensitiveParameter]
		string $key,
	)
	{
		$this->key = $this->createSymmetricKeyFromBase64($key);
	}

	/**
	 * @param mixed[] $payload
	 */
	public function create(array $payload, ?DateTimeInterface $expiresAt = null): Token
	{
		$token = Builder::getLocal($this->key, new Version4())
			->set('payload', $payload)
			->setExpiration($expiresAt)
			->setIssuedAt()
			->setNotBefore();

		return new PasetoToken($token->toString(), $payload, $expiresAt);
	}

	public function load(string $id): ?Token
	{
		$parser = Parser::getLocal($this->key, ProtocolCollection::v4())
			->addRule(new ValidAt());

		try {
			$token = $parser->parse($id);
		} catch (PasetoException) {
			return null;
		}

		$payload = $token->get('payload');
		$exp = $token->get('exp');
		$expiration = null;

		if (!is_array($payload)) {
			return null;
		}

		if (is_string($exp)) {
			try {
				$expiration = new DateTimeImmutable($exp);
			} catch (Throwable) {}
		}

		return new PasetoToken($id, $payload, $expiration);
	}

	private function createSymmetricKeyFromBase64(
		#[SensitiveParameter]
		string $key,
	): SymmetricKey
	{
		$encoded = base64_decode($key, true);

		if ($encoded === false) {
			throw new InvalidArgumentException('Invalid base64 encoding of paseto secret.');
		}

		return new SymmetricKey($encoded);
	}

}
