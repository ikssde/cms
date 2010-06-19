<?php
/**
 * The tests for Opl_Loader.
 *
 * WARNING: Requires PHPUnit 3.4!
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once 'vfsStream/vfsStream.php';

/**
 * @covers Opl_Loader
 * @runTestsInSeparateProcesses
 */
class Package_LoaderTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Checks if the loader tests have been started.
	 * @var boolean
	 */
	private $_started = false;

	/**
	 * Generates a file content for the virtual file system.
	 * @param string $file The file name
	 * @param string $content The content for the file.
	 */
	protected function _createFile($file, $content)
	{
		return vfsStream::newFile($file)->withContent('<'."?php\necho \"".$content.'\\n";');
	} // end _filePrint();

	/**
	 * Prepare a virtual filesystem for testing.
	 */
	protected function setUp()
	{
		// @codeCoverageIgnoreStart
		if(!$this->_started)
		{
			$config = parse_ini_file(dirname(__FILE__).'/../../paths.ini', true);
			require_once($config['libraries']['Opl'].'Base.php');
			Opl_Loader::register();

			$this->_started = true;
		}


		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot($root = new vfsStreamDirectory('libs'));

		$root->addChild(new vfsStreamDirectory('Foo'));
		$root->addChild(new vfsStreamDirectory('Bar'));
		$root->addChild(new vfsStreamDirectory('Joe'));
		$root->addChild(new vfsStreamDirectory('NewLib'));
		$root->addChild(new vfsStreamDirectory('Opl'));
		$root->addChild($this->_createFile('Bar.php', 'BAR.PHP'));

		// Contents of Opl/
		$root->getChild('Opl')->addChild(vfsStream::newFile('Test.php')->withContent(''));

		// Contents of NewLib/
		$root->getChild('NewLib')->addChild($this->_createFile('Class.php', 'NEWLIB/CLASS.PHP'));
		$root->getChild('NewLib')->addChild($this->_createFile('Foo.php', 'NEWLIB/FOO.PHP'));

		// Contents of Joe/
		$root->getChild('Joe')->addChild($this->_createFile('Class.php', 'JOE/CLASS.PHP'));
		$root->getChild('Joe')->addChild($this->_createFile('Foo.php', 'JOE/FOO.PHP'));
		$root->getChild('Joe')->addChild($this->_createFile('Bar.php', 'JOE/BAR.PHP'));
		$root->getChild('Joe')->addChild($this->_createFile('Exception.php', 'JOE/EXCEPTION.PHP'));
		$root->getChild('Joe')->addChild(new vfsStreamDirectory('Foo'));
		$root->getChild('Joe/Foo')->addChild($this->_createFile('Exception.php', 'JOE/FOO/EXCEPTION.PHP'));

		// Contents of Bar/
		$root->getChild('Bar')->addChild($this->_createFile('Class.php', 'BAR/CLASS.PHP'));

		// Contents of Foo/
		$root->getChild('Foo')->addChild($this->_createFile('Bar.php', 'FOO/BAR.PHP'));
		$root->getChild('Foo')->addChild($this->_createFile('Class.php', 'FOO/CLASS.PHP'));
		// @codeCoverageIgnoreStop
	} // end setUp();

	/**
	 * Clean-up.
	 */
	protected function tearDown()
	{
		// @codeCoverageIgnoreStart

		// @codeCoverageIgnoreStop
	} // end tearDown();

	public function testGettingDirectory()
	{
		Opl_Loader::setDirectory('vfs://');
		$this->assertEquals('vfs://', Opl_Loader::getDirectory());
	} // end testGettingDirectory();

	/**
	 * @cover Opl_Loader::load
	 */
	public function testSimpleClassLoading()
	{
		Opl_Loader::setDirectory('vfs://');

		ob_start();
		Opl_Loader::load('Bar_Class');

		$this->assertEquals(ob_get_clean(), "BAR/CLASS.PHP\n");
		return true;
	} // end testSimpleClassLoading();

	/**
	 * @cover Opl_Loader::load
	 */
	public function testLoadNamespaceClass()
	{
		Opl_Loader::setDirectory('vfs://');

		ob_start();
		Opl_Loader::load('Foo\Bar');

		$this->assertEquals(ob_get_clean(), "FOO/BAR.PHP\n");
		return true;
	} // end testLoadNamespaceClass();

	/**
	 * @cover Opl_Loader::load
	 */
	public function testLoadMoreClasses()
	{
		Opl_Loader::setDirectory('vfs://');

		ob_start();
		Opl_Loader::load('Joe_Foo');
		Opl_Loader::load('Joe_Bar');

		$this->assertEquals(ob_get_clean(), "JOE/FOO.PHP\nJOE/BAR.PHP\n");
		return true;
	} // end testLoadMoreClasses();

	/**
	 * @cover Opl_Loader::load
	 * @cover Opl_Loader::addLibrary
	 */
	public function testLoadDirectory()
	{
		Opl_Loader::addLibrary('Joe', array('directory' => 'vfs://Joe/'));

		ob_start();
		Opl_Loader::load('Joe_Foo');
		Opl_Loader::load('Joe_Bar');

		$this->assertEquals(ob_get_clean(), "JOE/FOO.PHP\nJOE/BAR.PHP\n");
		return true;
	} // end testLoadDirectory();

	/**
	 * @cover Opl_Loader::load
	 * @cover Opl_Loader::addLibrary
	 */
	public function testLoadBasePath()
	{
		Opl_Loader::addLibrary('Joe', array('basePath' => 'vfs://'));

		ob_start();
		Opl_Loader::load('Joe_Foo');
		Opl_Loader::load('Joe_Bar');

		$this->assertEquals(ob_get_clean(), "JOE/FOO.PHP\nJOE/BAR.PHP\n");
		return true;
	} // end testLoadBasePath();

	/**
	 * @cover Opl_Loader::load
	 * @cover Opl_Loader::addLibrary
	 */
	public function testLoadWholeNameByDirectory()
	{
		Opl_Loader::addLibrary('Bar', array('directory' => 'vfs://Bar/'));

		ob_start();
		Opl_Loader::load('Bar');

		$this->assertEquals(ob_get_clean(), "BAR.PHP");
		return true;
	} // end testLoadWholeNameByDirectory();

	/**
	 * @cover Opl_Loader::load
	 * @cover Opl_Loader::addLibrary
	 */
	public function testLoadWholeNameByBasePath()
	{
		Opl_Loader::addLibrary('Bar', array('basePath' => 'vfs://'));

		ob_start();
		Opl_Loader::load('Bar');

		$this->assertEquals(ob_get_clean(), "BAR.PHP\n");
		return true;
	} // end testLoadWholeNameByBasePath();
} // end Package_LoaderTest;