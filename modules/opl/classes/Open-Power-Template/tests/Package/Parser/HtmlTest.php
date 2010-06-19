<?php
/**
 * The tests for HTML parser.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('./Extra/TestFS.php');
require_once('./Extra/TestFSBase.php');

/**
 * @covers Opt_Compiler_Class
 * @covers Opt_Parser_Html
 */
class Package_Parser_HtmlTest extends Extra_TestFSBase
{

	/**
	 * Configuration method.
	 * @param Opt_Class $tpl
	 */
	public function configure(Opt_Class $tpl)
	{
		$tpl->parser = 'Opt_Parser_Html';
		$tpl->useExpressionNamespaces = false;
		$tpl->register(Opt_Class::OPT_NAMESPACE, 'ns1');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'ecf', 'Package_Parser_HtmlTest::testEcf');
	} // end configure();

	/**
	 * Provides the list of test cases.
	 * @return Array
	 */
	public static function dataProvider()
	{
		return array(0 =>
			array('tags_1.txt'),
			array('tags_2.txt'),
			array('tags_3.txt'),
			array('tags_4.txt'),
			array('tags_5.txt'),
			array('tags_6.txt'),
			array('tags_7.txt'),
			array('tags_8.txt'),
			array('tags_9.txt'),
			array('attributes_1.txt'),
			array('attributes_2.txt'),
			array('attributes_3.txt'),
			array('attributes_4.txt'),
			array('tags_2.txt'),
			array('tags_3.txt'),
			array('tags_4.txt'),
			array('tags_5.txt'),
			array('tags_6.txt'),
			array('tags_7.txt'),
			array('prolog_1.txt'),
			array('prolog_2.txt'),
			array('prolog_3.txt'),
			array('prolog_4.txt'),
			array('dtd_1.txt'),
			array('dtd_2.txt'),
			array('dtd_3.txt'),
			array('cdata_1.txt'),
			array('cdata_2.txt'),
			array('cdata_3.txt'),
			array('cdata_4.txt'),
			array('cdata_5.txt'),
			array('comments_1.txt'),
			array('comments_2.txt'),
			array('comments_3.txt'),
			array('comments_4.txt'),
			array('entities_1.txt'),
			array('entities_2.txt'),
			array('entities_3.txt'),
			array('entities_4.txt'),
			array('entities_5.txt'),
			array('entities_6.txt'),
			array('entities_7.txt'),
			array('entities_8.txt'),
			array('expressions_1.txt'),
			array('expressions_2.txt'),
			array('expressions_3.txt'),
		);
	} // end dataProvider();

 	/**
 	 * @dataProvider dataProvider
 	 */
	public function testParserHtml($testCase)
	{
		return $this->_checkTest(dirname(__FILE__).'/TestsHtml/'.$testCase);
	} // end testParserHtml();

	/**
	 * This function is necessary to complete the entitiy tests.
	 *
	 * @static
	 * @param String $text The text.
	 * @return String "OK" if the entities were replaced with the corresponding characters.
	 */
	static public function testEcf($text)
	{
		if($text == '<>&')
		{
			return 'OK';
		}
		return 'FAIL';
	} // end testEcf();
} // end Package_Parser_XmlTest;