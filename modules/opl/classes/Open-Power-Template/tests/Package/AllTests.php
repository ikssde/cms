<?php
/**
 * The test suite file that configures the execution of the test cases.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('ClassTest.php');
require_once('ViewTest.php');
require_once('FunctionTest.php');
require_once dirname(__FILE__).'/Parser/AllTests.php';
require_once dirname(__FILE__).'/Inflector/AllTests.php';
require_once dirname(__FILE__).'/Xml/AllTests.php';
require_once dirname(__FILE__).'/Instruction/AllTests.php';
require_once dirname(__FILE__).'/Expression/AllTests.php';
require_once dirname(__FILE__).'/Language/AllTests.php';

class Package_AllTests extends PHPUnit_Framework_TestSuite
{

	/**
	 * Configures the suite object.
	 *
	 * @return Suite
	 */
	public static function suite()
	{
		$suite = new Package_AllTests('Package');
		$suite->addTestSuite('Package_ClassTest');
		$suite->addTestSuite('Package_ViewTest');
		$suite->addTestSuite('Package_FunctionTest');

		$suite->addTestSuite(Package_Parser_AllTests::suite());
		$suite->addTestSuite(Package_Inflector_AllTests::suite());
		$suite->addTestSuite(Package_Xml_AllTests::suite());

		$suite->addTestSuite(Package_Instruction_AllTests::suite());
		$suite->addTestSuite(Package_Expression_AllTests::suite());
		$suite->addTestSuite(Package_Language_AllTests::suite());

		$suite->addTestSuite(Package_Cdf_AllTests::suite());

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

} // end Package_AllTests;