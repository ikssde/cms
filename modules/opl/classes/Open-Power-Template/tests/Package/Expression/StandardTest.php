<?php
/**
 * The tests for standard expression parser.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * @covers Opt_Expression_Interface
 * @covers Opt_Expression_Standard
 *
 */
class Package_Expression_StandardTest extends Extra_Testcase
{
	/**
	 * The main OPT object
	 * @var Opt_Class
	 */
	protected $tpl;

	/**
	 * The main OPT compiler object
	 * @var Opt_Compiler_Class
	 */
	protected $cpl;

	/**
	 * Configures the test case.
	 */
	public function setUp()
	{
		$tpl = new Opt_Class;
		$tpl->sourceDir = '/';
		$tpl->sourceDir = 'php://memory';
		$tpl->compileDir = dirname(__FILE__).'/../../Cache/';
		$tpl->escape = false;
		$tpl->allowArrays = true;
		$tpl->allowObjects = true;
		$tpl->allowObjectCreation = true;

		$tpl->register(Opt_Class::PHP_FUNCTION, '_', '_');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'foo', 'foo');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'bar', 'bar');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'joe', 'joe');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'lol', 'lol');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'funct', 'funct');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'rotfl', 'rotfl');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'lmao1', '#1#lmao');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'lmao2', '#2,1#lmao');
		$tpl->register(Opt_Class::PHP_FUNCTION, 'lmao3', '#3,1,2:null#lmao');
		$tpl->register(Opt_Class::PHP_CLASS, 'class', '_class');
		$tpl->register(Opt_Class::PHP_CLASS, 'e', 'e');
		$tpl->register(Opt_Class::PHP_CLASS, 'u', 'u');
		$tpl->register(Opt_Class::PHP_CLASS, 'a', 'a');
		Opl_Registry::set('opl_translate', $this->getMock('Opl_Translation_Interface'));
		$tpl->setup();
		$this->tpl = $tpl;
		$this->cpl = new Opt_Compiler_Class($tpl);
		$this->cpl->setFormatList(array('#container' => 'Container'));
	} // end setUp();

	/**
	 * Cleans up after the test.
	 */
	protected function tearDown()
	{
		$this->tpl = NULL;
		$this->cpl = NULL;
	} // end tearDown();

	/**
	 * Provides the list of test cases.
	 * @return Array
	 */
	public static function dataProvider()
	{
		$exceptionClass = 'Opt_Expression_Exception';
		return array(
			array(false, '\'foo\'', '\'foo\'', 0),
			array(false, '42', '42', 0),
			array(false, '$foo', '$ctx->_data[\'foo\']', 0),
			array(false, '$2foo', '', $exceptionClass),
			array(false, '@foo', '$ctx->_vars[\'foo\']', 0),
			array(false, '$foo@bar', '$this->_tf->_(\'foo\',\'bar\')', 0),
			array(false, '$foo[\'bar\']', '$ctx->_data[\'foo\'][\'bar\']', 0),
			array(false, '$foo.bar', '$ctx->_data[\'foo\'][\'bar\']', 0),
			array(false, '$foo.bar.joe', '$ctx->_data[\'foo\'][\'bar\'][\'joe\']', 0),
			array(false, '$foo.bar[\'joe\']', '$ctx->_data[\'foo\'][\'bar\'][\'joe\']', 0),
			array(false, '$foo[\'bar\'].joe', '', $exceptionClass),
			array(false, '$foo[1]', '$ctx->_data[\'foo\'][1]', 0),
			array(false, '$foo[$i]', '$ctx->_data[\'foo\'][$ctx->_data[\'i\']]', 0),
			array(false, '$foo[$i][\'bar\']', '$ctx->_data[\'foo\'][$ctx->_data[\'i\']][\'bar\']', 0),
			array(false, '@foo.bar', '$ctx->_vars[\'foo\'][\'bar\']', 0),
			array(false, '$foo + $bar', '$ctx->_data[\'foo\']+$ctx->_data[\'bar\']', 0),
			array(false, '$foo - $bar', '$ctx->_data[\'foo\']-$ctx->_data[\'bar\']', 0),
			array(false, '-$bar', '-$ctx->_data[\'bar\']', 0),
			array(false, '$foo == $bar', '$ctx->_data[\'foo\']==$ctx->_data[\'bar\']', 0),
			array(false, '$foo + 5', '$ctx->_data[\'foo\']+5', 0),
			array(false, '$foo == 5', '$ctx->_data[\'foo\']==5', 0),
			array(false, '$foo~\'bar\'', '(string)$ctx->_data[\'foo\'].(string)\'bar\'', 0),
			array(false, '$foo == \'bar\'', '$ctx->_data[\'foo\']==\'bar\'', 0),
			array(false, '$foo gt 3', '$ctx->_data[\'foo\']>3', 0),
			array(true, '$foo is $bar + $joe', '$ctx->_data[\'foo\']=$ctx->_data[\'bar\']+$ctx->_data[\'joe\']', 0),
			array(false, '($foo + $bar) * $joe', '($ctx->_data[\'foo\']+$ctx->_data[\'bar\'])*$ctx->_data[\'joe\']', 0),
			array(false, '$joe * ($foo + $bar)', '$ctx->_data[\'joe\']*($ctx->_data[\'foo\']+$ctx->_data[\'bar\'])', 0),
			array(false, 'funct($foo + $bar)', 'funct($ctx->_data[\'foo\']+$ctx->_data[\'bar\'])', 0),
			array(false, 'funct($foo + $bar, 5, \'joe\')', 'funct($ctx->_data[\'foo\']+$ctx->_data[\'bar\'],5,\'joe\')', 0),
			array(false, 'foo($a, funct($foo + $bar, 5, \'joe\'), $c)', 'foo($ctx->_data[\'a\'],funct($ctx->_data[\'foo\']+$ctx->_data[\'bar\'],5,\'joe\'),$ctx->_data[\'c\'])', 0),
			array(false, 'foo(,,)', '', $exceptionClass),
			array(false, 'foo($a,)', '', $exceptionClass),
			array(false, '$object::funct($foo + $bar, 5, \'joe\')', '$ctx->_data[\'object\']->funct($ctx->_data[\'foo\']+$ctx->_data[\'bar\'],5,\'joe\')', 0),
			array(false, 'class::funct($foo + $bar, 5, \'joe\')', '_class::funct($ctx->_data[\'foo\']+$ctx->_data[\'bar\'],5,\'joe\')', 0),
			array(true, '$object is new class', '$ctx->_data[\'object\']=new _class', 0),
			array(false, 'funct(new class)', 'funct(new _class)', 0),
			array(true, '$object is new class($foo)', '$ctx->_data[\'object\']=new _class($ctx->_data[\'foo\'])', 0),
			array(false, 'funct(new class($foo))', 'funct(new _class($ctx->_data[\'foo\']))', 0),
			array(false, 'foo(bar(joe(1)))', 'foo(bar(joe(1)))', 0),
			array(false, 'foo()', 'foo()', 0),
			array(false, 'foo()::bar', 'foo()->bar', 0),
			array(false, 'foo()::bar()', 'foo()->bar()', 0),
			array(false, 'foo()::bar()::joe', 'foo()->bar()->joe', 0),
			array(false, '($a + $b))::bar()', '', $exceptionClass),
			array(false, 'foo(bar(joe(1))', '', $exceptionClass),
			array(false, 'foo()::', '', $exceptionClass),
			array(false, '$foo add $bar', '$ctx->_data[\'foo\']+$ctx->_data[\'bar\']', 0),
			array(false, '$foo sub $bar', '$ctx->_data[\'foo\']-$ctx->_data[\'bar\']', 0),
			array(false, '$foo mul $bar', '$ctx->_data[\'foo\']*$ctx->_data[\'bar\']', 0),
			array(false, '$foo div $bar', '$ctx->_data[\'foo\']/$ctx->_data[\'bar\']', 0),
			array(false, '$foo mod $bar', '$ctx->_data[\'foo\']%$ctx->_data[\'bar\']', 0),
	//		array(false, 'add ~ sub', '\'add\'.\'sub\'', 0),
	//		array(false, 'mul', '\'mul\'', 0),
	//		array(false, 'mul ~ div ~ mod', '\'mul\'.\'div\'.\'mod\'', 0),
		//	array(false, 'add $bar', '', $exceptionClass),
			array(false, '++$bar', '++$ctx->_data[\'bar\']', 0),
			array(false, '$bar++', '$ctx->_data[\'bar\']++', 0),
			array(false, '--$bar', '--$ctx->_data[\'bar\']', 0),
			array(false, '$bar--', '$ctx->_data[\'bar\']--', 0),
			array(false, 'foo(++$bar)', 'foo(++$ctx->_data[\'bar\'])', 0),
			array(false, 'foo($bar++)', 'foo($ctx->_data[\'bar\']++)', 0),
			array(false, 'foo($bar++)++', '', $exceptionClass),
			array(false, '++$obj::foo', '++$ctx->_data[\'obj\']->foo', 0),
			array(false, '$obj::foo++', '$ctx->_data[\'obj\']->foo++', 0),
			array(false, '++$obj::foo()', '', $exceptionClass),
			array(false, '$obj::foo()++', '', $exceptionClass),
			array(false, 'not($foo lt $bar)', '!($ctx->_data[\'foo\']<$ctx->_data[\'bar\'])', 0),
			array(false, 'not($foo lt $bar and $foo gt 6)', '!($ctx->_data[\'foo\']<$ctx->_data[\'bar\']&&$ctx->_data[\'foo\']>6)', 0),
			array(false, '$foo lt $bar and $foo gt 6', '$ctx->_data[\'foo\']<$ctx->_data[\'bar\']&&$ctx->_data[\'foo\']>6', 0),
			array(false, '$a + () + $c', '', $exceptionClass),
			array(false, '$x + (++foo() - 5) * 2', '', $exceptionClass),
			array(false, '--$obj::foo()', '', $exceptionClass),
			array(false, '++($a + $b)', '', $exceptionClass),
			array(false, '($a + $b)++', '', $exceptionClass),
			array(false, 'null', 'null', 0),
			array(false, 'false', 'false', 0),
			array(false, 'true', 'true', 0),
			array(false, 'true add false', 'true+false', 0),
			array(false, 'foo(null)', 'foo(null)', 0),
			array(false, '5 true 5', '', $exceptionClass),
			array(false, '5 false 5', '', $exceptionClass),
			array(false, '5 null 5', '', $exceptionClass),
			array(false, '$foo[null]', '$ctx->_data[\'foo\'][null]', 0),
			array(false, 'rotfl()', 'rotfl()', 0),
			array(false, 'rotfl($a)', 'rotfl($ctx->_data[\'a\'])', 0),
			array(false, 'rotfl($a, $b)', 'rotfl($ctx->_data[\'a\'],$ctx->_data[\'b\'])', 0),
			array(false, 'lmao1()', 'lmao()', 'Opt_Expression_Exception'),
			array(false, 'lmao1($a)', 'lmao($ctx->_data[\'a\'])', 0),
			array(false, 'lmao2($a, $b)', 'lmao($ctx->_data[\'b\'],$ctx->_data[\'a\'])', 0),
			array(false, 'lmao3($a, $b, $c)', 'lmao($ctx->_data[\'b\'],$ctx->_data[\'c\'],$ctx->_data[\'a\'])', 0),
			array(false, 'lmao3($a, $b)', 'lmao($ctx->_data[\'b\'],null,$ctx->_data[\'a\'])', 0),
			array(false, 'lol()', 'lol()', 0),
			array(false, 'lol($a)', 'lol($ctx->_data[\'a\'])', 0),
			array(false, 'lol($a, $b)', 'lol($ctx->_data[\'a\'],$ctx->_data[\'b\'])', 0),
			array(false, 'lol($a, $b, $c)', 'lol($ctx->_data[\'a\'],$ctx->_data[\'b\'],$ctx->_data[\'c\'])', 0),
			array(false, 'assign($foo@bar, 5)', '$this->_tf->assign(\'foo\',\'bar\',5)', 0),
			array(true, '$foo@bar is 5', '', $exceptionClass),

			// Container and compound operator stuff
			array(true, '$foo contains 1', 'Opt_Function::contains($ctx->_data[\'foo\'], 1)', 0),
			array(true, '$foo contains both 1 and 2', 'Opt_Function::contains($ctx->_data[\'foo\'], 1)&&Opt_Function::contains($ctx->_data[\'foo\'], 2)', 0),
			array(true, '$foo contains either 1 or 2', 'Opt_Function::contains($ctx->_data[\'foo\'], 1)||Opt_Function::contains($ctx->_data[\'foo\'], 2)', 0),
			array(true, '$foo contains neither 1 nor 2', '!Opt_Function::contains($ctx->_data[\'foo\'], 1)&&!Opt_Function::contains($ctx->_data[\'foo\'], 2)', 0),

			array(true, '$foo is between 1 and 5', '1 < $ctx->_data[\'foo\'] && $ctx->_data[\'foo\'] < 5', 0),
			array(true, '$foo is not between 1 and 5', '1 >= $ctx->_data[\'foo\'] || $ctx->_data[\'foo\'] >= 5', 0),
			array(true, '$foo is either 1 or 5', '1 == $ctx->_data[\'foo\'] || $ctx->_data[\'foo\'] == 5', 0),
			array(true, '$foo is neither 1 nor 5', '1 !== $ctx->_data[\'foo\'] && $ctx->_data[\'foo\'] !== 5', 0),

			array(true, '1 is in $foo', 'Opt_Function::contains($ctx->_data[\'foo\'], 1)', 0),
			array(true, '1 is not in $foo', '!Opt_Function::contains($ctx->_data[\'foo\'], 1)', 0),
			array(true, '1 is either in $foo or $bar', 'Opt_Function::contains($ctx->_data[\'foo\'], 1) || Opt_Function::contains($ctx->_data[\'bar\'], 1)', 0),
			array(true, '1 is both in $foo and $bar', 'Opt_Function::contains($ctx->_data[\'foo\'], 1) && Opt_Function::contains($ctx->_data[\'bar\'], 1)', 0),
			array(true, '1 is neither in $foo nor $bar', '!Opt_Function::contains($ctx->_data[\'foo\'], 1) && !Opt_Function::contains($ctx->_data[\'bar\'], 1)', 0),

			// Assignment stuff
			array(true, '$a = 5', '$ctx->_data[\'a\']=5', 0),
			array(true, '@a = 5', '$ctx->_vars[\'a\']=5', 0),
			array(true, '$a = $b = 5', '$ctx->_data[\'a\']=$ctx->_data[\'b\']=5', 0),
			array(true, '++$a is 5', '', $exceptionClass),
			array(true, '$a + $b is $c + $d', '$ctx->_data[\'a\']+$ctx->_data[\'b\']=$ctx->_data[\'c\']+$ctx->_data[\'d\']', 0),
			array(true, 'foo() is $c + $d', '', $exceptionClass),
			array(true, '5 is 2', '', $exceptionClass),
			array(true, '$foo is', '', $exceptionClass),
			array(true, '$foo is ($bar is 3 * (5 + 3))', '$ctx->_data[\'foo\']=($ctx->_data[\'bar\']=3*(5+3))', 0),
			array(true, '$a = (((($b = 5))))', '$ctx->_data[\'a\']=(((($ctx->_data[\'b\']=5))))', 0),
			array(true, 'rotfl($a = 5)', 'rotfl($ctx->_data[\'a\']=5)', 0),
			array(true, 'rotfl($a = 5, $b = 10)', 'rotfl($ctx->_data[\'a\']=5,$ctx->_data[\'b\']=10)', 0),
			array(true, 'is', '\'is\'', $exceptionClass),
			array(true, 'is eq is', '\'is\'==\'is\'', $exceptionClass),
			array(true, '$foo is is', '$ctx->_data[\'foo\']=\'is\'', $exceptionClass),
			// Stupid syntax misuses
			array(false, '\'Text body\'~{$brackettedVariable}', '', $exceptionClass),
			array(false, 'Text body {$variable}', '', $exceptionClass),
			array(false, 'foo(\'text\'~$foo\')', '', $exceptionClass),
			array(false, 'foo(\'text\'~$foo\")', '', $exceptionClass),
			array(false, 'foo bar', '', $exceptionClass),

			// Other issues
			array(false, '_()', '_()', 0),
			array(false, '_(\'foo\')', '_(\'foo\')', 0),

			// Expression modifiers
			array(false, 'u:\'foo\'', '\'foo\'', 0),
			array(false, 'e:\'foo\'', 'htmlspecialchars(\'foo\')', 0),
			array(false, 'a:\'foo\'', '', 'htmlspecialchars(\'foo\')'),
			array(false, '\':\'', '\':\'', 0),
			array(false, 'e::method()', 'e::method()', 0),
			array(false, 'u::method()', 'u::method()', 0),
			array(false, 'a::method()', 'a::method()', 0),
		);
	} // end dataProvider();

 	/**
 	 * @dataProvider dataProvider
 	 */
	public function testExpression($assign, $src, $dst, $result)
	{
		try
		{
			$info = $this->cpl->compileExpression($src, $assign, Opt_Compiler_Class::ESCAPE_BOTH);

			if($result === 0)
			{
				$this->assertEquals($dst, $info[0]);
				return true;
			}
			$this->fail('Exception NOT returned, but should be: '.$result);
		}
		catch(Opt_Exception $e)
		{
			if($result !== 0)
			{
				if($result != get_class($e))
				{
					$this->fail('Invalid exception returned: #'.get_class($e).', '.$result.' expected.');
				}
				return true;
			}
			$this->fail('Exception returned: #'.get_class($e).': '.$e->getMessage().' (line: '.$e->getLine().')');
		}
	} // end testExpression();
} // end Package_Instruction_InstructionTest;