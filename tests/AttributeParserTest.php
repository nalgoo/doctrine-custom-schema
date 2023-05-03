<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Tests;

use Nalgoo\Doctrine\CustomSchema\AttributeParser;
use Nalgoo\Doctrine\CustomSchema\Tests\Model\Comment;
use PHPUnit\Framework\TestCase;

class AttributeParserTest extends TestCase
{
	public function testExtractForeignKeys()
	{
		$parser = new AttributeParser();

		$reflectionClass = new \ReflectionClass(Comment::class);

		$foreignKeys = $parser->extractForeignKeys($reflectionClass);

		$this->assertCount(2, $foreignKeys);

		$fk1 = $foreignKeys[0];
		$this->assertEquals('FK_post_reference' , $fk1->getName());
		$this->assertEqualsCanonicalizing(['post_id'], $fk1->getLocalColumns());
		$this->assertEquals('post', $fk1->getForeignTableName());
		$this->assertEqualsCanonicalizing(['id'], $fk1->getForeignColumns());
		$this->assertEqualsCanonicalizing(['NO ACTION', 'SET NULL'], $fk1->getOptions());

		$fk2 = $foreignKeys[1];
		$this->assertEquals('', $fk2->getName());
		$this->assertEqualsCanonicalizing(['user_id'], $fk2->getLocalColumns());
		$this->assertEquals('user', $fk2->getForeignTableName());
		$this->assertEqualsCanonicalizing(['id'], $fk2->getForeignColumns());
		$this->assertEqualsCanonicalizing(['CASCADE', 'CASCADE'], $fk2->getOptions());
	}
}
