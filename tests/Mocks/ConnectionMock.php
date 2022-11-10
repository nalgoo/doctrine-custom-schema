<?php
declare(strict_types=1);

namespace Nalgoo\Doctrine\CustomSchema\Tests\Mocks;

use Doctrine\DBAL\Connection;

class ConnectionMock extends Connection
{
	/**
	 * mocked method, won't query database
	 */
	public function getDatabase()
	{
		return 'test';
	}

}
