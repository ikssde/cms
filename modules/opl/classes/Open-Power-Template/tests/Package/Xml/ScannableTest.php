<?php
/**
 * The tests for Opt_Xml_Scannable
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * @covers Opt_Xml_Scannable
 */
class Package_Xml_ScannableTest extends Extra_Testcase
{
	/**
	 * The tested object.
	 * @var Opt_Xml_Scannable
	 */
	private $_obj;

	/**
	 * The standard mock method list
	 * @var array
	 */
	private $_mocked = array('preProcess', 'postProcess', 'preLink', 'postLink');

	/**
	 * Sets up the object to test. We use Extra_Wrapper_ScannableTester to test
	 * the basic interface.
	 */
	public function setUp()
	{
		// @codeCoverageIgnoreStart
		$this->_obj = new Extra_Wrapper_ScannableTester('opt:foo');
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
	 * @covers Opt_Xml_Scannable::appendChild
	 * @covers Opt_Xml_Scannable::getLastChild
	 * @covers Opt_Xml_Scannable::getFirstChild
	 */
	public function testAppendChild()
	{
		$mock = $this->getMock('Opt_Xml_Node', $this->_mocked);
		$this->_obj->appendChild($mock);
		$this->assertSame($mock, $this->_obj->getLastChild());
		$this->assertSame($mock, $this->_obj->getFirstChild());
		$this->assertSame($this->_obj, $mock->getParent());
		$this->assertEquals(null, $mock->getPrevious());
		$this->assertEquals(null, $mock->getNext());
		$this->assertEquals(1, $this->_obj->countChildren());
	} // end testAppendChild();

	/**
	 * @covers Opt_Xml_Scannable::appendChild
	 * @covers Opt_Xml_Scannable::getLastChild
	 * @covers Opt_Xml_Scannable::getFirstChild
	 */
	public function testAppendChildren()
	{
		$mock1 = $this->getMock('Opt_Xml_Node', $this->_mocked);
		$mock2 = $this->getMock('Opt_Xml_Node', $this->_mocked);
		$mock3 = $this->getMock('Opt_Xml_Node', $this->_mocked);
		$this->_obj->appendChild($mock1);
		$this->_obj->appendChild($mock2);

		$this->assertSame($mock2, $this->_obj->getLastChild());
		$this->assertSame($mock1, $this->_obj->getFirstChild());

		$this->assertSame($mock1, $mock2->getPrevious());
		$this->assertSame($mock2, $mock1->getNext());

		$this->_obj->appendChild($mock3);
		$this->assertSame($mock3, $this->_obj->getLastChild());

		$this->assertSame($mock2, $mock3->getPrevious());
		$this->assertSame($mock3, $mock2->getNext());
		$this->assertEquals(null, $mock1->getPrevious());
		$this->assertEquals(null, $mock3->getNext());
	} // end testAppendChildren();

	/**
	 * @covers Opt_Xml_Scannable::insertBefore
	 */
	public function testInsertBeforeNumerical()
	{
		$mock = array(0 => 
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);


		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->insertBefore($mock[2], 1);

		$this->assertSame($mock[0], $mock[2]->getPrevious());
		$this->assertSame($mock[2], $mock[0]->getNext());
		$this->assertSame($mock[2], $mock[1]->getPrevious());
		$this->assertSame($mock[1], $mock[2]->getNext());
		$this->assertEquals(null, $mock[0]->getPrevious());
		$this->assertEquals(null, $mock[1]->getNext());
	} // end testInsertBefore();

	/**
	 * @covers Opt_Xml_Scannable::insertBefore
	 */
	public function testInsertBeforeOrdinary()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->insertBefore($mock[2], $mock[1]);

		$this->assertSame($mock[0], $mock[2]->getPrevious());
		$this->assertSame($mock[2], $mock[0]->getNext());
		$this->assertSame($mock[2], $mock[1]->getPrevious());
		$this->assertSame($mock[1], $mock[2]->getNext());
		$this->assertEquals(null, $mock[0]->getPrevious());
		$this->assertEquals(null, $mock[1]->getNext());
	} // end testInsertBeforeOrdinary();

	/**
	 * @covers Opt_Xml_Scannable::insertBefore
	 */
	public function testInsertBeforeAtFirstPlace()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->insertBefore($mock[2], $mock[0]);

		$this->assertSame($mock[2], $this->_obj->getFirstChild());
		$this->assertSame($mock[0], $mock[2]->getNext());
		$this->assertSame($mock[2], $mock[0]->getPrevious());
		$this->assertEquals(null, $mock[2]->getPrevious());
	} // end testInsertBeforeAtFirstPlace();

	/**
	 * @covers Opt_Xml_Scannable::insertBefore
	 */
	public function testInsertBeforeAtLastPlace()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->insertBefore($mock[2], null);

		$this->assertSame($mock[2], $this->_obj->getLastChild());

		$this->assertSame($mock[1], $mock[2]->getPrevious());
		$this->assertSame($mock[2], $mock[1]->getNext());
		$this->assertEquals(null, $mock[2]->getNext());
	} // end testInsertBeforeAtLastPlace();

	/**
	 * @expectedException Opt_APIInvalidBorders_Exception
	 * @covers Opt_Xml_Scannable::insertBefore
	 */
	public function testInsertBeforeThrowsExceptionsOnINvalidRefNodesNumeric()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->insertBefore($mock[2], 2);
	} // end testInsertBeforeThrowsExceptionsOnINvalidRefNodesNumeric();

	/**
	 * @expectedException Opt_APIInvalidBorders_Exception
	 * @covers Opt_Xml_Scannable::insertBefore
	 */
	public function testInsertBeforeThrowsExceptionsOnINvalidRefNodesParent()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->insertBefore($mock[2], $mock[1]);
	} // end testInsertBeforeThrowsExceptionsOnINvalidRefNodesParent();

	/**
	 * @covers Opt_Xml_Scannable::removeChild
	 */
	public function testRemoveChildMiddle()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);
		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->_obj->removeChild($mock[1]);

		$this->assertSame($mock[2], $this->_obj->getLastChild());
		$this->assertSame($mock[0], $this->_obj->getFirstChild());

		$this->assertSame($mock[0], $mock[2]->getPrevious());
		$this->assertSame($mock[2], $mock[0]->getNext());

		$this->assertEquals(null, $mock[1]->getPrevious());
		$this->assertEquals(null, $mock[1]->getNext());
		$this->assertEquals(null, $mock[1]->getParent());
	} // end testRemoveChildMiddle();

	/**
	 * @covers Opt_Xml_Scannable::removeChild
	 */
	public function testRemoveChildFirst()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);
		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->_obj->removeChild($mock[0]);

		$this->assertSame($mock[2], $this->_obj->getLastChild());
		$this->assertSame($mock[1], $this->_obj->getFirstChild());

		$this->assertSame($mock[1], $mock[2]->getPrevious());
		$this->assertSame($mock[2], $mock[1]->getNext());
		$this->assertEquals(null, $mock[1]->getPrevious());

		$this->assertEquals(null, $mock[0]->getPrevious());
		$this->assertEquals(null, $mock[0]->getNext());
		$this->assertEquals(null, $mock[0]->getParent());
	} // end testRemoveChildFirst();

	/**
	 * @covers Opt_Xml_Scannable::removeChild
	 */
	public function testRemoveChildLast()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);
		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->_obj->removeChild($mock[2]);

		$this->assertSame($mock[1], $this->_obj->getLastChild());
		$this->assertSame($mock[0], $this->_obj->getFirstChild());

		$this->assertSame($mock[0], $mock[1]->getPrevious());
		$this->assertSame($mock[1], $mock[0]->getNext());
		$this->assertEquals(null, $mock[1]->getNext());

		$this->assertEquals(null, $mock[2]->getPrevious());
		$this->assertEquals(null, $mock[2]->getNext());
		$this->assertEquals(null, $mock[2]->getParent());
	} // end testRemoveChildLast();

	/**
	 * @covers Opt_Xml_Scannable::removeChild
	 */
	public function testRemoveChildNumeric()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);
		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->_obj->removeChild(1);

		$this->assertSame($mock[2], $this->_obj->getLastChild());
		$this->assertSame($mock[0], $this->_obj->getFirstChild());

		$this->assertSame($mock[0], $mock[2]->getPrevious());
		$this->assertSame($mock[2], $mock[0]->getNext());

		$this->assertEquals(null, $mock[1]->getPrevious());
		$this->assertEquals(null, $mock[1]->getNext());
		$this->assertEquals(null, $mock[1]->getParent());
	} // end testRemoveChildNumeric();

	/**
	 * @covers Opt_Xml_Scannable::removeChildren
	 */
	public function testRemoveChildren()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);
		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->assertEquals(3, $this->_obj->countChildren());

		$this->_obj->removeChildren();

		$this->assertEquals(0, $this->_obj->countChildren());
		$this->assertEquals(null, $this->_obj->getLastChild());
		$this->assertEquals(null, $this->_obj->getFirstChild());

		foreach($mock as $m)
		{
			$this->assertEquals(null, $m->getPrevious());
			$this->assertEquals(null, $m->getNext());
			$this->assertEquals(null, $m->getParent());
		}
	} // end testRemoveChildren();


	/**
	 * @covers Opt_Xml_Scannable::moveChildren
	 */
	public function testMoveChildrenToEmptyNode()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);
		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$dest = new Extra_Wrapper_ScannableTester('opt:foo');
		$dest->moveChildren($this->_obj);

		$this->assertEquals(3, $dest->countChildren());
		$this->assertEquals(0, $this->_obj->countChildren());

		$this->assertSame($mock[0], $dest->getFirstChild());
		$this->assertSame($mock[2], $dest->getLastChild());
		$this->assertEquals(null, $this->_obj->getLastChild());
		$this->assertEquals(null, $this->_obj->getFirstChild());

		foreach($mock as $m)
		{
			$this->assertSame($dest, $m->getParent());
		}
	} // end testMoveChildrenToEmptyNode();

	/**
	 * @covers Opt_Xml_Scannable::moveChildren
	 */
	public function testMoveChildrenToNonEmptyNode()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$nonempty = $this->getMock('Opt_Xml_Node', $this->_mocked);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$dest = new Extra_Wrapper_ScannableTester('opt:foo');
		$dest->appendChild($nonempty);
		$dest->moveChildren($this->_obj);

		$this->assertEquals(3, $dest->countChildren());
		$this->assertEquals(0, $this->_obj->countChildren());

		$this->assertSame($mock[0], $dest->getFirstChild());
		$this->assertSame($mock[2], $dest->getLastChild());
		$this->assertSame(null, $nonempty->getParent());
	} // end testMoveChildrenToNonEmptyNode();

	/**
	 * @covers Opt_Xml_Scannable::replaceChild
	 */
	public function testReplaceChildMiddle()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$replacing = $this->getMock('Opt_Xml_Node', $this->_mocked);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->_obj->replaceChild($replacing, $mock[1]);

		$this->assertSame($mock[2], $this->_obj->getLastChild());
		$this->assertSame($mock[0], $this->_obj->getFirstChild());

		$this->assertSame($replacing, $mock[2]->getPrevious());
		$this->assertSame($mock[2], $replacing->getNext());
		$this->assertSame($this->_obj, $replacing->getParent());

		$this->assertSame(null, $mock[1]->getPrevious());
		$this->assertSame(null, $mock[1]->getNext());
		$this->assertSame(null, $mock[1]->getParent());
	} // end testRemoveChildMiddle();

	/**
	 * @covers Opt_Xml_Scannable::replaceChild
	 */
	public function testReplaceChildFirst()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$replacing = $this->getMock('Opt_Xml_Node', $this->_mocked);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->_obj->replaceChild($replacing, $mock[0]);

		$this->assertSame($mock[2], $this->_obj->getLastChild());
		$this->assertSame($replacing, $this->_obj->getFirstChild());

		$this->assertSame($replacing, $mock[1]->getPrevious());
		$this->assertSame($mock[1], $replacing->getNext());
		$this->assertSame($this->_obj, $replacing->getParent());

		$this->assertSame(null, $mock[0]->getPrevious());
		$this->assertSame(null, $mock[0]->getNext());
		$this->assertSame(null, $mock[0]->getParent());
	} // end testReplaceChildFirst();

	/**
	 * @covers Opt_Xml_Scannable::replaceChild
	 */
	public function testReplaceChildLast()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$replacing = $this->getMock('Opt_Xml_Node', $this->_mocked);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->_obj->replaceChild($replacing, $mock[2]);

		$this->assertSame($replacing, $this->_obj->getLastChild());
		$this->assertSame($mock[0], $this->_obj->getFirstChild());

		$this->assertSame($mock[1], $replacing->getPrevious());
		$this->assertSame($replacing, $mock[1]->getNext());
		$this->assertSame($this->_obj, $replacing->getParent());

		$this->assertSame(null, $mock[2]->getPrevious());
		$this->assertSame(null, $mock[2]->getNext());
		$this->assertSame(null, $mock[2]->getParent());
	} // end testReplaceChildLast();

	/**
	 * @covers Opt_Xml_Scannable::replaceChild
	 */
	public function testReplaceChildNumeric()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$replacing = $this->getMock('Opt_Xml_Node', $this->_mocked);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$this->_obj->replaceChild($replacing, 1);

		$this->assertSame($mock[2], $this->_obj->getLastChild());
		$this->assertSame($mock[0], $this->_obj->getFirstChild());

		$this->assertSame($replacing, $mock[2]->getPrevious());
		$this->assertSame($mock[2], $replacing->getNext());
		$this->assertSame($this->_obj, $replacing->getParent());

		$this->assertSame(null, $mock[1]->getPrevious());
		$this->assertSame(null, $mock[1]->getNext());
		$this->assertSame(null, $mock[1]->getParent());
	} // end testReplaceChildNumeric();

	/**
	 * @covers Opt_Xml_Scannable::getChildren
	 */
	public function testGetChildren()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		$list = $this->_obj->getChildren();

		$this->assertEquals(3, sizeof($list));

		$this->assertSame($mock[0], $list[0]);
		$this->assertSame($mock[1], $list[1]);
		$this->assertSame($mock[2], $list[2]);
	} // end testGetChildren();

	/**
	 * @covers Opt_Xml_Scannable::getElements
	 */
	public function testGetElements()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$subnode = new Extra_Wrapper_ScannableTester('opt:foo');
		$subnode->appendChild($mock[1]);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($subnode);
		$this->_obj->appendChild($mock[2]);

		$list = $this->_obj->getElements();

		$this->assertEquals(4, sizeof($list));

		$this->assertSame($mock[0], $list[0]);
		$this->assertSame($subnode, $list[1]);
		$this->assertSame($mock[2], $list[2]);
		$this->assertSame($mock[1], $list[3]);
	} // end testGetElements();

	/**
	 * @covers Opt_Xml_Scannable::rewind
	 * @covers Opt_Xml_Scannable::next
	 * @covers Opt_Xml_Scannable::valid
	 * @covers Opt_Xml_Scannable::current
	 * @covers Opt_Xml_Scannable::key
	 */
	public function testIteration()
	{
		$mock = array(0 =>
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked),
			$this->getMock('Opt_Xml_Node', $this->_mocked)
		);

		$this->_obj->appendChild($mock[0]);
		$this->_obj->appendChild($mock[1]);
		$this->_obj->appendChild($mock[2]);

		foreach($this->_obj as $key => $element)
		{
			$this->assertSame($mock[$key], $element);
		}
	} // end testIteration();

	/**
	 * @covers Opt_Xml_Scannable::getElementsByTagName
	 */
	public function testNonrecursiveNameBasedSearch()
	{
		$structure = $this->_buildXmlTree();

		$list = $this->_obj->getElementsByTagName('bar', false);

		$this->assertEquals(2, sizeof($list));

		$this->assertContains($structure[0][0], $list);
		$this->assertContains($structure[0][1], $list);
	} // end testNonrecursiveNameBasedSearch();

	/**
	 * @covers Opt_Xml_Scannable::getElementsByTagName
	 * @covers Opt_Xml_Scannable::_getElementsByTagName
	 */
	public function testRecursiveNameBasedSearch()
	{
		$structure = $this->_buildXmlTree();

		$list = $this->_obj->getElementsByTagName('bar');

		$this->assertEquals(5, sizeof($list));

		$this->assertContains($structure[0][0], $list);
		$this->assertContains($structure[0][1], $list);
		$this->assertContains($structure[1][0], $list);
		$this->assertContains($structure[1][1], $list);
		$this->assertContains($structure[2][1], $list);
	} // end testRecursiveNameBasedSearch();

	/**
	 * @covers Opt_Xml_Scannable::getElementsByTagNameNS
	 */
	public function testNonrecursiveNamespaceBasedSearch()
	{
		$structure = $this->_buildXmlTree();

		$list = $this->_obj->getElementsByTagNameNS('foo', 'bar', false);

		$this->assertEquals(1, sizeof($list));

		$this->assertContains($structure[0][0], $list);
	} // end testNonrecursiveNamespaceBasedSearch();

	/**
	 * @covers Opt_Xml_Scannable::getElementsByTagNameNS
	 * @covers Opt_Xml_Scannable::_getElementsByTagName
	 */
	public function testRecursiveNamespaceBasedSearch()
	{
		$structure = $this->_buildXmlTree();

		$list = $this->_obj->getElementsByTagNameNS('foo', 'bar');

		$this->assertEquals(2, sizeof($list));

		$this->assertContains($structure[0][0], $list);
		$this->assertContains($structure[1][0], $list);
	} // end testRecursiveNamespaceBasedSearch();

	/**
	 * @covers Opt_Xml_Scannable::getElementsByTagNameNS
	 * @covers Opt_Xml_Scannable::_getElementsByTagName
	 */
	public function testRecursiveNamespaceOnly()
	{
		$structure = $this->_buildXmlTree();

		$list = $this->_obj->getElementsByTagNameNS('foo', '*');

		$this->assertEquals(3, sizeof($list));

		$this->assertContains($structure[0][0], $list);
		$this->assertContains($structure[1][0], $list);
		$this->assertContains($structure[2][4], $list);
	} // end testRecursiveNamespaceOnly();

	/**
	 * An XML tree builder for the element search tests.
	 * @return array
	 */
	private function _buildXmlTree()
	{
		// @codeCoverageIgnoreStart
		$level0 = array(
			new Opt_Xml_Element('foo:bar'),
			new Opt_Xml_Element('joe:bar'),
			new Opt_Xml_Element('lol'),
			new Opt_Xml_Element('rotfl'),
		);

		$level1a = array(
			new Opt_Xml_Element('foo:bar'),
			new Opt_Xml_Element('joe:bar'),
			new Opt_Xml_Element('lol'),
			new Opt_Xml_Element('rotfl'),
		);

		$level1b = array(
			new Opt_Xml_Element('joe:foo'),
			new Opt_Xml_Element('joe:bar'),
			new Opt_Xml_Element('lol'),
			new Opt_Xml_Element('lmao'),
			new Opt_Xml_Element('foo:lmao'),
		);

		foreach($level0 as $item)
		{
			$this->_obj->appendChild($item);
		}

		foreach($level1a as $item)
		{
			$level0[1]->appendChild($item);
		}

		foreach($level1b as $item)
		{
			$level0[2]->appendChild($item);
		}
		// @codeCoverageIgnoreStop
		return array($level0, $level1a, $level1b);
	} // end buildXmlTree();
} // end Package_Xml_ScannableTest;