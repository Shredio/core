<?php declare(strict_types = 1);

namespace Tests\Doctrine\entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "posts")]
final class Post
{

	#[ORM\Id]
	#[ORM\Column(type: "integer")]
	private int $id;

	#[ORM\Column(name: 'contents', type: "string", length: 255)]
	private string $content;

}
