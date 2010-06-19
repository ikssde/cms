<?php
/**
 * The tests for attribute parsing.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('./Extra/TestFS.php');
require_once('./Extra/TestFSBase.php');

/**
 * @covers Opt_Compiler_Class
 */
class Package_Language_ExpressionsTest extends Extra_TestFSBase
{
	protected $_type = 0;

	/**
	 * Configuration method.
	 * @param Opt_Class $tpl
	 */
	public function configure(Opt_Class $tpl)
	{
		if($this->_type == 0)
		{
			$tpl->parser = 'Opt_Parser_Html';
		}
		else
		{
			$tpl->parser = 'Opt_Parser_Xml';
		}
		$tpl->register(Opt_Class::EXPR_ENGINE, 'test', 'Extra_Mock_Expression');
	} // end configure();


	/**
	 * Provides the list of test cases.
	 * @return Array
	 */
	public static function dataProvider()
	{
		return array(0 =>
			array('instruction_1.txt'),
			array('newattr_1.txt'),
			array('newattr_2.txt'),
			array('newattr_3.txt'),
			array('text_1.txt'),
			array('text_2.txt'),
			array('oldattr_1.txt'),
			array('oldattr_2.txt'),
			array('oldinstruction_1.txt'),
			array('oldinstruction_2.txt'),
		);
	} // end dataProvider();

 	/**
 	 * @dataProvider dataProvider
 	 */
	public function testExpressionsInHtmlParser($testCase)
	{
		$this->_type = 0;
		return $this->_checkTest(dirname(__FILE__).'/TestsExpressions/'.$testCase);
	} // end testExpressionsInHtmlParser();

 	/**
 	 * @dataProvider dataProvider
 	 */
	public function testExpressionsInXmlParser($testCase)
	{
		$this->_type = 1;
		return $this->_checkTest(dirname(__FILE__).'/TestsExpressions/'.$testCase);
	} // end testExpressionsInXmlParser();

} // end Package_Language_ExpressionsTest;