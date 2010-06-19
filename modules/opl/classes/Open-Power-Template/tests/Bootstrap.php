<?php
/**
 * The bootstrap file for unit tests.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

$config = parse_ini_file(dirname(__FILE__).'/../paths.ini', true);
require($config['libraries']['Opl'].'Base.php');
Opl_Loader::loadPaths($config);
Opl_Loader::setCheckFileExists(false);
Opl_Loader::addLibrary('Extra', array('directory' => './Extra/', 'handler' => null));
Opl_Loader::addLibrary('Package', array('directory' => './Package/', 'handler' => null));
Opl_Loader::register();