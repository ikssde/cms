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
 * Processes the procedure instruction.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Instructions
 * @subpackage Modules
 */
class Opt_Instruction_Procedure extends Opt_Instruction_Abstract
{
	/**
	 * The processor name required by the parent.
	 * @internal
	 * @var string
	 */
	protected $_name = 'procedure';

	/**
	 * The procedure generation stack
	 * @var SplStack
	 */
	protected $_stack;

	/**
	 * Configures the instruction processor.
	 *
	 * @internal
	 */
	public function configure()
	{
		$this->_addInstructions(array('opt:procedure'));
	} // end configure();

	/**
	 * Resets the processor after the finished compilation and frees the
	 * memory taken by the snippets.
	 *
	 * @internal
	 */
	public function reset()
	{
		$this->_stack = new SplStack;
	} // end reset();

	/**
	 * Processes the opt:procedure element.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The found element
	 */
	public function _processProcedure(Opt_Xml_Element $node)
	{
		// Snippet insertion
		$params = array(
			'name' => array(0 => self::REQUIRED, self::ID),
			'__UNKNOWN__' => array(0 => self::OPTIONAL, self::STRING)
		);
		$arguments = $this->_extractAttributes($node, $params);

		$code = '$ctx->_procs[\''.$params['name'].'\'] = function(Opt_InternalContext $ctx';

		$argCounter = 0;
		$args = array();
		foreach($arguments as $name => $argument)
		{
			if($argument == 'required')
			{
				$code .= ', $__arg_'.$argCounter;
			}
			else
			{
				$code .= ', $__arg_'.$argCounter.' = ';
				switch($argument)
				{
					case 'null':
						$code .= 'null';
						break;
					case 'false':
						$code .= 'false';
						break;
					case 'true':
						$code .= 'true';
						break;
					default:
						if(ctype_digit($argument))
						{
							$code .= $argument;
						}
						else
						{
							// TODO: Add slash detection.
							$code .= '\''.addslashes($argument).'\'';
						}
				}
			}
			$this->_compiler->setConversion('##var_'.$name, '$__arg_'.$argCounter);
			$args[] = $name;
		}
		$code .= '){ '.PHP_EOL;
		$node->addBefore(Opt_Xml_Buffer::TAG_BEFORE, $code);
		$node->addAfter(Opt_Xml_Buffer::TAG_AFTER, PHP_EOL.' }; ');
		$node->set('postprocess', true);
		$this->_stack->push($args);
		$this->_process($node);
	} // end _processProcedure();

	/**
	 * Post-processes the opt:procedure element.
	 *
	 * @internal
	 * @param Opt_Xml_Element $node The found element
	 */
	public function _postprocessProcedure(Opt_Xml_Element $node)
	{
		$args = $this->_stack->pop();
		foreach($args as $name)
		{
			$this->_compiler->unsetConversion('##var_'.$name);
		}
	} // end _postprocessProcedure();

	/**
	 * Generates a piece of PHP code that calls the specified
	 * procedure.
	 *
	 * @param array $procedureExpression The procedure name expression
	 * @param array|string $arguments The procedure arguments
	 * @param bool $callUserFunc Compile as a call_user_func_array() call.
	 * @return string
	 */
	public function callProcedure(array $procedureExpression, $arguments, $callUserFunc = false)
	{
		$code = '';
		if($procedureExpression['complexity'] > 10)
		{
			$procedureName = '$__proc';
			$code = '$__proc = '.$procedureExpression['bare'].'; '.PHP_EOL;
		}
		else
		{
			$procedureName = $procedureExpression['bare'];
		}

		$code .= ' if(!isset($ctx->_procs['.$procedureName.'])){ throw new Opt_ObjectNotExists_Exception(\'template procedure\', '.$procedureName.'); } ';

		if(!$callUserFunc)
		{
			$code .= ' $__call = $ctx->_procs['.$procedureName.']; ';
			if(sizeof($arguments) == 0)
			{
				$code .= ' $__call($ctx); ';
			}
			else
			{
				$code .= ' $__call($ctx, '.implode(',',$arguments).'); ';
			}
		}
		else
		{
			$code .= ' call_user_func_array($ctx->_procs['.$procedureName.'], '.$arguments.'); ';
		}
		return $code;
	} // end callProcedure();
} // end Opt_Instruction_Procedure;