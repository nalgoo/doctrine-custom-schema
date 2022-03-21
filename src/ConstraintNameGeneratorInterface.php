<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema;

interface ConstraintNameGeneratorInterface
{
	public function getForeignKeyName(
		string $tableName,
		array $columnNames,
		string $foreignTableName,
		array $foreignColumnNames
	): string;
}
