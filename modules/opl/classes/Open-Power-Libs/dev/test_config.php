<?php
    // OPL Initialization
    require('../../lib/opl/base.php');
    Opl_Loader::setDirectory('../../lib/');
    Opl_Registry::setState('opl_debug_console', true);
    Opl_Registry::setState('opl_extended_errors', true);
    spl_autoload_register(array('Opl_Loader', 'autoload'));
    
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