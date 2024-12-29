<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Type;

final class TextTypeLength
{

	public const int TinyText = 255;
	public const int Text = 65535;
	public const int MediumText = 16777215;
	public const null LongText = null;

}
