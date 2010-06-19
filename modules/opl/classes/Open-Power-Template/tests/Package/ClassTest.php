<?php
/**
 * The tests for Opt_Class.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */


/**
 * @covers Opt_Class
 */
class Package_ClassTest extends Extra_Testcase
{
	/**
	 * The OPT class object
	 * @var Opt_Class
	 */
	private $_tpl;

	/**
	 * Prepare a new Opt_Class object for tests.
	 */
	protected function setUp()
	{
		// @codeCoverageIgnoreStart
		$this->_tpl = new Opt_Class;
		// @codeCoverageIgnoreStop
	} // end setUp();

	/**
	 * Free the existing Opt_Class object.
	 */
	protected function tearDown()
	{
		Opl_Registry::register('opt', null);
		unset($this->_tpl);
	} // end tearDown();

	/**
	 * @covers Opt_Class::setup
	 */
	public function testSetupRegistersTranslationInterface()
	{
		$this->_tpl->sourceDir = 'php://memory';
		$this->_tpl->compileDir = 'php://memory';

		Opl_Registry::register('opl_translate', null);
		Opl_Registry::register('opl_translate', $obj = $this->getMock('Opl_Translation_Interface'));
		$this->_tpl->setup();

		$this->assertSame($obj, $this->_tpl->getTranslationInterface());		
	} // end testSetupRegistersTranslationInterface();

	/**
	 * @covers Opt_Class::setup
	 */
	public function testSetupEnablesDebugMode()
	{
		$this->_tpl->sourceDir = 'php://memory';
		$this->_tpl->compileDir = 'php://memory';
		$this->_tpl->debugConsole = false;

		Opl_Registry::setState('opl_debug_console', true);

		$this->_tpl->setup();

		$this->assertTrue($this->_tpl->debugConsole);
		$this->_tpl->debugConsole = false;
		Opl_Registry::setState('opl_debug_console', false);
	} // end testSetupEnablesDebugMode();

	/**
	 * @covers Opt_Class::setup
	 */
	public function testSetupLoadsExternalConfiguration()
	{
		$this->_tpl->pluginDataDir = 'php://memory';
		$this->_tpl->sourceDir = 'fake';
		$this->_tpl->setup(array(
			'stripWhitespaces' => false
		));

		$this->assertFalse($this->_tpl->stripWhitespaces);
	} // end testSetupLoadsExternalConfiguration();

	/**
	 * @covers Opt_Class::setup
	 * @covers Opt_Class::getInflector
	 */
	public function testSetupCreatesInflector()
	{
		$this->_tpl->sourceDir = 'php://memory';
		$this->_tpl->compileDir = 'php://memory';
		$this->_tpl->setup();
		$this->assertTrue($this->_tpl->getInflector() instanceof Opt_Inflector_Standard);
	} // end testSetupCreatesInflector();

	/**
	 * @covers Opt_Class::setup
	 * @covers Opt_Class::setInflector
	 */
	public function testSetupCDoesNotCreateInitializedInflector()
	{
		$this->_tpl->setInflector($obj = $this->getMock('Opt_Inflector_Interface'));
		$this->_tpl->setup();
		$this->assertSame($obj, $this->_tpl->getInflector());
	} // end testSetupCreatesInflector();

	/**
	 * @covers Opt_Class::register
	 */
	public function testRegisterIsLockedAfterInit()
	{
		try
		{
			$this->_tpl->sourceDir = 'php://memory';
			$this->_tpl->compileDir = 'php://memory';
			$this->_tpl->setup();

			$this->_tpl->register(Opt_Class::PHP_CLASS, 'foo', 'bar');
		}
		catch(Opt_Initialization_Exception $exception)
		{
			return true;
		}
		$this->fail('Exception Opt_Initialization_Exception not returned');
	} // end testRegisterIsLockedAfterInit();

	/**
	 * @covers Opt_Class::register
	 */
	public function testRegisterFromArray()
	{
		$this->_tpl->register(Opt_Class::PHP_CLASS, array(
			'foo' => 'bar',
			'joe' => 'goo'
		));

		$out = $this->_tpl->_getList('_classes');
		if(!isset($out['foo']) || $out['foo'] != 'bar')
		{
			$this->fail('foo key not set');
		}
		if(!isset($out['joe']) || $out['joe'] != 'goo')
		{
			$this->fail('joe key not set');
		}
		return true;
	} // end testRegisterFromArray();

	/**
	 * @covers Opt_Class::register
	 */
	public function testRegisterShortForm()
	{
		$this->_tpl->register(Opt_Class::OPT_NAMESPACE, 'Foo');

		$out = $this->_tpl->_getList('_namespaces');
		$this->assertContains('Foo', $out);
		return true;
	} // end testRegisterShortForm();

	/**
	 * @covers Opt_Class::register
	 */
	public function testRegisterLongForm()
	{
		$this->_tpl->register(Opt_Class::OPT_COMPONENT, 'Foo', 'Bar');

		$out = $this->_tpl->_getList('_components');
		$this->assertArrayHasKey('Foo', $out);
		$this->assertContains('Bar', $out);
		return true;
	} // end testRegisterLongForm();

	/**
	 * @covers Opt_Class::register
	 */
	public function testRegisterFormat()
	{
		$this->_tpl->register(Opt_Class::OPT_FORMAT, 'Foo');
		$this->_tpl->register(Opt_Class::OPT_FORMAT, 'Bar', 'Bar_Joe');

		$out = $this->_tpl->_getList('_formats');
		$this->assertArrayHasKey('Foo', $out);
		$this->assertContains('Opt_Format_Foo', $out);

		$this->assertArrayHasKey('Bar', $out);
		$this->assertContains('Bar_Joe', $out);
		return true;
	} // end testRegisterFormat();

	/**
	 * @covers Opt_Class::register
	 */
	public function testRegisterInstruction()
	{
		$this->_tpl->register(Opt_Class::OPT_INSTRUCTION, 'Foo');
		$this->_tpl->register(Opt_Class::OPT_INSTRUCTION, 'Bar', 'Bar_Joe');

		$out = $this->_tpl->_getList('_instructions');
		$this->assertArrayHasKey('Foo', $out);
		$this->assertContains('Opt_Instruction_Foo', $out);

		$this->assertArrayHasKey('Bar', $out);
		$this->assertContains('Bar_Joe', $out);
		return true;
	} // end testRegisterInstruction();

	/**
	 * @covers Opt_Class::setBufferState
	 * @covers Opt_Class::getBufferState
	 */
	public function testBufferCounter1()
	{
		$this->assertFalse($this->_tpl->getBufferState('test1'));
		$this->_tpl->setBufferState('test1', false);
		$this->assertFalse($this->_tpl->getBufferState('test1'));
		$this->_tpl->setBufferState('test1', true);
		$this->assertTrue($this->_tpl->getBufferState('test1'));
		return true;
	} // end testBufferCounter1();

	/**
	 * @covers Opt_Class::setBufferState
	 * @covers Opt_Class::getBufferState
	 */
	public function testBufferCounter2()
	{
		$this->assertFalse($this->_tpl->getBufferState('test2'));
		$this->_tpl->setBufferState('test2', true);
		$this->assertTrue($this->_tpl->getBufferState('test2'));
		$this->_tpl->setBufferState('test2', true);
		$this->assertTrue($this->_tpl->getBufferState('test2'));
		return true;
	} // end testBufferCounter2();

	/**
	 * @covers Opt_Class::setBufferState
	 * @covers Opt_Class::getBufferState
	 */
	public function testBufferCounter3()
	{
		$this->assertFalse($this->_tpl->getBufferState('test3'));
		$this->_tpl->setBufferState('test3', true);
		$this->assertTrue($this->_tpl->getBufferState('test3'));
		$this->_tpl->setBufferState('test3', true);
		$this->assertTrue($this->_tpl->getBufferState('test3'));
		$this->_tpl->setBufferState('test3', false);
		$this->assertTrue($this->_tpl->getBufferState('test3'));
		$this->_tpl->setBufferState('test3', false);
		$this->assertFalse($this->_tpl->getBufferState('test3'));
		return true;
	} // end testBufferCounter3();

	/**
	 * @covers Opt_Class::setCache
	 * @covers Opt_Class::getCache
	 */
	public function testCacheSet()
	{
		$this->_tpl->setCache($obj = $this->getMock('Opt_Caching_Interface'));
		$this->assertSame($obj, $this->_tpl->getCache());
		return true;
	} // end testCacheSet();

	/**
	 * @covers Opt_Class::setCache
	 * @covers Opt_Class::getCache
	 */
	public function testCacheReset()
	{
		$this->_tpl->setCache($obj = $this->getMock('Opt_Caching_Interface'));
		$this->assertSame($obj, $this->_tpl->getCache());
		$this->_tpl->setCache();
		$this->assertEquals(null, $this->_tpl->getCache());
		return true;
	} // end testCacheReset();

} // end Package_ClassTest;