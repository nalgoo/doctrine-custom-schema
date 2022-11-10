<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema;

interface ConstraintNameGeneratorInterface
{
	public function getForeignKeyName(
		string $tableName,
		array $columnNames,
		string $foreignTableName,
		array $foreignColumnNames,
		int $maxIdentifierLength,
	): string;

	public function getIndexName(
		string $tableName,
		array $columnNames,
		bool $isUnique,
		int $maxIdentifierLength,
	): string;
}
