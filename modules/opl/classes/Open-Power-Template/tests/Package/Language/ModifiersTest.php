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
class Package_Language_ModifiersTest extends Extra_TestFSBase
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

		$tpl->register(Opt_Class::MODIFIER, 'a', 'Extra_Mock_Modifier::modifier');
		$tpl->register(Opt_Class::MODIFIER, 'r', 'Extra_Mock_Modifier::modifier');
		$tpl->register(Opt_Class::MODIFIER, 'p', 'htmlspecialchars');
		$tpl->register(Opt_Class::MODIFIER, 'b', null);
	} // end configure();


	/**
	 * Provides the list of test cases.
	 * @return Array
	 */
	public static function dataProvider()
	{
		return array(0 =>
			
			array('default_2.txt'),
			array('custom_1.txt'),
			array('custom_2.txt'),
			array('attributes_1.txt'),
			array('default_1.txt'),
			);
	} // end dataProvider();

 	/**
 	 * @dataProvider dataProvider
 	 */
	public function testModifiersInHtmlParser($testCase)
	{
		$this->_type = 0;
		return $this->_checkTest(dirname(__FILE__).'/TestsModifiers/'.$testCase);
	} // end testModifiersInHtmlParser();

 	/**
 	 * @dataProvider dataProvider
 	 */
	public function testModifiersInXmlParser($testCase)
	{
		$this->_type = 1;
		return $this->_checkTest(dirname(__FILE__).'/TestsModifiers/'.$testCase);
	} // end testModifiersInXmlParser();

} // end Package_Language_ModifiersTest;