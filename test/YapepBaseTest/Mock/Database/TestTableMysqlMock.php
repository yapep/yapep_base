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

/**
 * Mock class for PDOStatement
 *
 * @codeCoverageIgnore
 */
class TestTableMysqlMock extends \YapepBase\Database\MysqlTable {
	/**
	 * The name of the table.
	 *
	 * @var string
	 */
	protected $tableName = 'test';

	/**
	 * The default connection name what should be used for the database connection.
	 *
	 * @var string
	 */
	protected $defaultDbConnectionName = 'test';

	/** The id field. */
	const FIELD_ID = 'id';
	/** The key field. */
	const FIELD_KEY = 'key';
	/** The value field. */
	const FIELD_VALUE = 'value';

	/**
	 * Returns the fields of the table.
	 *
	 * @return array
	 */
	public function getFields() {
		return array(
			self::FIELD_ID,
			self::FIELD_KEY,
			self::FIELD_VALUE
		);
	}
}