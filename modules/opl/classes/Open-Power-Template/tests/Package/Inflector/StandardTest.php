<?php
/**
 * The tests for standard inflector.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * @covers Opt_Inflector_Standard
 */
class Package_Inflector_StandardTest extends Extra_Testcase
{
	/**
	 * @covers Opt_Inflector_Standard::__construct
	 * @covers Opt_Inflector_Standard::hasStream
	 * @covers Opt_Inflector_Standard::getStream
	 */
	public function testConstructorReadsStreamFromConfig()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo';
		$mock->stdStream = 'file';
		$mock->expects($this->once())
			->method('_securePath')
			->with($this->equalTo('foo'));

		$inflector = new Opt_Inflector_Standard($mock);
		$this->assertTrue($inflector->hasStream('file'));
	} // end testConstructorReadsStreamsFromConfig();

	/**
	 * @covers Opt_Inflector_Standard::__construct
	 * @covers Opt_Inflector_Standard::hasStream
	 * @covers Opt_Inflector_Standard::getStream
	 */
	public function testConstructorReadsStreamsFromConfig()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = array('file' => 'foo', 'bar' => 'joe');
		$mock->stdStream = 'file';
		$mock->expects($this->exactly(2))
			->method('_securePath');

		$inflector = new Opt_Inflector_Standard($mock);
		$this->assertTrue($inflector->hasStream('file'));
		$this->assertTrue($inflector->hasStream('bar'));
	} // end testConstructorReadsStreamsFromConfig();

	/**
	 * @covers Opt_Inflector_Standard::__construct
	 * @expectedException Opt_InvalidOptionValue_Exception
	 */
	public function testConstructorShouldThrowException()
	{
		$mock = $this->getMock('Opt_Class');
		$inflector = new Opt_Inflector_Standard($mock);
	} // end testConstructorShouldThrowException();


	/**
	 * @covers Opt_Inflector_Standard::addStream
	 */
	public function testAddStreamNormally()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo';
		$mock->stdStream = 'file';

		$inflector = new Opt_Inflector_Standard($mock);

		$mock->expects($this->once())
			->method('_securePath')
			->with('joe');

		$inflector->addStream('bar', 'joe');

		$this->assertTrue($inflector->hasStream('bar'));
		$this->assertEquals('joe', $inflector->getStream('bar'));
	} // end testAddStreamNormally();

	/**
	 * @covers Opt_Inflector_Standard::addStream
	 */
	public function testAddStreamWithoutObfuscation()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo';
		$mock->stdStream = 'file';
		$inflector = new Opt_Inflector_Standard($mock);

		$mock->expects($this->never())
			->method('_securePath');

		$inflector->addStream('bar', 'joe', false);

		$this->assertTrue($inflector->hasStream('bar'));
		$this->assertEquals('joe', $inflector->getStream('bar'));
	} // end testAddStreamWithoutObfuscation();

	/**
	 * @covers Opt_Inflector_Standard::addStream
	 * @expectedException Opt_ObjectExists_Exception
	 */
	public function testAddExistingStream()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo';
		$mock->stdStream = 'file';
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->addStream('bar', 'joe');
		$inflector->addStream('bar', 'joe');
	} // end testAddExistingStream();

	/**
	 * @covers Opt_Inflector_Standard::getStream
	 * @expectedException Opt_ObjectNotExists_Exception
	 */
	public function testGetStreamThrowsException()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo';
		$mock->stdStream = 'file';
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->getStream('nully');
	} // end testGetStreamThrowsException();

	/**
	 * @covers Opt_Inflector_Standard::removeStream
	 */
	public function testRemoveStreamRemoves()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo';
		$mock->stdStream = 'file';
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->addStream('bar', 'joe');
		$this->assertTrue($inflector->hasStream('bar'));
		$inflector->removeStream('bar');
		$this->assertFalse($inflector->hasStream('bar'));
	} // end testRemoveStreamRemoves();

	/**
	 * @covers Opt_Inflector_Standard::removeStream
	 * @expectedException Opt_ObjectNotExists_Exception
	 */
	public function testRemoveStreamThrowsException()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo';
		$mock->stdStream = 'file';
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->removeStream('bar');
	} // end testRemoveStreamThrowsException();

	/**
	 * @covers Opt_Inflector_Standard::getSourcePath
	 */
	public function testGetSourcePathReturnsDefaultStreamPath()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$mock->allowRelativePaths = false;
		$inflector = new Opt_Inflector_Standard($mock);
		$this->assertEquals('foo://file.tpl', $inflector->getSourcePath('file.tpl'));
	} // end testGetSourcePathReturnsDefaultStreamPath();

	/**
	 * @covers Opt_Inflector_Standard::getSourcePath
	 * @expectedException Opt_NotSupported_Exception
	 */
	public function testGetSourcePathDoesNotAllowRelativePathsDefault()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$mock->allowRelativePaths = false;
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->getSourcePath('../file.tpl');
	} // end testGetSourcePathDoesNotAllowRelativePathsDefault();

	/**
	 * @covers Opt_Inflector_Standard::getSourcePath
	 * @expectedException Opt_ObjectNotExists_Exception
	 */
	public function testGetSourcePathDefaultStreamMissing()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'joe';
		$mock->allowRelativePaths = false;
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->getSourcePath('file.tpl');
	} // end testGetSourcePathDefaultStreamMissing();

	/**
	 * @covers Opt_Inflector_Standard::getSourcePath
	 */
	public function testGetSourcePathRelativePathsWorkDefault()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$mock->allowRelativePaths = true;
		$inflector = new Opt_Inflector_Standard($mock);
		$this->assertEquals('foo://../file.tpl', $inflector->getSourcePath('../file.tpl'));
	} // end testGetSourcePathRelativePathsWork();

	/**
	 * @covers Opt_Inflector_Standard::getSourcePath
	 */
	public function testGetSourcePathReturnsStreamPath()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$mock->allowRelativePaths = false;
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->addStream('bar', 'joe://');
		$this->assertEquals('joe://file.tpl', $inflector->getSourcePath('bar:file.tpl'));
	} // end testGetSourcePathReturnsDefaultStreamPath();

	/**
	 * @covers Opt_Inflector_Standard::getSourcePath
	 * @expectedException Opt_NotSupported_Exception
	 */
	public function testGetSourcePathDoesNotAllowRelativePaths()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$mock->allowRelativePaths = false;
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->addStream('bar', 'joe://');
		$inflector->getSourcePath('bar:../file.tpl');
	} // end testGetSourcePathDoesNotAllowRelativePathsDefault();

	/**
	 * @covers Opt_Inflector_Standard::getSourcePath
	 * @expectedException Opt_ObjectNotExists_Exception
	 */
	public function testGetSourcePathStreamMissing()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'joe';
		$mock->allowRelativePaths = false;
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->getSourcePath('bar:file.tpl');
	} // end testGetSourcePathDefaultStreamMissing();

	/**
	 * @covers Opt_Inflector_Standard::getSourcePath
	 */
	public function testGetSourcePathRelativePathsWork()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$mock->allowRelativePaths = true;
		$inflector = new Opt_Inflector_Standard($mock);
		$inflector->addStream('bar', 'joe://');
		$this->assertEquals('joe://../file.tpl', $inflector->getSourcePath('bar:../file.tpl'));
	} // end testGetSourcePathRelativePathsWork();

	/**
	 * @covers Opt_Inflector_Standard::getCompiledPath
	 */
	public function testGetCompiledPathReplacesDangerousSymbols()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$inflector = new Opt_Inflector_Standard($mock);

		$this->assertEquals('test__a__b__c.tpl.php', $inflector->getCompiledPath('test/a:b\\c.tpl', array()));
	} // end testGetCompiledPathReplacesDangerousSymbols();

	/**
	 * @covers Opt_Inflector_Standard::getCompiledPath
	 */
	public function testGetCompiledPathPrependsCompileId()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$mock->compileId = 'foo';
		$inflector = new Opt_Inflector_Standard($mock);

		$this->assertEquals('foo_test__a__b__c.tpl.php', $inflector->getCompiledPath('test/a:b\\c.tpl', array()));
	} // end testGetCompiledPathPrependsCompileId();

	/**
	 * @covers Opt_Inflector_Standard::getCompiledPath
	 */
	public function testGetCompiledPathBuildsPathForInheritance()
	{
		$mock = $this->getMock('Opt_Class');
		$mock->sourceDir = 'foo://';
		$mock->stdStream = 'file';
		$inflector = new Opt_Inflector_Standard($mock);

		$this->assertEquals('bar.tpl/foo.tpl/test__a__b__c.tpl.php', $inflector->getCompiledPath('test/a:b\\c.tpl', array('foo.tpl', 'bar.tpl')));
	} // end testGetCompiledPathBuildsPathForInheritance();
} // end Package_Inflector_StandardTest;