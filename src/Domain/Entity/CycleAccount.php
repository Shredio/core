<?php declare(strict_types = 1);

namespace Shredio\Core\Domain\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Table\Index;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

#[Index(columns: ['nick'], unique: true)]
#[Index(columns: ['email'], unique: true)]
abstract class CycleAccount
{

	#[Column(type: 'primary')]
	protected int $id;

	#[NotBlank]
	#[Regex('#^[a-z0-9_]+$#', 'Must contains only a-z, 0-9 and _', payload: ['attribute' => false])]
	#[Length(max: 20)]
	#[Column(type: 'string(60)')]
	protected string $nick;

	#[NotBlank]
	#[Email]
	#[Length(max: 120)]
	#[Column(type: 'string(120)')]
	protected string $email;

	#[Column(type: 'string', nullable: true)]
	protected ?string $avatar = null;

	public function __construct(string $nick, string $email)
	{
		$this->nick = $nick;
		$this->email = $email;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getNick(): string
	{
		return $this->nick;
	}

	public function setNick(string $nick): static
	{
		$this->nick = $nick;

		return $this;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): static
	{
		$this->email = $email;

		return $this;
	}

	public function getAvatar(): ?string
	{
		return $this->avatar;
	}

	public function setAvatar(?string $avatar): static
	{
		$this->avatar = $avatar;

		return $this;
	}

}
