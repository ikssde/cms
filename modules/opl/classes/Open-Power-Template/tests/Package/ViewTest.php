<?php
/**
 * The tests for Opt_View.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * @covers Opt_View
 */
class Package_ViewTest extends Extra_Testcase
{
	/**
	 * The OPT class object
	 * @var Opt_Class
	 */
	private $_tpl;

	/**
	 * Tiny helping function.
	 * 
	 * @param string $text The text to strip.
	 * @return string
	 */
	private function stripWs($text)
	{
		return trim(str_replace(array("\r", "\n"),array('', ''), $text));
	} // end stripws();

	/**
	 * Prepare a new Opt_Class object for tests.
	 */
	protected function setUp()
	{
		// @codeCoverageIgnoreStart
		$this->_tpl = new Opt_Class;
		$this->_tpl->sourceDir = './Package/templates/';
		$this->_tpl->compileDir = './Cache/';
		$this->_tpl->setup();
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
	 * @covers Opt_View::__construct
	 * @covers Opt_View::getTemplate
	 */
	public function testConstructor()
	{
		$view = new Opt_View('template.tpl');

		$this->assertEquals('template.tpl', $view->getTemplate());
	} // end testConstructor();

	/**
	 * @covers Opt_View::__construct
	 * @covers Opt_View::getCache
	 */
	public function testConstructorGetsCacheFromOpt()
	{
		$this->_tpl->setCache($obj = $this->getMock('Opt_Caching_Interface'));
		$view = new Opt_View('template.tpl');

		$this->assertSame($obj, $view->getCache());
	} // end testConstructorGetsCacheFromOpt();

	/**
	 * @covers Opt_View::setBranch
	 * @covers Opt_View::getBranch
	 */
	public function testSettingBranches()
	{
		$view = new Opt_View('template.tpl');
		$view->setBranch('branch');

		$this->assertEquals('branch', $view->getBranch());
	} // end testSettingBranches();

	/**
	 * @covers Opt_View::__set
	 * @covers Opt_View::__get
	 */
	public function testAssignMagicMethods()
	{
		$view = new Opt_View('template.tpl');
		$view->variable = 'Foo';
		$this->assertEquals('Foo', $view->variable);
	} // end testAssignMagicMethods();

	/**
	 * @covers Opt_View::assign
	 * @covers Opt_View::get
	 */
	public function testAssignStandard()
	{
		$view = new Opt_View('template.tpl');
		$view->assign('variable', 'Foo');
		$this->assertEquals('Foo', $view->get('variable'));
	} // end testAssignStandard();

	/**
	 * @covers Opt_View::assignGroup
	 * @covers Opt_View::get
	 */
	public function testAssignGroup()
	{
		$view = new Opt_View('template.tpl');
		$view->assignGroup(array(
			'var1' => 'Foo',
			'var2' => 'Bar'
		));
		$this->assertEquals('Foo', $view->get('var1'));
		$this->assertEquals('Bar', $view->get('var2'));
	} // end testAssignGroup();

	/**
	 * @covers Opt_View::assignRef
	 * @covers Opt_View::get
	 */
	public function testAssignRef()
	{
		$view = new Opt_View('template.tpl');

		$variable = 'Foo';

		$view->assignRef('variable', $variable);
		$this->assertEquals('Foo', $view->get('variable'));

		$variable = 'Bar';

		$this->assertEquals('Bar', $view->get('variable'));
	} // end testAssignRef();

	/**
	 * @covers Opt_View::get
	 */
	public function testGetAccessesToUnexistingVariable()
	{
		$view = new Opt_View('template.tpl');
		$this->assertTrue($view->get('variable') === null);
	} // end testAssignStandard();

	/**
	 * @covers Opt_View::assign
	 * @covers Opt_View::defined
	 */
	public function testAssignExists()
	{
		$view = new Opt_View('template.tpl');
		$view->assign('variable', 'Foo');
		$this->assertTrue($view->defined('variable'));
		$this->assertFalse($view->defined('foo'));
	} // end testAssignExists();

	/**
	 * @covers Opt_View::__set
	 * @covers Opt_View::__get
	 * @covers Opt_View::__unset
	 */
	public function testUnsetVariableReturnsNull()
	{
		$view = new Opt_View('template.tpl');
		$view->variable = 'Foo';
		unset($view->variable);
		$this->assertEquals(null, $view->variable);
	} // end testUnsetVariableReturnsNull();

	/**
	 * @covers Opt_View::__set
	 * @covers Opt_View::__get
	 * @covers Opt_View::__isset
	 */
	public function testVariableExists()
	{
		$view = new Opt_View('template.tpl');
		$view->variable = 'Foo';
		$this->assertTrue(isset($view->variable));
		$this->assertFalse(isset($view->foo));
	} // end testVariableExists();

	/**
	 * @covers Opt_View::assign
	 * @covers Opt_View::get
	 */
	public function testAssignNormalMethods()
	{
		$view = new Opt_View('template.tpl');
		$view->assign('variable', 'Foo');
		$this->assertEquals('Foo', $view->get('variable'));
	} // end testAssignNormalMethods();

	/**
	 * @covers Opt_View::assignGlobal
	 * @covers Opt_View::getGlobal
	 */
	public function testAssignGlobalVars()
	{
		Opt_View::assignGlobal('variable', 'Foo');
		$this->assertEquals('Foo', Opt_View::getGlobal('variable'));
	} // end testAssignGlobalVars();

	/**
	 * @covers Opt_View::setTemplate
	 * @covers Opt_View::getTemplate
	 */
	public function testChangingTemplate()
	{
		$view = new Opt_View('tpl1.tpl');
		$this->assertEquals('tpl1.tpl', $view->getTemplate());
		$view->setTemplate('tpl2.tpl');
		$this->assertEquals('tpl2.tpl', $view->getTemplate());
	} // end testChangingTemplate();

	/**
	 * @covers Opt_View::setTemplate
	 * @covers Opt_View::getTemplate
	 */
	public function testSettingParser()
	{
		$view = new Opt_View('foo.tpl');
		$this->assertEquals('Opt_Parser_Xml', $view->getParser());

		$view->setParser('Opt_Parser_Html');
		$this->assertEquals('Opt_Parser_Html', $view->getParser());
	} // end testSettingParser();

	/**
	 * @covers Opt_View::_parse
	 * @covers Opt_View::_preprocess
	 */
	public function testRunningCompilationInStandardMode()
	{
		$this->_tpl->compileMode = Opt_Class::CM_DEFAULT;
		file_put_contents('./Package/templates/mod_template.tpl', 'TEST 1');
		$view = new Opt_View('mod_template.tpl');
		$view->setParser('Opt_Parser_Quirks');

		$output = new Opt_Output_Return;
		$this->assertEquals('TEST 1', $this->stripWs($output->render($view)));

		$this->assertEquals('TEST 1', $this->stripWs($output->render($view)));

		sleep(2);

		file_put_contents('./Package/templates/mod_template.tpl', 'TEST 2');

		$this->assertEquals('TEST 2', $this->stripWs($output->render($view)));
	} // end testRunningCompilationInStandardMode();

	/**
	 * @covers Opt_View::_parse
	 * @covers Opt_View::_preprocess
	 */
	public function testRunningCompilationInPerformanceMode()
	{
		sleep(2);
		$this->_tpl->compileMode = Opt_Class::CM_DEFAULT;
		file_put_contents('./Package/templates/mod_template.tpl', 'TEST 1');

		$view = new Opt_View('mod_template.tpl');
		$view->setParser('Opt_Parser_Quirks');

		$output = new Opt_Output_Return;
		$this->assertEquals('TEST 1', $this->stripWs($output->render($view)));

		$this->_tpl->compileMode = Opt_Class::CM_PERFORMANCE;

		$this->assertEquals('TEST 1', $this->stripWs($output->render($view)));
		sleep(2);

		file_put_contents('./Package/templates/mod_template.tpl', 'TEST 2');

		$this->assertEquals('TEST 1', $this->stripWs($output->render($view)));
	} // end testRunningCompilationInPerformanceMode();

	/**
	 * @covers Opt_View::_parse
	 */
	public function testNoNoticesOnUnexistingVars()
	{
		$output = $this->getMock('Opt_Output_Interface');
		$view = new Opt_View('view_no_notices.tpl');

		// It should not get any error.
		ob_start();
		$view->_parse($output);
		ob_end_clean();
	} // end testNoNoticesOnUnexistingVars();

} // end Package_ViewTest;