<?php
/**
 * The tests for Opt_Xml_Buffer
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * @covers Opt_Xml_Buffer
 */
class Package_Xml_BufferTest extends Extra_Testcase
{
	/**
	 * The tested object.
	 * @var Opt_Xml_Buffer
	 */
	private $_obj;

	/**
	 * Sets up the object to test. We use Opt_Xml_Element to test
	 * the basic interface.
	 */
	public function setUp()
	{
		// @codeCoverageIgnoreStart
		$this->_obj = new Opt_Xml_Element('opt:foo');
		// @codeCoverageIgnoreStop
	} // end setUp();

	/**
	 * Removes the tested object.
	 */
	public function tearDown()
	{
		// @codeCoverageIgnoreStart
		if($this->_obj !== null)
		{
			$this->_obj->dispose();
			$this->_obj = null;
		}
		// @codeCoverageIgnoreStop
	} // end tearDown();

	/**
	 * @covers Opt_Xml_Buffer::addAfter
	 * @covers Opt_Xml_Buffer::getBuffer
	 */
	public function testAddAfter()
	{
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'foo');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'bar');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_AFTER, 'foo');

		$buf1 = $this->_obj->getBuffer(Opt_Xml_Buffer::TAG_BEFORE);
		$buf2 = $this->_obj->getBuffer(Opt_Xml_Buffer::TAG_AFTER);

		$this->assertEquals(2, sizeof($buf1));
		$this->assertEquals(1, sizeof($buf2));

		$this->assertEquals(array('foo', 'bar'), $buf1);
		$this->assertEquals(array('foo'), $buf2);
	} // end testAddAfter();

	/**
	 * @covers Opt_Xml_Buffer::addAfter
	 * @covers Opt_Xml_Buffer::getBuffer
	 */
	public function testAddBefore()
	{
		$this->_obj->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'foo');
		$this->_obj->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'bar');
		$this->_obj->addBefore(Opt_Xml_Buffer::TAG_AFTER, 'foo');

		$buf1 = $this->_obj->getBuffer(Opt_Xml_Buffer::TAG_BEFORE);
		$buf2 = $this->_obj->getBuffer(Opt_Xml_Buffer::TAG_AFTER);

		$this->assertEquals(2, sizeof($buf1));
		$this->assertEquals(1, sizeof($buf2));

		$this->assertEquals(array('bar', 'foo'), $buf1);
		$this->assertEquals(array('foo'), $buf2);
	} // end testAddBefore();

	/**
	 * @covers Opt_Xml_Buffer::getBuffer
	 */
	public function testGetBufferNullBuffers()
	{
		$buf1 = $this->_obj->getBuffer(Opt_Xml_Buffer::TAG_BEFORE);
		$buf2 = $this->_obj->getBuffer(Opt_Xml_Buffer::TAG_AFTER);
		
		$this->assertEquals(array(), $buf1);
		$this->assertEquals(array(), $buf2);
	} // end testGetBufferNullBuffers();

	/**
	 * @covers Opt_Xml_Buffer::copyBuffer
	 */
	public function testCopyBufferNew()
	{
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'foo');

		$obj2 = new Opt_Xml_Element('opt:foo');
		$obj2->copyBuffer($this->_obj, Opt_Xml_Buffer::TAG_BEFORE, Opt_Xml_Buffer::TAG_AFTER);
		$buf1 = $obj2->getBuffer(Opt_Xml_Buffer::TAG_AFTER);

		$this->assertEquals(array('foo'), $buf1);
	} // end testCopyBufferNew();

	/**
	 * @covers Opt_Xml_Buffer::copyBuffer
	 */
	public function testCopyBufferExisting()
	{
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'foo');

		$obj2 = new Opt_Xml_Element('opt:foo');
		$obj2->addAfter(Opt_Xml_Buffer::TAG_AFTER, 'bar');
		$obj2->copyBuffer($this->_obj, Opt_Xml_Buffer::TAG_BEFORE, Opt_Xml_Buffer::TAG_AFTER);
		$buf1 = $obj2->getBuffer(Opt_Xml_Buffer::TAG_AFTER);

		$this->assertEquals(array('foo', 'bar'), $buf1);
	} // end testCopyBufferExisting();

	/**
	 * @covers Opt_Xml_Buffer::bufferSize
	 */
	public function testBufferSize()
	{
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'foo');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'bar');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'joe');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_AFTER, 'foo');

		$this->assertEquals(3, $this->_obj->bufferSize(Opt_Xml_Buffer::TAG_BEFORE));
		$this->assertEquals(1, $this->_obj->bufferSize(Opt_Xml_Buffer::TAG_AFTER));
		$this->assertEquals(0, $this->_obj->bufferSize(Opt_Xml_Buffer::TAG_OPENING_BEFORE));
	} // end testBufferSize();

	/**
	 * @covers Opt_Xml_Buffer::buildCode
	 */
	public function testBuildCodeSingleBufferConcatenation()
	{
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'foo');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'bar');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'joe');

		$this->assertEquals('<?php foo bar joe  ?>', $this->_obj->buildCode(Opt_Xml_Buffer::TAG_BEFORE));
	} // end testBuildCodeSingleBufferConcatenation();

	/**
	 * @covers Opt_Xml_Buffer::buildCode
	 */
	public function testBuildCodeDoubleBufferConcatenation()
	{
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'foo');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'bar');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'joe');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_AFTER, 'goo');

		$this->assertEquals('<?php foo bar joe goo  ?>', $this->_obj->buildCode(Opt_Xml_Buffer::TAG_BEFORE, Opt_Xml_Buffer::TAG_AFTER));
	} // end testBuildCodeDoubleBufferConcatenation();

	/**
	 * @covers Opt_Xml_Buffer::buildCode
	 */
	public function testBuildCodeUsesStrings()
	{
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'foo');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'bar');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'joe');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_AFTER, 'goo');

		$this->assertEquals('<?php foo bar joe  echo \' \'; goo  ?>', $this->_obj->buildCode(Opt_Xml_Buffer::TAG_BEFORE, ' ', Opt_Xml_Buffer::TAG_AFTER));
	} // end testBuildCodeUsesStrings();

	/**
	 * @covers Opt_Xml_Buffer::buildCode
	 */
	public function testBuildCodeRecognizesNophp()
	{
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'foo');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'bar');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_BEFORE, 'joe');
		$this->_obj->addAfter(Opt_Xml_Buffer::TAG_AFTER, 'goo');
		$this->_obj->set('nophp', true);

		$this->assertEquals('foo bar joe tgoo', $this->_obj->buildCode(Opt_Xml_Buffer::TAG_BEFORE, 't', Opt_Xml_Buffer::TAG_AFTER));
	} // end testBuildCodeRecognizesNophp();

	/**
	 * @covers Opt_Xml_Buffer::buildCode
	 */
	public function testBuildCodeReturnsEmptyStrings()
	{
		$this->assertEquals('', $this->_obj->buildCode(Opt_Xml_Buffer::TAG_BEFORE));
	} // end testBuildCodeReturnsEmptyStrings();
} // end Package_Inflector_StandardTest;