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
 * The standard OPT expression engine that implements the official
 * expression syntax.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Expressions
 */
class Opt_Expression_Standard implements Opt_Expression_Interface
{
	const SCALAR_WEIGHT = 1;
	const PARENTHESES_WEIGHT = 1;
	const ARRAY_CALL_WEIGHT = 1;
	const CONTAINER_ITEM_WEIGHT = 2;
	const VARIABLE_WEIGHT = 2;
	const OBJECT_FIELD_CALL_WEIGHT = 2;
	const STATIC_FIELD_CALL_WEIGHT = 2;
	const SECTION_ITEM_WEIGHT = 2;
	const SECTION_VARIABLE_WEIGHT = 2;
	const LANGUAGE_VARIABLE_WEIGHT = 3;
	const MATH_OP_WEIGHT = 5;
	const LOGICAL_OP_WEIGHT = 5;
	const COMPARE_OP_WEIGHT = 5;
	const CONCAT_OP_WEIGHT = 6;
	const ASSIGN_OP_WEIGHT = 15;
	const EXISTS_OP_WEIGHT = 15;
	const DF_OP_WEIGHT = 20;
	const INCDEC_OP_WEIGHT = 30;
	const FUNCTIONAL_OP_WEIGHT = 30;
	const CONTAINER_WEIGHT = 30;
	const CLONE_WEIGHT = 50;
	const NEW_WEIGHT = 50;
	const FUNCTION_WEIGHT = 100;
	const METHOD_CALL_WEIGHT = 100;

	const CONTEXT_READ = 0;
	const CONTEXT_ASSIGN = 1;
	const CONTEXT_POSTINCREMENT = 2;
	const CONTEXT_POSTDECREMENT = 3;
	const CONTEXT_PREINCREMENT = 4;
	const CONTEXT_PREDECREMENT = 5;
	const CONTEXT_EXISTS = 6;

	/**
	 * A translation of the context numbers to the
	 * data format calls.
	 *
	 * @var array
	 */
	private $_dfCalls = array(0 =>
		'',
		'.assign',
		'.postincrement',
		'.postdecrement',
		'.preincrement',
		'.predecrement',
		'.exists'
	);


	/**
	 * The compiler instance.
	 *
	 * @var Opt_Compiler_Class
	 */
	protected $_compiler;

	/**
	 * The main class instance.
	 *
	 * @var Opt_Class
	 */
	protected $_tpl;

	/**
	 * The compiled expression.
	 * @var string
	 */
	protected $_compiled;
	/**
	 * Is the assignment operator used at the lowest level?
	 * @var boolean
	 */
	protected $_assign;
	/**
	 * The calculated expression complexity for optimization purposes.
	 * @var integer
	 */
	protected $_complexity;

	/**
	 * The calculated data format.
	 * @var string
	 */
	protected $_format;

	/**
	 * The unique identifier generator
	 * @var integer
	 */
	protected $_unique = 0;

	/**
	 * The expression body.
	 * @var string
	 */
	protected $_expression;

	/**
	 * Sets the compiler instance in the expression parser.
	 *
	 * @param Opt_Compiler_Class $compiler The compiler object
	 */
	public function setCompiler(Opt_Compiler_Class $compiler)
	{
		$this->_compiler = $compiler;
		$this->_tpl = Opl_Registry::get('opt');
		$this->_tf = $this->_tpl->getTranslationInterface();
	} // end setCompiler();

	/**
	 * The method should reset all the object references it possesses.
	 */
	public function dispose()
	{
		$this->_format = null;
		$this->_tpl = null;
		$this->_compiler = null;
	} // end dispose();

	/**
	 * Parses the source expressions to the PHP code.
	 *
	 * @throws Opt_Expression_Exception
	 * @param string $expression The expression source
	 * @return array
	 */
	public function parse($expr)
	{
		try
		{
			$this->_unique = 0;
			$this->_expression = $expr;

			$lexer = new Opt_Expression_Standard_Lexer($expr);
			$parser = new Opt_Expression_Standard_Parser($this);
			while ($lexer->yylex())
			{
				if($lexer->token != 'w')
				{
					$parser->doParse($lexer->token, $lexer->value);
				}
			}
			$parser->doParse(0, 0);

			$exprType = Opt_Expression_Interface::COMPOUND;
			if($this->_assign == true)
			{
				$exprType = Opt_Expression_Interface::ASSIGNMENT;
			}
			$this->_assign = false;
			return array(
				'bare'			=> $this->_compiled,
				'expression'	=> $this->_compiled,
				'complexity'	=> $this->_complexity,
				'type'			=> $exprType
			);
		}
		catch(Opt_Expression_Exception $exception)
		{
			$exception->setExpression($expr);
			throw $exception;
		}
	} // end parse();

	/**
	 * Returns the expression body for debugging purposes.
	 *
	 * @return string
	 */
	public function getExpression()
	{
		return $this->_expression;
	} // end getExpression();

	/**
	 * Finalizes the expression parsing. Side effects: the compilation
	 * results are saved into the $_compiled and $_complexity object
	 * fields.
	 * 
	 * @param SplFixedArray $expression The expression array.
	 */
	public function _finalize(SplFixedArray $expression)
	{
		$this->_compiled = $expression[0];
		$this->_complexity = $expression[1];

		// The last expression field is used to mark some special things,
		// such as that we have an assignment.
		if($expression[3] == 1)
		{
			$this->_assign = true;
		}
	} // end _finalize();

	/**
	 * Creates a scalar value.
	 *
	 * @param mixed $value
	 * @param int $weight
	 * @return SplFixedArray
	 */
	public function _scalarValue($value, $weight)
	{
		$array = new SplFixedArray(4);
		$array[0] = $value;
	//	$array[0] = '\''.$value.'\'';
		$array[1] = $weight;
		$array[3] = 0;

		return $array;
	} // end _scalarValue();

	/**
	 * Prepares a script variable for further parsing. We do not parse it
	 * into PHP here, because we must check if we have an assignment, incrementation
	 * or something else.
	 *
	 * @param string $name The variable name
	 * @return SplFixedArray
	 */
	public function _prepareScriptVar($name)
	{
		$array = new SplFixedArray(3);
		$array[0] = array($name);
		$array[1] = '$';

		return $array;
	} // end _prepareScriptVar();

	/**
	 * Prepares a template variable for further parsing. We do not parse it
	 * into PHP here, because we must check if we have an assignment, incrementation
	 * or something else.
	 *
	 * @param string $name The variable name
	 * @return SplFixedArray
	 */
	public function _prepareTemplateVar($name)
	{
		$array = new SplFixedArray(3);
		$array[0] = array($name);
		$array[1] = '@';

		return $array;
	} // end _prepareTemplateVar();

	/**
	 * Compiles a language variable.
	 * @param string $group The group name
	 * @param string $id The message ID name
	 * @param int $weight The token weight
	 * @return SplFixedArray
	 */
	public function _compileLanguageVar($group, $id, $weight)
	{
		if($this->_tpl->getTranslationInterface() == null)
		{
			throw new Opt_NotSupported_Exception('language variable call', 'no translation interface installed');
		}

		$array = new SplFixedArray(4);
		$array[0] = '$this->_tf->_(\''.$group.'\',\''.$id.'\')';
		$array[1] = $weight;
		$array[3] = 0;

		return $array;
	} // end _compileLanguageVar();

	/**
	 * Compiles the variable call in the specified context. It processes the containers,
	 * assignments and other stuff directly related to the variables, returning an
	 * SplFixedArray object with token information.
	 *
	 * @param array $variable The list of container elements
	 * @param string $type The variable type
	 * @param integer $weight The expression weight
	 * @param integer $context The variable occurence context (normal, assignment, etc.)
	 * @param string $contextInfo The information provided by the context
	 * @param string $extra The support for object and array calls
	 * @return SplFixedArray
	 */
	public function _compileVariable(array $variable, $type, $weight, $context = 0, $contextInfo = null, $extra = null)
	{
		$answer = new SplFixedArray(4);
		$answer[3] = 0;

		// If we have an assignment context, we must mark it in the created
		// expression node for the _finalize() method in order to choose
		// an appropriate expression type.
		if($context == self::CONTEXT_ASSIGN)
		{
			$answer[3] = 1;
		}

		if($type == '@')
		{
			// Local variables must be handled differently.
			$count = sizeof($variable);
			$final = $count - 1;
			$localWeight = 0;
			$code = '';
			foreach($variable as $id => $item)
			{
				if($code == '')
				{
					if(($to = $this->_compiler->convert('##var_'.$item)) != '##var_'.$item)
					{
						$code .= $to;						
					}
					else
					{
						$code .= '$ctx->_vars[\''.$item.'\']';
					}
					$localWeight += self::SINGLE_VAR;
					continue;
				}
				if(is_object($item))
				{
					$localWeight += $item[1];
					$code .= '['.$item[0].']';
				}
				elseif(ctype_digit($item))
				{
					$code .= '['.$item.']';
					$localWeight += self::CONTAINER_ITEM_WEIGHT;
				}
				else
				{
					$code .= '[\''.$item.'\']';
					$localWeight += self::CONTAINER_ITEM_WEIGHT;
				}
			}

			switch($context)
			{
				case self::CONTEXT_ASSIGN:
					$code .= '='.$contextInfo[0];
					break;
				case self::CONTEXT_EXISTS:
					$code = 'isset('.$code.')';
					break;
				case self::CONTEXT_POSTDECREMENT:
					$code .= '--';
					break;
				case self::CONTEXT_POSTINCREMENT:
					$code .= '++';
					break;
				case self::CONTEXT_PREDECREMENT:
					$code = '--'.$code;
					break;
				case self::CONTEXT_PREINCREMENT:
					$code = '++'.$code;
					break;
			}
			$answer[0] = $code;
			$answer[1] = $localWeight;

			return $answer;
		}

		// Now official variables
		$defaultFormat = null;
		$state = array(
			'further'	=> false,
			'section'	=> null
		);

		// The variable scanner
		$proc = null;
		if($this->_compiler->isProcessor('section') !== null)
		{
			$proc = $this->_compiler->processor('section');
		}

		$count = sizeof($variable);
		$final = $count - 1;
		$localWeight = 0;
		$code = '';
		$path = '';
		$previous = null;
		foreach($variable as $id => $item)
		{			
			// Handle conversions
			$previous = $path;
			if($path == '')
			{
				// Parsing the first element. First, check the conversions.
				$path = $item;
				$state['first'] = true;
				if(($to = $this->_compiler->convert('##simplevar_'.$item)) != '##simplevar_'.$item)
				{
					$item = $to;
				}
				elseif(($to = $this->_compiler->convert('##rawvar_'.$item)) != '##rawvar_'.$item)
				{
					// TODO: Add support for different contexts!
					$code .= $to;
					continue;
				}
			}
			else
			{
				// Parsing one of the later elements
				$path .= '.'.$item;
				$state['first'] = false;
			}

			// Processing section calls
			if($proc !== null)
			{
				if($state['section'] === null)
				{
					// Check if any section with the specified name exists.
					$sectionName = $this->_compiler->convert($item);

					if(($section = $proc->getSection($sectionName)) !== null)
					{
						$path = $sectionName;
						$state['section'] = $section;

						if($id == $final)
						{
							// This is the last element
							$hook = 'section:item'.$this->_dfCalls[$context];
							if(!$section['format']->property($hook))
							{
								var_dump($hook);
								throw new Opt_Format_Exception('The operation '.$hook.' is not supported by format '.$section['format']->getName());
							}
							$section['format']->assign('value', $contextInfo[0]);
							$section['format']->assign('code', $code);

							$code = $section['format']->get($hook);
							$localWeight = self::SECTION_ITEM_WEIGHT;

							break;
						}
						continue;
					}
				}
				else
				{
					// The section has been found, we need to process the item.
					// TODO: Perhaps INCREMENT and DECREMENT must have here a different code...
					// We must remember that the container call may be longer and they may refer
					// to the other part of the chain.
					$state['section']['format']->assign('item', $item);

					$hook = 'section:variable';
					if($id == $final)
					{
						$hook .= $this->_dfCalls[$context];
						$section['format']->assign('value', $contextInfo[0]);
						$section['format']->assign('code', $code);
						if(!$section['format']->property($hook))
						{
							throw new Opt_Format_Exception('The operation '.$hook.' is not supported by format '.$section['format']->getName());
						}
					}
					$code = $section['format']->get($hook);
					$localWeight = self::SECTION_VARIABLE_WEIGHT;

					$state['section'] = null;

					continue;
				}
			}

			// Now, the normal container calls
			if($id == 0)
			{
				// The first element processing
				$format = $this->_compiler->getFormat($path, true);
				if(!$format->supports('variable'))
				{
					throw new Opt_Format_Exception('Variable access is not supported by the format '.$format->getName());
				}
				// Check if the format supports capturing the whole container
				if($format->property('variable:capture'))
				{
					$isDynamic = false;
					foreach($variable as $id => $item)
					{
						if(is_object($item))
						{
							$isDynamic = true;
						}
					}

					$format->assign('items', $variable);
					$format->assign('dynamic', $isDynamic);
					$hook = 'capture';
				}
				// An ordinary call
				else
				{
					$hook = 'item';
				}
				$format->assign('item', $item);
				if($context > 0)
				{
					$format->assign('value', $contextInfo[0]);
					$format->assign('code', $code);

					if(!$format->property('variable:'.$hook.$this->_dfCalls[$context]))
					{
						throw new Opt_Format_Exception('Variable '.ltrim($this->_dfCalls[$context], '.').' is not supported by the format '.$format->getName().' in '.$path);
					}
				}
				$code = $format->get('variable:'.$hook.$this->_dfCalls[$context]);
				$localWeight = $count * self::CONTAINER_ITEM_WEIGHT;
				if($hook == 'capture')
				{
					break;
				}
			}
			else
			{
				$format = $this->_compiler->getFormat($path, true);
				$hook = 'item:item';
				$format->assign('item', $item);
				if($id == $final)
				{
					$hook .= $this->_dfCalls[$context];
					$format->assign('value', $contextInfo[0]);
					$format->assign('code', $code);

					if($context > 0 && !$format->property($hook))
					{
						throw new Opt_OperationNotSupported_Exception($path, $this->_dfCalls[$context]);
					}
					$code .= $format->get($hook);
				}
				else
				{
					$code .= $format->get($hook);
				}
				$localWeight += self::CONTAINER_ITEM_WEIGHT;
			}
		}
		$answer[0] = $code;
		$answer[1] = $localWeight + $weight;
		return $answer;
	} // end _compileVariable();

	/**
	 * Processes a binary operator that connects two expressions.
	 *
	 * @param string $operator The PHP operator
	 * @param SplFixedArray $expr1 The left expression
	 * @param SplFixedArray $expr2 The right expression
	 * @param int $weight The operator weight
	 * @return SplFixedArray
	 */
	public function _stdOperator($operator, SplFixedArray $expr1, SplFixedArray $expr2, $weight)
	{
		if($operator == '.')
		{
			$expr1[0] = '(string)'.$expr1[0].$operator.'(string)'.$expr2[0];
		}
		else
		{
			$expr1[0] = $expr1[0].$operator.$expr2[0];
		}
		$expr1[1] += $expr2[1] + $weight;
		$expr1[3] = 0;

		return $expr1;
	} // end _stdOperator();

	/**
	 * Processes an unary operator for a single expression.
	 *
	 * @param string $operator The PHP operator
	 * @param SplFixedArray $expr The expression
	 * @param int $weight The operator weight
	 * @return SplFixedArray
	 */
	public function _unaryOperator($operator, SplFixedArray $expr, $weight)
	{
		$expr[0] = $operator.$expr[0];
		$expr[1] += $weight;
		$expr[3] = 0;

		return $expr;
	} // end _unaryOperator();

	/**
	 * The compound expression operator parsing.
	 *
	 * @param string $operator The used operator name
	 * @param array $arguments The operator arguments
	 * @param int $weight The operator weight
	 * @return SplFixedArray
	 */
	public function _expressionOperator($operator, array $arguments, $weight)
	{
		// More complex expressions should be sanitized to avoid
		// potential problems with operator precedence.
		foreach($arguments as &$arg)
		{
			if($arg[1] >= 5)
			{
				$arg[0] = '('.$arg[0].')';
			}
		}

		// Select the operator and the action.
		$containerFormat = $this->_compiler->getFormat('#container', true);
		switch($operator)
		{
			case 'contains':
				$containerFormat->assign('container', $arguments[0][0]);
				$containerFormat->assign('values', $arguments[1][0]);
				$containerFormat->assign('optimize', false);
				$finalExpression = $containerFormat->get('container:contains');
				$finalWeight = $arguments[0][1] + $arguments[1][1] + $weight;
				break;
			case 'contains_both':
				$op = '&&';
			case 'contains_either':
				$containerFormat->assign('container', $arguments[0][0]);
				$containerFormat->assign('values', array($arguments[1][0], $arguments[2][0]));
				$containerFormat->assign('optimize', $arguments[0][1] >= 5);
				$containerFormat->assign('operator', (isset($op) ? $op : '||'));
				$finalExpression = $containerFormat->get('container:contains');
				$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
				break;
			case 'contains_neither':
				$containerFormat->assign('container', $arguments[0][0]);
				$containerFormat->assign('values', array($arguments[1][0], $arguments[2][0]));
				$containerFormat->assign('optimize', $arguments[0][1] >= 5);
				$containerFormat->assign('operator', '&&');
				$finalExpression = $containerFormat->get('container:notContains');
				$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
				break;
			case 'between':
				// Decide if we need an optimization, when the tested first expression is too complex.
				if($arguments[0][1] < 5)
				{
					// OK, this is pretty lightweight, we can duplicate it
					$finalExpression = $arguments[1][0].' < '.$arguments[0][0].' && '.$arguments[0][0].' < '.$arguments[2][0];
					$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
				}
				else
				{
					// This is hard. The result of the first expression should be stored in a
					// temporary variable in order not to calculate everything twice.
					$finalExpression = $arguments[1][0].' < ($__expru_'.$this->_unique.' = '.$arguments[0][0].') && $__expru_'.$this->_unique.' < '.$arguments[2][0];
					$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
					$this->_unique++;
				}
				break;
			case 'not_between':
				// Decide if we need an optimization, when the tested first expression is too complex.
				if($arguments[0][1] < 5)
				{
					// OK, this is pretty lightweight, we can duplicate it
					$finalExpression = $arguments[1][0].' >= '.$arguments[0][0].' || '.$arguments[0][0].' >= '.$arguments[2][0];
					$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
				}
				else
				{
					// This is hard. The result of the first expression should be stored in a
					// temporary variable in order not to calculate everything twice.
					$finalExpression = $arguments[1][0].' >= ($__expru_'.$this->_unique.' = '.$arguments[0][0].') || $__expru_'.$this->_unique.' >= '.$arguments[2][0];
					$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
					$this->_unique++;
				}
				break;
			case 'either':
				// Decide if we need an optimization, when the tested first expression is too complex.
				if($arguments[0][1] < 5)
				{
					// OK, this is pretty lightweight, we can duplicate it
					$finalExpression = $arguments[1][0].' == '.$arguments[0][0].' || '.$arguments[0][0].' == '.$arguments[2][0];
					$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
				}
				else
				{
					// This is hard. The result of the first expression should be stored in a
					// temporary variable in order not to calculate everything twice.
					$finalExpression = $arguments[1][0].' == ($__expru_'.$this->_unique.' = '.$arguments[0][0].') || $__expru_'.$this->_unique.' == '.$arguments[2][0];
					$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
					$this->_unique++;
				}
				break;
			case 'neither':
				// Decide if we need an optimization, when the tested first expression is too complex.
				if($arguments[0][1] < 5)
				{
					// OK, this is pretty lightweight, we can duplicate it
					$finalExpression = $arguments[1][0].' !== '.$arguments[0][0].' && '.$arguments[0][0].' !== '.$arguments[2][0];
					$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
				}
				else
				{
					// This is hard. The result of the first expression should be stored in a
					// temporary variable in order not to calculate everything twice.
					$finalExpression = $arguments[1][0].' !== ($__expru_'.$this->_unique.' = '.$arguments[0][0].') && $__expru_'.$this->_unique.' !== '.$arguments[2][0];
					$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
					$this->_unique++;
				}
				break;
			case 'is_in':
				$containerFormat->assign('container', $arguments[1][0]);
				$containerFormat->assign('values', $arguments[0][0]);
				$containerFormat->assign('optimize', false);
				$finalExpression = $containerFormat->get('container:contains');
				$finalWeight = $arguments[0][1] + $arguments[1][1] + $weight;
				break;
			case 'is_not_in':
				$containerFormat->assign('container', $arguments[1][0]);
				$containerFormat->assign('values', $arguments[0][0]);
				$containerFormat->assign('optimize', false);
				$finalExpression = $containerFormat->get('container:notContains');
				$finalWeight = $arguments[0][1] + $arguments[1][1] + $weight;
				break;
			case 'is_both_in':
				$op = ' && ';
			case 'is_either_in':
				// TODO: Optimize!
				$containerFormat->assign('container', $arguments[1][0]);
				$containerFormat->assign('values', $arguments[0][0]);
				$containerFormat->assign('optimize', false);
				$finalExpression = $containerFormat->get('container:contains').(isset($op) ? $op : ' || ');

				$containerFormat->assign('container', $arguments[2][0]);
				$containerFormat->assign('values', $arguments[0][0]);
				$containerFormat->assign('optimize', false);
				$finalExpression .= $containerFormat->get('container:contains');

				$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
				break;
			case 'is_neither_in':
				// TODO: Optimize!
				$containerFormat->assign('container', $arguments[1][0]);
				$containerFormat->assign('values', $arguments[0][0]);
				$containerFormat->assign('optimize', false);
				$finalExpression = $containerFormat->get('container:notContains').' && ';

				$containerFormat->assign('container', $arguments[2][0]);
				$containerFormat->assign('values', $arguments[0][0]);
				$containerFormat->assign('optimize', false);
				$finalExpression .= $containerFormat->get('container:notContains');

				$finalWeight = $arguments[0][1] + $arguments[1][1] + $arguments[2][1] + $weight;
				break;
			default:
				throw new Opt_Expression_Exception('Unknown operator type: '.$operator);
		}
		$arguments[0][0] = $finalExpression;
		$arguments[0][1] = $finalWeight;
		$arguments[0][3] = 0;

		return $arguments[0];
	} // end _expressionOperator();

	/**
	 * Packs the expression within parentheses.
	 *
	 * @param string $what The parenthese type
	 * @param SplFixedArray $expr The expression to pack
	 * @param int $weight The parentheses weight
	 * @return SplFixedArray
	 */
	public function _package($what, SplFixedArray $expr, $weight)
	{
		$expr[0] = '('.$expr[0].')';
		$expr[1] += $weight;

		return $expr;
	} // end _package();

	/**
	 * Produces a container constructor.
	 *
	 * @param array $list The list of container items.
	 * @param int $weight The initial container weight
	 * @return SplFixedArray
	 */
	public function _containerValue($list, $weight)
	{
		if($list === null)
		{
			$obj = new SplFixedArray(4);
			$obj[0] = 'array()';
			$obj[1] = $weight;
			$obj[3] = 0;
		}
		else
		{
			$code = 'array(';
			$cardinal = array();
			$associative = array();

			// Put the non-indexed elements in the first place.
			foreach($list as $item)
			{
				if($item[0] === null)
				{
					$cardinal[] = $item;
				}
				else
				{
					$associative[] = $item;
				}
			}
			foreach($cardinal as $item)
			{
				$weight += $item[1][1];
				$code .= $item[1][0].', ';
			}
			foreach($associative as $item)
			{
				$weight += $item[0][1] + $item[1][1];
				$code .= $item[0][0].' => '.$item[1][0].',';
			}
			$obj = new SplFixedArray(4);
			$obj[0] = $code . ')';
			$obj[1] = $weight;
			$obj[3] = 0;
		}
		return $obj;
	} // end _containerValue();

	/**
	 * Creates a pair of two elements.
	 *
	 * @param mixed $e1 First element.
	 * @param mixed $e2 Second element.
	 * @return SplFixedArray
	 */
	public function _pair($e1, $e2)
	{
		$array = new SplFixedArray(2);
		$array[0] = $e1;
		$array[1] = $e2;
		return $array;
	} // end _pair();

	/**
	 * Produces a functional which can be later used to create either
	 * function or method call.
	 *
	 * @param string $identifier The function or method name.
	 * @param array $arguments The list of arguments.
	 * @return SplFixedArray
	 */
	public function _makeFunctional($identifier, array $arguments)
	{
		$array = new SplFixedArray(2);
		$array[0] = (string)$identifier;
		$array[1] = $arguments;

		return $array;
	} // end _makeFunctional();

	/**
	 * Creates a function from a functional.
	 *
	 * @param SplFixedArray $functional The functional to process.
	 * @return SplFixedArray
	 */
	public function _makeFunction(SplFixedArray $functional)
	{
		if(($name = $this->_compiler->isFunction($functional[0])) === null)
		{
			throw new Opt_Expression_Exception('Function '.$functional[0].' cannot be used in templates.');
		}

		// Determine the requested argument order.
		if($name[0] == '#')
		{
			$pos = strpos($name, '#', 1);
			if($pos === false)
			{
				throw new Opt_Expression_Exception('Missing trailing token (#) in the function definition '.$name);
			}
			$codes = explode(',', substr($name, 1, $pos - 1));
			$name = substr($name, $pos+1, strlen($name));
			$newArgs = array();
			$i = 0;
			foreach($codes as $code)
			{
				$data = explode(':', $code);
				if(!isset($functional[1][$i]))
				{
					if(!isset($data[1]))
					{
						throw new Opt_Expression_Exception('Argument '.$i.' is not defined in function '.$name);
					}
					$array = new SplFixedArray(2);
					$array[0] = $data[1];
					$array[1] = self::SCALAR_WEIGHT;
					$newArgs[(int)$data[0]-1] = $array;
				}
				else
				{
					$newArgs[(int)$data[0]-1] = $functional[1][$i];
				}
				$i++;
			}
			$functional[1] = $newArgs;
		}
		// Parse the argument list.
		$code = $name.'(';
		$weight = self::FUNCTION_WEIGHT;
		$first = true;
		foreach($functional[1] as $argument)
		{
			if(!$first)
			{
				$code .= ',';
			}
			else
			{
				$first = false;
			}
			$code .= $argument[0];
			$weight += $argument[1];
		}
		$obj = new SplFixedArray(4);
		$obj[0] = $code.')';
		$obj[1] = $weight;
		$obj[3] = 0;
		return $obj;
	} // end _makeFunction();

	/**
	 * Processes the object access operator from a template variable
	 * or a container.
	 *
	 * @param SplFixedArray $variable The parent variable
	 * @param string $current The object field name
	 * @return SplFixedArray
	 */
	public function _buildObjectFieldDynamic(SplFixedArray $variable, $current)
	{
		if(!$this->_tpl->allowObjects)
		{
			throw new Opt_NotSupported_Exception('direct object access', 'disabled by the configuration');
		}

		$variable = $this->_compileVariable($variable[0], $variable[1], 0);

		$variable[0] .= '->'.(string)$current;
		$variable[1] += self::OBJECT_FIELD_CALL_WEIGHT;
		return $variable;
	} // end _buildObjectFieldDynamic();

	/**
	 * Processes the object access operator from a static class.
	 *
	 * @param string $className The static class name
	 * @param string $current The class field name
	 * @return SplFixedArray
	 */
	public function _buildObjectFieldStatic($className, $current)
	{
		if(!$this->_tpl->allowObjects)
		{
			throw new Opt_NotSupported_Exception('direct object access', 'disabled by the configuration');
		}

		if(($compiled = $this->_compiler->isClass($token)) !== null)
		{
			throw new Opt_ItemNotAllowed_Exception('Class', $className);
		}
		$variable = new SplFixedArray(4);
		$variable[0] = $compiled.'::$'.(string)$current;
		$variable[1] = self::STATIC_FIELD_CALL_WEIGHT;
		$variable[3] = 0;
		return $variable;
	} // end _buildObjectFieldStatic();

	/**
	 * Processes the object access operator from an existing PHP
	 * call (arrays, objects, functions, methods etc.)
	 *
	 * @param SplFixedArray $variable The parent item
	 * @param string $current The object field name
	 * @return SplFixedArray
	 */
	public function _buildObjectFieldNext(SplFixedArray $variable, $current)
	{
		if(!$this->_tpl->allowObjects)
		{
			throw new Opt_NotSupported_Exception('direct object access', 'disabled by the configuration');
		}

		$variable[0] .= '->'.(string)$current;
		$variable[1] += self::OBJECT_FIELD_CALL_WEIGHT;
		return $variable;
	} // end _buildObjectFieldNext();

	/**
	 * Processes the method access operator from a template variable
	 * or a container.
	 *
	 * @param SplFixedArray $variable The parent variable
	 * @param SplFixedArray $current The method functional
	 * @return SplFixedArray
	 */
	public function _buildMethodDynamic(SplFixedArray $variable, $current)
	{
		if(!$this->_tpl->allowObjects)
		{
			throw new Opt_NotSupported_Exception('direct object access', 'disabled by the configuration');
		}

		$variable = $this->_compileVariable($variable[0], $variable[1], 0);

		$variable[0] .= '->'.(string)$current[0].'(';
		$first = true;
		// Process the method arguments
		foreach($current[1] as $item)
		{
			if(!$first)
			{
				$variable[0] .= ',';
			}
			else
			{
				$first = false;
			}
			$variable[0] .= $item[0];
			$variable[1] += $item[1];
		}
		$variable[0] .= ')';
		$variable[1] += self::METHOD_CALL_WEIGHT;
		return $variable;
	} // end _buildMethodDynamic();

	/**
	 * Processes the object access operator from a static class.
	 *
	 * @param string $className The static class name.
	 * @param SplFixedArray $current The method functional.
	 * @return SplFixedArray
	 */
	public function _buildMethodStatic($className, $current)
	{
		if(!$this->_tpl->allowObjects)
		{
			throw new Opt_NotSupported_Exception('direct class access', 'disabled by the configuration');
		}

		if(($compiled = $this->_compiler->isClass($className)) === null)
		{
			throw new Opt_ItemNotAllowed_Exception('Class', $className);
		}
		$variable = new SplFixedArray(4);
		$variable[0] = $compiled.'::'.(string)$current[0].'(';
		$variable[1] = self::METHOD_CALL_WEIGHT;
		$first = true;
		// Process the method arguments
		foreach($current[1] as $item)
		{
			if(!$first)
			{
				$variable[0] .= ',';
			}
			else
			{
				$first = false;
			}
			$variable[0] .= $item[0];
			$variable[1] += $item[1];
		}
		$variable[0] .= ')';
		$variable[3] = 0;
		return $variable;
	} // end _buildMethodStatic();

	/**
	 * Processes the method access operator from another PHP
	 * call (methods, functions, objects, arrays etc.)
	 *
	 * @param SplFixedArray $variable The parent item
	 * @param SplFixedArray $current The method functional
	 * @return SplFixedArray
	 */
	public function _buildMethodNext(SplFixedArray $variable, $current)
	{
		if(!$this->_tpl->allowObjects)
		{
			throw new Opt_NotSupported_Exception('direct object access', 'disabled by the configuration');
		}

		$variable[0] .= '->'.(string)$current[0].'(';
		$first = true;
		// Process the method arguments
		foreach($current[1] as $item)
		{
			if(!$first)
			{
				$variable[0] .= ',';
			}
			else
			{
				$first = false;
			}
			$variable[0] .= $item[0];
			$variable[1] += $item[1];
		}
		$variable[0] .= ')';
		$variable[1] += self::METHOD_CALL_WEIGHT;
		return $variable;
	} // end _buildMethodNext();

	/**
	 * Processes the array access operator from a template
	 * variable or container.
	 *
	 * @param SplFixedArray $variable The parent variable
	 * @param SplFixedArray $current The index expression
	 * @return SplFixedArray
	 */
	public function _buildArrayDynamic(SplFixedArray $variable, SplFixedArray $current)
	{
		if(!$this->_tpl->allowArrays)
		{
			throw new Opt_NotSupported_Exception('direct array access', 'disabled by the configuration');
		}

		$variable = $this->_compileVariable($variable[0], $variable[1], 0);

		$variable[0] .= '['.(string)$current[0].']';
		$variable[1] += self::ARRAY_CALL_WEIGHT + $current[1];

		
		return $variable;
	} // end _buildArrayDynamic();

	/**
	 * Processes the array access operator from another PHP call
	 * (objects, arrays).
	 *
	 * @param SplFixedArray $variable The parent item
	 * @param SplFixedArray $current The index expression
	 * @return SplFixedArray
	 */
	public function _buildArrayNext(SplFixedArray $variable, SplFixedArray $current)
	{
		if(!$this->_tpl->allowArrays)
		{
			throw new Opt_NotSupported_Exception('direct array access', 'disabled by the configuration');
		}

		$variable[0] .= '['.(string)$current[0].']';
		$variable[1] += self::ARRAY_CALL_WEIGHT + $current[1];
		return $variable;
	} // end _buildArrayNext();

	/**
	 * Processes some extra objective calls: cloning and creating
	 * new objects.
	 *
	 * @param string $action The action: 'new' or 'clone'
	 * @param mixed $what The action argument
	 * @param int $weight The action weight
	 * @return SplFixedArray
	 */
	public function _objective($action, $what, $weight)
	{
		if(!$this->_tpl->allowObjects || !$this->_tpl->allowObjectCreation)
		{
			throw new Opt_NotSupported_Exception('object creation', 'disabled by the configuration');
		}
		switch($action)
		{
			case 'clone':
				$answer = $what;
				$answer[0] = 'clone '.$answer[0];
				$answer[1] += $weight;
				$answer[3] = 0;
				break;
			case 'new':
				if(($compiled = $this->_compiler->isClass($what[0])) === null)
				{
					throw new Opt_ItemNotAllowed_Exception('Class', $what[0]);
				}
				$answer = new SplFixedArray(4);
				$answer[0] = 'new '.$compiled;
				$answer[1] = $weight;
				$answer[3] = 0;
				
				// Process the constructor arguments
				if(sizeof($what[1]) > 0)
				{
					$answer[0] .= '(';
					$first = true;
					foreach($what[1] as $item)
					{
						if(!$first)
						{
							$answer[0] .= ',';
						}
						else
						{
							$first = false;
						}
						$answer[0] .= $item[0];
						$answer[1] += $item[1];
					}
					$answer[0] .= ')';
				}
		}
		return $answer;
	} // end _objective();

	/**
	 * Compiles the operation on a PHP call, such as object
	 * field or array item.
	 *
	 * @param string $operation The operation to perform
	 * @param SplFixedArray $var The variable
	 * @param SplFixedArray $expr The assigned expression
	 * @param int $weight The operation weight
	 * @return SplFixedArray
	 */
	public function _compilePhpOperation($operation, SplFixedArray $var, $expr, $weight)
	{
		switch($operation)
		{
			case' assign':
				$var[0] .= ' = '.$expr[0];
				$var[1] += $weight + $expr[1];
				$var[3] = 1;
				break;
			case 'preincrement':
				$var[0] = '++'.$var[0];
				$var[1] += $weight;
				$var[3] = 0;
				break;
			case 'postincrement':
				$var[0] .= '++';
				$var[1] += $weight;
				$var[3] = 0;
				break;
			case 'predecrement':
				$var[0] = '--'.$var[0];
				$var[1] += $weight;
				$var[3] = 0;
				break;
			case 'postdecrement':
				$var[0] .= $var[0].'--';
				$var[1] += $weight;
				$var[3] = 0;
				break;
		}

		return $var;
	} // end _compilePhpAssign();
} // end Opt_Expression_Standard;
