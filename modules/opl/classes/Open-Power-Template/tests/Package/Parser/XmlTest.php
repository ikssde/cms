<?php
/**
 * The tests for XML parser.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('./Extra/TestFS.php');
require_once('./Extra/TestFSBase.php');

/**
 * @covers Opt_Compiler_Class
 * @covers Opt_Parser_Xml
 */
class Package_Parser_XmlTest extends Extra_TestFSBase
{

	/**
	 * Configuration method.
	 * @param Opt_Class $tpl
	 */
	public function configure(Opt_Class $tpl)
	{
		$tpl->parser = 'Opt_Parser_Xml';
		$tpl->useExpressionNamespaces = false;
		$tpl->register(Opt_Class::OPT_NAMESPACE, 'ns1');
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
			array('attributes_1.txt'),
			array('namespaces_1.txt'),
			array('namespaces_2.txt'),
			array('namespaces_3.txt'),
			array('namespaces_4.txt'),
			array('comments_1.txt'),
		);
	} // end dataProvider();

 	/**
 	 * @dataProvider dataProvider
 	 */
	public function testParserXml($testCase)
	{
		return $this->_checkTest(dirname(__FILE__).'/TestsXml/'.$testCase);
	} // end testParserXml();
} // end Package_Parser_XmlTest;