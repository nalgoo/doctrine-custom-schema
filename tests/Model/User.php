<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Tests\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user')]
#[ORM\Entity]
class User
{
	#[ORM\Column(name: 'id', type: 'integer', nullable: false)]
	#[ORM\Id]
	#[ORM\GeneratedValue(strategy: 'IDENTITY')]
	public int $id;
}
