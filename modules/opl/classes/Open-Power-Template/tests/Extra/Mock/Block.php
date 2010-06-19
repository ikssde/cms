<?php
/**
 * Extra mock block used in testing.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

class Extra_Mock_Block implements Opt_Block_Interface
{

	public function setView(Opt_View $view)
	{
		echo "VIEW PASSED\r\n";
	} // end setView();

	public function onOpen(Array $attributes)
	{
		echo "ON OPEN: ".sizeof($attributes)."\r\n";
		if(isset($attributes['hide']))
		{
			echo "HIDING\r\n";
			return false;
		}
		return true;
	} // end onOpen();

	public function onClose()
	{
		echo "ON CLOSE\r\n";

	} // end onClose();

	public function onSingle(Array $attributes)
	{
		echo "ON SINGLE: ".sizeof($attributes)."\r\n";
	} // end onSingle();

} // end Extra_Mock_Block;