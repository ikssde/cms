Opl_Loader::setDirectory('');
Opl_Loader::setDefaultHandler(array('Opl_Loader', 'pharHandler'));
Opl_Loader::addLibrary('%%library%%', array('directory' => 'phar://'.__FILE__));
Opl_Loader::register();

__HALT_COMPILER();
?>