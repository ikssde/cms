<?php
// OPL Initialization
require('../lib/Base.php');
Opl_Loader::addLibrary('Opl', array('directory' => '../lib/'));
Opl_Registry::setState('opl_debug_console', true);
Opl_Registry::setState('opl_extended_errors', true);
spl_autoload_register(array('Opl_Loader', 'autoload'));

$obj = new Opl_ErrorHandler;
echo 'If you see this message, it means, this is OK.';
