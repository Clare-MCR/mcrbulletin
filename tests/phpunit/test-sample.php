
<?php
/**
 * Class MCRbulletinTest
 *
 * @package mcrbulletin
 */
/**
 * Sample test case.
 */
if (!class_exists('\PHPUnit\Framework\TestCase') &&
     class_exists('\PHPUnit_Framework_TestCase')) {
     class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
 }


class MCRbulletinTest extends WP_UnitTestCase {
	/**
	 * A single example test.
	 */
	function test_mcrbulletin() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}
}
