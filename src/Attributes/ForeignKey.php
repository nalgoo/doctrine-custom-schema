<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class ForeignKey
{
	/**
	 * Constraint name
	 */
	public ?string $name;

	/**
	 * Column(s) name
	 *
	 * @var string|array<string>|null
	 */
	public string|array|null $column;

	/**
	 * Referenced table name
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
	 * @Enum({"NO_ACTION", "RESTRICT", "CASCADE", "SET_NULL"})
	 */
	public string $onUpdate = 'NO_ACTION';

	/**
	 * @Enum({"NO_ACTION", "RESTRICT", "CASCADE", "SET_NULL"})
	 */
	public string $onDelete = 'NO_ACTION';

	public function __construct(string $refTable, string|array $refColumn, ?string $name = null, string|array|null $column = null, string $onUpdate = 'NO_ACTION', string $onDelete = 'NO_ACTION')
	{
		$this->name = $name;
		$this->column = $column;
		$this->refTable = $refTable;
		$this->refColumn = $refColumn;
		$this->onUpdate = $onUpdate;
		$this->onDelete = $onDelete;
	}
}
