<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 *
 */

/**
 * The abstract XML node class - the base of all the node types.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package XML
 * @abstract
 */
abstract class Opt_Xml_Node extends Opt_Xml_Buffer
{
	/**
	 * The parent of the current node.
	 * @var Opt_Xml_Node
	 */
	protected $_parent = null;

	/**
	 * The predecessor of the current node.
	 * @var Opt_Xml_Node
	 */
	protected $_previous = null;

	/**
	 * The successor of the current node.
	 * @var Opt_Xml_Node
	 */
	protected $_next = null;

	private $_cntx;

	/**
	 * OPT-XML object counter for debugging purposes.
	 * @var integer
	 */
	static private $_objCount = 0;

	/**
	 * OPT-XML object registry
	 * @var array
	 */
	static private $_registry = array();

	/**
	 * Constructs the new node object.
	 */
	public function __construct()
	{
		self::$_objCount++;
		$this->_cntx = self::$_objCount;
		self::$_registry[self::$_objCount] = $this;
	} // end __construct();

	/**
	 * Destroys the node object.
	 */
	public function __destruct()
	{
		self::$_objCount--;
	} // end __destruct();

	/**
	 * Returns the object counter for debugging purposes.
	 *
	 * @internal
	 * @static
	 * @return integer The number of currently existing OPT-XML objects.
	 */
	static public function getObjectCount()
	{
		return self::$_objCount;
	} // end getObjectCount();

	/**
	 * Returns the object registry.
	 *
	 * @internal
	 * @static
	 * @return array
	 */
	static public function getObjectRegistry()
	{
		 return self::$_registry;
	} // end getObjectRegistry();

	/**
	 * Sets the parent of the current node. USE WITH CAUTION!
	 *
	 * @param Opt_Xml_Node $parent The new parent or NULL.
	 */
	public function setParent($parent)
	{
		$this->_parent = $parent;
	} // end setParent();

	/**
	 * Returns the type of the current node (taken from the class name).
	 * @return String
	 */
	public function getType()
	{
		return get_class($this);
	} // end getType();

	/**
	 * Returns the node parent.
	 * @return Opt_Xml_Node
	 */
	public function getParent()
	{
		return $this->_parent;
	} // end getParent();

	/**
	 * Returns the predecessor of the current node. If the node does not
	 * have a predecessor, it returns null.
	 * @return Opt_Xml_Node
	 */
	public function getPrevious()
	{
		return $this->_previous;
	} // end getPrevious();

	/**
	 * Returns the successor of the current node. If the node does not
	 * have a successor, it returns null.
	 * @return Opt_Xml_Node
	 */
	public function getNext()
	{
		return $this->_next;
	} // end getNext();

	/**
	 * If the node is mounted into another location of an OPT tree, it
	 * umounts it from there.
	 */
	public function unmount()
	{
		if($this->_parent !== null)
		{
			$this->_parent->removeChild($this);
		}
	} // end _unmount();

	/**
	 * Prints the node type.
	 * @return String
	 */
	public function __toString()
	{
		return get_class($this);
	} // end __toString();

	/**
	 * The handler for the cloning procedure - it should be implemented, if the
	 * node type requires certain operations to be performed during a recursiveless
	 * cloning.
	 */
	protected function _cloneHandler()
	{
		$this->_parent = null;
		$this->_previous = null;
		$this->_next = null;
	} // end _cloneHandler();

	/**
	 * Prepares the node to be collected by the
	 * garbage collector. You must use this method before
	 * removing the last reference to the node to avoid the
	 * memory leak.
	 */
	public function dispose()
	{
		$this->_dispose();
	} // end dispose();

	/**
	 * This method allows to define some custom code that needs to
	 * be executed to dispose the node.
	 *
	 * @internal
	 */
	protected function _dispose()
	{
		unset(self::$_registry[$this->_cntx]);
		$this->unmount();
		$this->_parent = null;
		$this->_previous = null;
		$this->_next = null;
		$this->_buffers = null;
		$this->_args = null;
	} // end _dispose();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing.
	 */
	abstract public function preProcess(Opt_Compiler_Class $compiler);

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing, after processing the child nodes.
	 */
	abstract public function postProcess(Opt_Compiler_Class $compiler);

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking.
	 */
	abstract public function preLink(Opt_Compiler_Class $compiler);

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking, after linking the child nodes.
	 */
	abstract public function postLink(Opt_Compiler_Class $compiler);
} // end Opt_Xml_Node;