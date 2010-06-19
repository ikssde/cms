<?php
/**
 * The structure for the tests that use TestFS.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * The base class for test cases. Contains some common stuff, such as
 * preconfiguration for PHP 5.3 and PHPUnit 3.4.
 */
class Extra_Testcase extends PHPUnit_Framework_TestCase
{
	/**
	 * Protect against resetting the state of the autoloader
	 * during tests.
	 *
	 * @var array
	 */
	protected $backupStaticAttributesBlacklist = array(
        'Opl_Loader' => array('_handler', '_directory', '_libraries',
			'_mappedFiles', '_initialized', '_loaded', '_fileCheck',
			'_handleUnknownLibraries'
		)
    );
} // end Extra_Testcase;