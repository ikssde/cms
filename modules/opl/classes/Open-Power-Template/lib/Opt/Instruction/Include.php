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
 * The processor for opt:include instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Modules
 */
class Opt_Instruction_Include extends Opt_Instruction_Abstract
{
	/**
	 * The instruction processor name - required by the instruction API.
	 * @internal
	 * @var string
	 */
	protected $_name = 'include';

	/**
	 * Configures the instruction processor, registering the tags and
	 * attributes.
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:include'));
	} // end configure();

	/**
	 * Migrates the opt:include node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function migrateNode(Opt_Xml_Node $node)
	{
		$this->_process($node);
	} // end migrateNode();

	/**
	 * Processes the opt:include node.
	 * @internal
	 * @param Opt_Xml_Node $node The recognized node.
	 */
	public function processNode(Opt_Xml_Node $node)
	{
		$params = array(
			'file' => array(0 => self::OPTIONAL, self::EXPRESSION, null, 'str'),
			'view' => array(0 => self::OPTIONAL, self::EXPRESSION, NULL, 'parse'),
			'from' => array(0 => self::OPTIONAL, self::ID, NULL),

			'default' => array(0 => self::OPTIONAL, self::EXPRESSION, NULL, 'str'),
			'import' => array(0 => self::OPTIONAL, self::BOOL, NULL),
			'branch' => array(0 => self::OPTIONAL, self::EXPRESSION, NULL, 'str'),
			'__UNKNOWN__' => array(0 => self::OPTIONAL, self::EXPRESSION, 'parse')
		);
		$vars = $this->_extractAttributes($node, $params);

		// Conditional attribute control.
		if(!isset($params['from']) && !isset($params['file']) && !isset($params['view']))
		{
			throw new Opt_IncludeNoAttributes($node->getXmlName());
		}
		// Possible section integration
		$codeBegin = '';
		$codeEnd = '';
		$viewExistenceCond = '';

		if(isset($params['from']))
		{
			$section = Opt_Instruction_Section_Abstract::getSection($params['from']);

			if(is_null($section))
			{
				throw new Opt_Instruction_Exception('opt:include cannot be integrated with section '.$params['from']);
			}
			$section['format']->assign('item', 'view');
			$view = $section['format']->get('section:variable');

			$viewExistenceCond = '!'.$view.' instanceof Opt_View ||';
		}

		if(isset($params['view']))
		{
			$view = $params['view'];
			$viewExistenceCond = '!'.$view.' instanceof Opt_View || ';
		}
		elseif(isset($params['file']))
		{
			$codeBegin = '$view = new Opt_View('.$params['file'].');';
			$view = '$view';
			$codeEnd = ' unset($view); ';
		}
		// Compile the import
		if($params['import'] == 'yes')
		{
			if(isset($params['file']))
			{
				$codeBegin .= $view.'->_data = $this->_data; ';
			}
			else
			{
				$codeBegin .= $view.'->_data = array_merge('.$view.'->_data, $this->_data); ';
			}
		}
		foreach($vars as $name => $value)
		{
			$codeBegin .= $view.'->'.$name.' = '.$value.'; ';
		}

		if(isset($params['branch']))
		{
			$codeBegin .= $view.'->setBranch('.$params['branch'].'); ';
		}
		if(!is_null($params['default']))
		{
			$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $codeBegin.' if('.$viewExistenceCond.'!'.$view.'->_parse($output, false)){ '.$view.'->_template = '.$params['default'].'; '.$view.'->_parse($output, true); } '.$codeEnd);
		}
		elseif($node->hasChildren())
		{
			$node->addBefore(Opt_Xml_Buffer::TAG_CONTENT_BEFORE, $codeBegin.' if('.$viewExistenceCond.'!'.$view.'->_parse($output, false)){ ');
			$node->addAfter(Opt_Xml_Buffer::TAG_CONTENT_AFTER, ' } '.$codeEnd);
			$this->_process($node);
		}
		else
		{
			$node->addAfter(Opt_Xml_Buffer::TAG_BEFORE, $codeBegin.'  '.$view.'->_parse($output, $exception); '.$codeEnd);
		}
	} // end processNode();
} // end Opt_Instruction_Include;