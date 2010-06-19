<?php
/**
 * The test suite file that configures the execution of the XML node test.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('ExpressionsTest.php');
require_once('ModifiersTest.php');

class Package_Language_AllTests extends PHPUnit_Framework_TestSuite
{

	/**
	 * Configures the suite object.
	 *
	 * @return Suite
	 */
	public static function suite()
	{
		$suite = new Package_Language_AllTests('Package_Language');
		$suite->addTestSuite('Package_Language_ExpressionsTest');
		$suite->addTestSuite('Package_Language_ModifiersTest');

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

} // end Package_Xml_AllTests;