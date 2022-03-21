<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Annotations;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("CLASS")
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class CustomSchema implements Annotation
{
	/** @var array<ForeignKey> */
	public $value;

	public function __construct($value)
	{
		$this->value = (array) $value;
	}
}
