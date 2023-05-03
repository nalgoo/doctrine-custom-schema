<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CustomSchema
{
	/** @var array<ForeignKey> */
	public $value;

	public function __construct($value)
	{
		$this->value = (array) $value;
	}
}
