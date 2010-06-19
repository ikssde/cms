<?php
/**
 * Extra mock expression engine used in testing.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

class Extra_Mock_Expression implements Opt_Expression_Interface
{
	/**
	 * The compiler uses this method to send itself to the expression engine.
	 *
	 * @param Opt_Compiler_Class $compiler The compiler object
	 */
	public function setCompiler(Opt_Compiler_Class $compiler)
	{
		/* null */
	} // end setCompiler();

	/**
	 * The role of this method is to parse the expression to the
	 * corresponding PHP code.
	 *
	 * @param String $expression The expression source
	 * @return Array
	 */
	public function parse($expression)
	{
		if(preg_match('/^\#\#(.+)$/', $expression, $found))
		{
			return array('bare' => '$this->_data[\''.$found[1].'\']', 'type' => Opt_Compiler_Class::COMPOUND);
		}
		return array('bare' => '0', 'type' => Opt_Compiler_Class::SCALAR);
	} // end parse();
} // end Extra_Mock_Expression;