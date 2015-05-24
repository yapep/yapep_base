<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBaseTest
 * @subpackage   Mock\Database
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBaseTest\Mock\Database;


use YapepBase\Database\MysqlConnection;

/**
 * Mock class for MysqlConnection
 *
 * @codeCoverageIgnore
 */
class MysqlConnectionMock extends MysqlConnection {

	/**
	 * Constructor.
	 *
	 * @param PdoMock $pdo   PDO connection to use
	 */
	public function __construct(PdoMock $pdo) {
		$this->connection = $pdo;
	}

	/**
	 * Opens the connection.
	 *
	 * @param array $configuration   The configuration for the connection.
	 *
	 * @return void
	 *
	 * @throws \PDOException   On connection errors.
	 */
	protected function connect(array $configuration) {
	}
}