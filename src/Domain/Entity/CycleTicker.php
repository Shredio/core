<?php declare(strict_types = 1);

namespace Shredio\Core\Domain\Entity;

use Cycle\Annotated\Annotation\Column;

abstract class CycleTicker
{

	#[Column(type: 'string(20)', primary: true)]
	protected string $id;

	#[Column(type: 'string(120)')]
	protected string $name;

	#[Column(type: 'float')]
	protected float $price = 0;

	#[Column(type: 'float', nullable: true)]
	protected ?float $change = null;

	#[Column(type: 'float', name: 'change_percentage', nullable: true)]
	protected ?float $percentageChange = null;

	#[Column(type: 'string(3)', nullable: true)]
	protected ?string $currency;

	#[Column(type: 'string', nullable: true)]
	protected ?string $image = null;

	public function __construct(string $id)
	{
		$this->id = $id;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getPrice(): float
	{
		return $this->price;
	}

	public function getPercentageChange(): ?float
	{
		return $this->percentageChange;
	}

	public function setCurrency(?string $currency): void
	{
		$this->currency = $currency;
	}

	public function getCurrency(): ?string
	{
		return $this->currency;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): static
	{
		$this->name = $name;

		return $this;
	}

	public function getImage(): ?string
	{
		return $this->image;
	}

	public function setImage(?string $image): static
	{
		$this->image = $image;

		return $this;
	}

	public function getChange(): ?float
	{
		return $this->change;
	}

	public function setChange(float $change): static
	{
		$this->change = $change;

		return $this;
	}

}
