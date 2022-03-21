<?php

namespace Nalgoo\Doctrine\CustomSchema\Tests\Model;

use Doctrine\ORM\Mapping as ORM;
use Nalgoo\Doctrine\CustomSchema\Annotations\CustomSchema;
use Nalgoo\Doctrine\CustomSchema\Annotations\ForeignKey;

/**
 * @ORM\Entity
 * @ORM\Table(name="comment")
 * @CustomSchema({
 *     @ForeignKey(name="FK_post_reference", column="post_id", refTable="post", refColumn="id", onDelete="SET_NULL")
 * })
 */
class Comment
{
	/**
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	public int $id;

	/**
	 * @ORM\Column(name="user_id", type="integer", nullable=false)
	 * @ForeignKey(refTable="user", refColumn="id", onUpdate="CASCADE", onDelete="CASCADE")
	 */
	public int $userId;

	/**
	 * @ORM\Column(name="post_id", type="integer", nullable=false)
	 */
	public int $postId;
}
