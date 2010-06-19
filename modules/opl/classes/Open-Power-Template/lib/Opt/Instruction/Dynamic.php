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
 * The processor for opt:dynamic instruction. It encapsulates
 * a compiler feature in a convenient form. Note that the particular
 * caching system must support the dynamic code snippet feature
 * in order to make this instruction actually useful.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Cache
 */
class Opt_Instruction_Dynamic extends Opt_Instruction_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'dynamic';

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:dynamic'));
		$this->_addAttributes(array('opt:dynamic'));
	} // end configure();

	/**
	 * Migrates the opt:dynamic node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end migrateNode();

	/**
	 * Processes the opt:dynamic node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' if($this->_tpl->getBufferState(\'cache\')) { $this->_outputBuffer[] = ob_get_flush(); }');
		$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' if($this->_tpl->getBufferState(\'cache\')) { ob_start(); } ');

		// We do not want to capture the output buffering in the dynamic block, so we
		// must copy the contents to a fake sub-node and set the "dynamic" flag to it.
		$turboNode = new Opt_Xml_Element('opt:_');
		$turboNode->moveChildren($node);
		$node->appendChild($turboNode);

		$turboNode->set('hidden', false);
		$turboNode->set('dynamic', true);
		$this->_process($turboNode);
	} // end processNode();

	/**
	 * Processes the instruction attribute.
	 * @param Opt_Xml_Node $node The node with the attribute.
	 * @param Opt_Xml_Attribute $attr The captured instruction attribute.
	 */
	public function processAttribute(Opt_Xml_Node $node, Opt_Xml_Attribute $attr)
	{
		// TODO: The expressions in the dynamic part do not work. Fix.
		if($attr == 'yes')
		{
			$turboNode = new Opt_Xml_Element('opt:_');
			$turboNode->set('hidden', false);
			$turboNode->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' if($this->_tpl->getBufferState(\'cache\')) { $this->_outputBuffer[] = ob_get_flush(); }');
			$turboNode->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' if($this->_tpl->getBufferState(\'cache\')) { ob_start(); } ');

			$node->getParent()->replaceChild($turboNode, $node);
			$node->set('dynamic', true);
			$turboNode->appendChild($node);
		}
	} // end processAttribute();
} // end Opt_Instruction_Dynamic;