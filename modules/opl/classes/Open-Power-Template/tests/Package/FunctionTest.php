<?php
/**
 * The tests for Opt_View.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('./Extra/TestFS.php');
require_once('./Extra/TestFSBase.php');

/**
 * @covers Opt_Function
 */
class Package_FunctionTest extends Extra_TestFSBase
{

	/**
	 * Configuration method.
	 * @param Opt_Class $tpl
	 */
	public function configure(Opt_Class $tpl)
	{
		$tpl->parser = 'Opt_Parser_Html';
	} // end configure();

	/**
	 * Provides the list of test cases.
	 * @return Array
	 */
	public static function dataProvider()
	{
		return array(0 =>
			array('absolute_1.txt'),
			array('absolute_2.txt'),
			array('average_1.txt'),
			array('capitalize_1.txt'),
			array('capitalize_2.txt'),
			array('contains_key_1.txt'),
			array('contains_key_2.txt'),
			array('contains_key_3.txt'),
			array('count_1.txt'),
			array('count_chars_1.txt'),
			array('count_words_1.txt'),
			array('cycle_1.txt'),
			array('cycle_2.txt'),
			array('date_1.txt'),
			array('entity_1.txt'),
			array('entity_2.txt'),
			array('firstof_1.txt'),
			array('indent_1.txt'),
			array('isimage_1.txt'),
			array('isurl_1.txt'),
			array('lower_1.txt'),
			array('lower_2.txt'),
			array('money_1.txt'),
			array('money_2.txt'),
			array('nl2br_1.txt'),
			array('nl2br_2.txt'),
			array('number_1.txt'),
			array('number_2.txt'),
			array('number_3.txt'),
			array('range_1.txt'),
			array('regex_replace_1.txt'),
			array('replace_1.txt'),
			array('scalar_1.txt'),
			array('spacify_1.txt'),
			array('spacify_2.txt'),
			array('stddev_1.txt'),
			array('strip_1.txt'),
			array('strip_2.txt'),
			array('strip_tags_1.txt'),
			array('strip_tags_2.txt'),
			array('sum_1.txt'),
			array('truncate_1.txt'),
			array('upper_1.txt'),
			array('upper_2.txt'),
			array('wordwrap_1.txt'),
			array('wordwrap_2.txt'),
		);
	} // end dataProvider();

 	/**
 	 * @dataProvider dataProvider
 	 */
	public function testFunctions($testCase)
	{
		return $this->_checkTest(dirname(__FILE__).'/TestsFunction/'.$testCase);
	} // end testFunctions();
} // end Package_FunctionTest;