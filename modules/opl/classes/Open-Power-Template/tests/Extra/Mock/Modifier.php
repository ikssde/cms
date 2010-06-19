<?php
/**
 * Extra mock expression modifier used in testing.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

class Extra_Mock_Modifier
{
	/**
	 * The mocking modifier. Displays "HI!", so that
	 * it can be tested by the output.
	 *
	 * @static
	 * @param mixed $expression The expression value to modify
	 * @return mixed
	 */
	static public function modifier($expression)
	{
		echo "HI!";

		return $expression;
	} // end modifier();
} // end Extra_Mock_Modifier;