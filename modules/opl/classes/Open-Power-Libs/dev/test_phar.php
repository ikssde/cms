<?php
	require_once('../../tools/Opl.phar');
	
    class Opx_Class extends Opl_Class
    {
    	public $directive1;
    } // end Opx_Class;
    
    $obj1 = new Opx_Class;
    
    $obj1->loadConfig('./config.ini');
    if($obj1->directive1 == 'value' && $obj1->directive2 == 'value')
    {
    	echo 'Ini loading OK<br/>';
    }
    
    $obj2 = new Opx_Class;
    $obj2->loadConfig(array('directive1' => 'value', 'directive2' => 'value'));
    if($obj2->directive1 == 'value' && $obj1->directive2 == 'value')
    {
    	echo 'Array loading OK<br/>';
    }
?>