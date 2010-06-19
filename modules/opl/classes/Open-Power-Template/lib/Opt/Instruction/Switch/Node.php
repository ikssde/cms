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
 * The compilation of opt:switch is quite difficult due to the features
 * of the instruction. We have to build an abstract tree that represents
 * the distribution of different top-level and nested cases. This class
 * represents the nodes of this tree.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 */
class Opt_Instruction_Switch_Node
{
	/**
	 * The OPT-XML tree element.
	 * @var Opt_Xml_Element
	 */
	private $_element;

	/**
	 * The parent node.
	 * @var Opt_Instruction_Switch_Node
	 */
	private $_parent;

	/**
	 * The next case node.
	 * @var Opt_Instruction_Switch_Node
	 */
	private $_next = null;

	/**
	 * Is some static content present after the node?
	 * @var boolean
	 */
	private $_tail = false;

	/**
	 * Creates the node.
	 *
	 * @param Opt_Xml_Element $element The OPT-XML element represented by this node.
	 * @param Opt_Instruction_Switch_Node $parent The parent of this node.
	 */
	public function __construct(Opt_Xml_Element $element, Opt_Instruction_Switch_Node $parent = null)
	{
		$this->_element = $element;
		$this->_parent = $parent;
	} // end __construct();

	/**
	 * Sets the next element.
	 *
	 * @param Opt_Instruction_Switch_Node $next The next element reference
	 */
	public function setNext(Opt_Instruction_Switch_Node $next)
	{
		$this->_next = $next;
	} // end setNext();

	/**
	 * The nodes that do not have any extra non-whitespace content right after them,
	 * are called tail nodes. This method allows to control the tail status of this
	 * node.
	 *
	 * @param boolean $status The new tail status.
	 */
	public function setTail($status)
	{
		$this->_tail = (bool)$status;
	} // end setTail();

	/**
	 * Returns the next node.
	 *
	 * @return Opt_Instruction_Switch_Node
	 */
	public function getNext()
	{
		return $this->_next;
	} // end getNext();

	/**
	 * Returns the parent node.
	 *
	 * @return Opt_Instruction_Switch_Node
	 */
	public function getParent()
	{
		return $this->_parent;
	} // end getParent();

	/**
	 * Returns the current tail status.
	 *
	 * @return boolean
	 */
	public function getTail()
	{
		return $this->_tail;
	} // end getTail();

	/**
	 * Returns the element represented by this node.
	 *
	 * @return Opt_Xml_Element
	 */
	public function getElement()
	{
		return $this->_element;
	} // end getElement();
} // end Opt_Instruction_Switch_Node;