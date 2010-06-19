<?php
/**
 * The structure for the tests that use TestFS.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

class Extra_TestFSBase extends Extra_Testcase
{
	/**
	 * The main OPT object.
	 * @var Opt_Class
	 */
	protected $tpl;

	/**
	 * The test was...
	 * @var string
	 */
	private $_test;

	/**
	 * Sets up everything.
	 */
	protected function setUp()
	{
		$tpl = new Opt_Class;
		$tpl->sourceDir = 'test://templates/';
		$tpl->compileDir = dirname(__FILE__).'/../Cache/';
		$tpl->compileMode = Opt_Class::CM_REBUILD;
		$tpl->stripWhitespaces = false;
		$tpl->prologRequired = true;
		$this->configure($tpl);
		$tpl->setup();
		$this->tpl = $tpl;
	} // end setUp();

	/**
	 * Allows to configure the main object.
	 * @param Opt_Class $tpl
	 */
	public function configure(Opt_Class $tpl)
	{
		/* null */
	} // end configure();

	/**
	 * Finalizes everything.
	 */
	protected function tearDown()
	{
		$opt = Opl_Registry::get('opt');
		$opt->dispose();
		Opl_Registry::set('opt', null);
		Opt_View::clear();
		unset($this->tpl);
	} // end tearDown();

	/**
	 * Strips the certain white characters from the output in order to
	 * give more reliable comparisons.
	 *
	 * @param String $text Original text.
	 * @return String
	 */
	private function stripWs($text)
	{
		return str_replace(array("\r", "\n"),array('', ''), $text);
	} // end stripws();

	/**
	 * Checks the test using TestFS.
	 *
	 * @param String $test Test case path
	 * @return Boolean
	 */
	protected function _checkTest($test)
	{
		$this->_test = $test;
		Extra_TestFS::loadFilesystem($test);
		$view = new Opt_View('test.tpl');
		if(file_exists('test://data.php'))
		{
			eval(file_get_contents('test://data.php'));
		}

		$out = new Opt_Output_Return;
		$expected = file_get_contents('test://expected.txt');

		if(strpos($expected, 'OUTPUT') === 0)
		{
			// This test shoud give correct results
			try
			{
				$result = $out->render($view);
				$this->assertEquals($this->stripWs(trim(file_get_contents('test://result.txt'))), $this->stripWs(trim($result)));
			}
			catch(Opt_Exception $e)
			{
				$this->fail('Exception returned: #'.get_class($e).': '.$e->getMessage());
			}
		}
		else
		{
			// This test should generate an exception
			$expected = trim($expected);
			try
			{
				$out->render($view);
			}
			catch(Opt_Exception $e)
			{
				if($expected != get_class($e))
				{
					$this->fail('Invalid exception returned: #'.get_class($e).', '.$expected.' expected.');
				}
				return true;
			}
			$this->fail('Exception NOT returned, but should be: '.$expected);
		}
	} // end _checkTest();
} // end Extra_TestFSBase;