<?php
// OPL Initialization
require('../lib/Base.php');
Opl_Loader::addLibrary('Opl', array('directory' => '../lib/'));
Opl_Registry::setState('opl_debug_console', true);
Opl_Registry::setState('opl_extended_errors', true);
Opl_Registry::setState('opl_custom_console', true);

Opl_Loader::register();

Opl_Debug_Console::addList('foo', 'Foo');
Opl_Debug_Console::addListOption('foo', 'Bar', 'Joe');

var_dump(Opl_Debug_Console::getLists());

Opl_Debug_Console::display();
