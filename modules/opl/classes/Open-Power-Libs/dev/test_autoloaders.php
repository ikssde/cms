<?php
	require('./Doctrine/Doctrine.php');
	spl_autoload_register(array('Doctrine', 'autoload'));

    // OPL Initialization
    require('../lib/Base.php');
    Opl_Loader::mapLibrary('Opl', '../lib/');
	Opl_Loader::register();
    Opl_Registry::setState('opl_debug_console', true);

	Opl_Debug::write('Debug loading');

?>
