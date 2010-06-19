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
 * This abstract class contains various tools useful for loop instructions.
 * Currently it supports separators.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage API
 * @abstract
 */
abstract class Opt_Instruction_Loop_Abstract extends Opt_Instruction_Abstract
{
	/**
	 * Processes the loop separator. The programmer must provide the
	 * variable name that will be used to check if we need to apply
	 * the separator, and the optional value of "separator" attribute
	 * in the node attributes. The separator is added to the specified
	 * XML node.
	 *
	 * If the node contains too many opt:separator tags, an exception
	 * is thrown.
	 *
	 * @throws Opt_Instruction_Exception
	 * @param string $varname The internal variable name
	 * @param string $arg The value of "separator" attribute
	 * @param Opt_Xml_Scannable $node The node the separator will be added to.
	 */
	public function processSeparator($varname, $arg, Opt_Xml_Scannable $node)
	{
		$items = $node->getElementsByTagNameNS('opt', 'separator', false);

		switch(sizeof($items))
		{
			case 1:
				// Move this node to the beginning
				$node->removeChild($items[0]);
				$node->insertBefore($items[0], 0);
				$this->_process($items[0]);
				$items[0]->set('hidden', false);

				// Add PHP code
				$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' '.$varname.' = 0;');
				$items[0]->addBefore(Opt_Xml_Buffer::TAG_BEFORE, 'if('.$varname.' == 1){');
				$items[0]->addAfter(Opt_Xml_Buffer::TAG_AFTER, '}else{ '.$varname.' = 1; }');
				break;
			case 0:
				if(!is_null($arg))
				{
					$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $varname.' = 0;');
					$node->addBefore(Opt_Xml_Buffer::TAG_CONTENT_BEFORE, 'if('.$varname.' == 1){ echo '.$arg.'; }else{ '.$varname.' = 1; }');
				}
				break;
			default:
				throw new Opt_Instruction_Exception('Too many "opt:separator" elements: zero or one expected.');
		}
	} // end processSeparator();

	/**
	 * Returns the configuration of the "separator" attribute
	 * for Opt_Compiler_Instruction::_extractAttributes()
	 * @return array
	 */
	public function getSeparatorConfig()
	{
		return array(self::OPTIONAL, self::EXPRESSION, NULL);
	} // end getSeparatorConfig();
} // end Opt_Instruction_Loop_Abstract;