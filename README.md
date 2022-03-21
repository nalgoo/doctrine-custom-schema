Custom Schema for Doctrine
==========================

This package allows you to define custom schema on top of your ORM model. For instance, creating foreign keys without the need of `JoinColumn`.

Install via Composer
--------------------

```
composer require nalgoo/doctrine-custom-schema
```

Usage
-----

```
$entityManager = EntityManager::create(...);  

CustomSchemaListener::register($entityManager);
```

Annotations
-----------

### ForeignKey

```
class Entity 
{
	/**
	 * @ORM\Column(name="user_id", type="integer", nullable=false)
	 * @ForeignKey(refTable="user", refColumn="id", onUpdate="CASCADE", onDelete="CASCADE")
	 */
	public int $userId;
}
```
