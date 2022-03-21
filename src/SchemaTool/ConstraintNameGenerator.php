<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\SchemaTool;

use Nalgoo\Doctrine\CustomSchema\ConstraintNameGeneratorInterface;

class ConstraintNameGenerator implements ConstraintNameGeneratorInterface
{
	public function getForeignKeyName(
		string $tableName,
		array $columnNames,
		string $foreignTableName,
		array $foreignColumnNames,
	): string {
		return sprintf('fk_%s_%s', $tableName, implode('_', $columnNames));
	}
}
