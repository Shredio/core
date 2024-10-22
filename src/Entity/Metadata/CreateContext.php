<?php declare(strict_types = 1);

namespace Shredio\Core\Entity\Metadata;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class CreateContext extends Context
{

}
