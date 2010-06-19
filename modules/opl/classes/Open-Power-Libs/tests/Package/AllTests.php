<?php
/**
 * The test suite file that configures the execution of the test cases.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('LoaderTest.php');
require_once('RegistryTest.php');
require_once('ErrorHandlerTest.php');

require_once dirname(__FILE__).'/Getopt/AllTests.php';

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
		$suite->addTestSuite('Package_LoaderTest');
		$suite->addTestSuite('Package_RegistryTest');
		$suite->addTestSuite('Package_ErrorHandlerTest');

		$suite->addTestSuite(Package_Getopt_AllTests::suite());

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