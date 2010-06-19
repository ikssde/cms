<?php
/**
 * The test suite file that configures the execution of the test cases for parsers.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('XmlTest.php');
require_once('HtmlTest.php');

class Package_Parser_AllTests extends PHPUnit_Framework_TestSuite
{

	/**
	 * Configures the suite object.
	 *
	 * @return Suite
	 */
	public static function suite()
	{
		$suite = new Package_Parser_AllTests('Package_Parser');
		$suite->addTestSuite('Package_Parser_XmlTest');
		$suite->addTestSuite('Package_Parser_HtmlTest');
	//	$suite->addTestSuite('Package_Parser_QuirksTest');

		return $suite;
	} // end suite();

	/**
	 * Configures the OPL autoloader and installs the libraries.
	 */
	protected function setUp()
	{
		/* currently null */
	} // end setUp();

	/**
	 * Shuts down the test procedure.
	 */
	protected function tearDown()
	{
		/* currently null */
	} // end tearDown();

} // end Package_Instruction_AllTests;