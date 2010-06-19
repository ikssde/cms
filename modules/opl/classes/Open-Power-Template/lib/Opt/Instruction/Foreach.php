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
 * The processor for opt:foreach instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Control
 */
class Opt_Instruction_Foreach extends Opt_Instruction_Loop_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'foreach';
	/**
	 * The opt:foreach nesting level used to generate unique variable names.
	 * @internal
	 * @var integer
	 */
	protected $_nesting = 0;

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions('opt:foreach');
		$this->_addAmbiguous(array(
			'opt:body' => 'opt:foreach',
			'opt:else' => 'opt:foreach'
		));
	} // end configure();

	/**
	 * Migrates the opt:foreach node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		// TODO: opt:foreachelse -> opt:else
		// TODO: opt:foreach attribute "array" => opt:foreach attribute "data".
		$this->_process($node);
	} // end migrateNode();

	/**
	 * Processes the opt:foreach node.
	 *
	 * @internal
	 * @throws Opt_Instruction_Exception
	 * @param Opt_Xml_Element $node The recognized node.
	 */
	protected function _processForeach(Opt_Xml_Element $node)
	{
		// First, we must check, what type of foreach we have to deal with...
		if($node->get('ambiguous:opt:body') !== null)
		{
			$cond = $node;
			$body = $node->get('ambiguous:opt:body');
		}
		else
		{
			$cond = $node;
			$body = $node;
		}

		$node->sort(array('*' => 0, 'opt:else' => 1));

		$params = array(
			'data' => array(0 => self::REQUIRED, self::EXPRESSION_EXT),
			'value' => array(0 => self::REQUIRED, self::ID),
			'index' => array(0 => self::OPTIONAL, self::ID, null),
			'separator' => array(0 => self::OPTIONAL, self::EXPRESSION, null)
		);

		$this->_extractAttributes($node, $params);
		$this->_nesting++;

		$list = $node->getElementsByTagNameNS('opt', 'else', false);


		switch(sizeof($list))
		{
			case 0:
				$body->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' foreach('.$params['data']['bare'].' as '.($params['index'] !== null ? '$__fe'.$this->_nesting.'_idx => ' : '').'$__fe'.$this->_nesting.'_val){ ');
				$body->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
				break;
			case 1:
				// A small optimization...
				if($params['data']['complexity'] > 6)
				{
					$body->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' foreach($__foreach_'.$this->_nesting.' as '.($params['index'] !== null ? '$__fe'.$this->_nesting.'_idx => ' : '').'$__fe'.$this->_nesting.'_val){ ');
					$cond->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' if(sizeof($__foreach_'.$this->_nesting.' = '.$params['data']['bare'].') > 0){ ');
					if($cond === $body)
					{
						$list[0]->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' } } else { ');
						$cond->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
					}
					else
					{
						$cond->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
						$body->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
						$list[0]->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' } else { ');
					}
				}
				else
				{
					
					$body->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' foreach('.$params['data']['bare'].' as '.($params['index'] !== null ? '$__fe'.$this->_nesting.'_idx => ' : '').'$__fe'.$this->_nesting.'_val){ ');
					$cond->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' if(sizeof('.$params['data']['bare'].') > 0){ ');
					if($cond === $body)
					{
						$list[0]->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' } } else { ');
						$cond->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
					}
					else
					{
						$cond->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
						$body->addAfter(Opt_Xml_Buffer::TAG_AFTER, ' } ');
						$list[0]->addBefore(Opt_Xml_Buffer::TAG_BEFORE, ' } else { ');
					}
				}				
				break;
			default:
				throw new Opt_Instruction_Exception('opt:foreach error: too many "opt:else" items.');
		}

		$this->processSeparator('$__foreach_'.$this->_nesting, $params['separator'], $node, $body);

		if($body !== $cond)
		{
			$body->set('priv:foreach-nesting', $this->_nesting);
			$body->set('priv:foreach-index', $params['index']);
			$body->set('priv:foreach-value', $params['value']);
		}
		else
		{
			$node->set('priv:foreach-index', $params['index']);
			$node->set('priv:foreach-value', $params['value']);
			$this->_compiler->setConversion('##var_'.$params['value'], '$__fe'.$this->_nesting.'_val');
			if($params['index'] !== null)
			{
				$this->_compiler->setConversion('##var_'.$params['index'], '$__fe'.$this->_nesting.'_idx');
			}			
		}
		$node->set('postprocess', true);
		$this->_process($node);
	} // end _processForeach();

	/**
	 * Postprocesses the opt:foreach node.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The recognized node.
	 */
	protected function _postprocessForeach(Opt_Xml_Element $node)
	{
		if($node->get('priv:foreach-value') !== null)
		{
			$this->_compiler->unsetConversion('##var_'.$node->get('priv:foreach-value'));
			$this->_compiler->unsetConversion('##var_'.$node->get('priv:foreach-index'));
		}
		$this->_nesting--;
	} // end _postprocessForeach();

	/**
	 * Processes the opt:body node.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The recognized node.
	 */
	protected function _processBody(Opt_Xml_Element $node)
	{
		$this->_compiler->setConversion('##var_'.$node->get('priv:foreach-value'), '$__fe'.$node->get('priv:foreach-nesting').'_val');
		if($node->get('priv:foreach-index') !== null)
		{
			$this->_compiler->setConversion('##var_'.$node->get('priv:foreach-index'), '$__fe'.$node->get('priv:foreach-nesting').'_idx');
		}
		$node->set('postprocess', true);
		$this->_process($node);
	} // end _processBody();

	/**
	 * Postprocesses the opt:body node.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The recognized node.
	 */
	protected function _postprocessBody(Opt_Xml_Element $node)
	{
		$this->_compiler->unsetConversion('##var_'.$node->get('priv:foreach-value'));
		$this->_compiler->unsetConversion('##var_'.$node->get('priv:foreach-index'));
	} // end _postprocessBody();

	/**
	 * Processes the opt:else node.
	 *
	 * @internal
	 * @throws Opt_Instruction_Exception
	 * @param Opt_Xml_Element $node The recognized node.
	 */
	protected function _processElse(Opt_Xml_Element $node)
	{
		if($node->getParent()->getName() != 'foreach')
		{
			throw new Opt_Instruction_Exception('Invalid parent of "opt:else": "opt:foreach" expected.');
		}
		$this->_process($node);
	} // end _processElse();
} // end Opt_Instruction_Foreach;