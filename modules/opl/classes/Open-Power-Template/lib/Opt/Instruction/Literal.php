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
 * The processor for opt:literal instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage XML
 */
class Opt_Instruction_Literal extends Opt_Instruction_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'literal';

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:literal'));
	} // end configure();

	/**
	 * Migrates the opt:literal node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end migrateNode();

	/**
	 * Processes the opt:literal node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		$params = array(
			'type' => array(0 => self::OPTIONAL, self::ID, 'cdata')
		);
		$this->_extractAttributes($node, $params);

		// First, disable displaying CDATA around all CDATA text parts found
		$this->disableCDATA($node, true);

		// Define, what to display...
		$node->clear();
		$node->set('nophp', true);
		switch($params['type'])
		{
			case 'transparent';
				break;
			case 'comment':
				$node->set('commented', true);
				break;
			case 'comment_cdata':
				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, '/* <![CDATA[ */');
				$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, '/* ]]> */');
				break;
			case 'cdata':
			default:
				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, '<![CDATA[');
				$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, ']]>');
		}
		$this->_process($node);
	} // end processNode();

	/**
	 * Used on a node, it looks for the CDATA elements and disables the
	 * CDATA flag on them. Moreover, it allows to disable the text entitizing.
	 *
	 * Note: this function is deprecated. Use Opt_Compiler_Utils::removeCdata()
	 * instead.
	 *
	 * @deprecated
	 * @param Opt_Xml_Node $node The scanned node
	 * @param Boolean $noEntitize optional The entitizing flag.
	 */
	public function disableCDATA(Opt_Xml_Node $node, $noEntitize = false)
	{
		Opt_Compiler_Utils::removeCdata($node, !$noEntitize);
	} // end disableCdata();
} // end Opt_Instruction_Literal;
