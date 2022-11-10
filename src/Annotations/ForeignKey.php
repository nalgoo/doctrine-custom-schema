<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Annotations;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Annotation class for defining custom Foreign Keys
 *
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class ForeignKey implements Annotation
{
	/**
	 * Constraint name
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Column(s) name
	 *
	 * @var string|array<string>
	 */
	public string|array $column;

	/**
	 * Referenced table name
	 *
	 * @var string
	 * @Required
	 */
	public string $refTable;

	/**
	 * Referenced column(s) name
	 *
	 * @var string|array<string>
	 */
	public string|array $refColumn;

	/**
	 * @var string
	 * @Enum({"NO_ACTION", "RESTRICT", "CASCADE", "SET_NULL"})
	 */
	public string $onUpdate = 'NO_ACTION';

	/**
	 * @var string
	 * @Enum({"NO_ACTION", "RESTRICT", "CASCADE", "SET_NULL"})
	 */
	public string $onDelete = 'NO_ACTION';
}
