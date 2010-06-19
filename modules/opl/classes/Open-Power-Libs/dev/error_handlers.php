<?php
$config = parse_ini_file('../paths.ini', true);
require($config['libraries']['Opl'].'Base.php');
Opl_Loader::loadPaths($config);
Opl_Loader::register();

Opl_Registry::setState('opl_extended_errors', true);

try
{
    function test()
    {
        throw new Opl_Debug_Exception('lolz');
        //throw new Opl_NoTranslationInterface_Exception('lo <sdfsf> l');
        $e = new Opt_APIInvalidNodeType_Exception('asd', 'ad');
        $e->setData(array('lolz'));
        throw $e;
    }
    test();
}
catch(Opt_Exception $exception)
{
    $handler = new Opt_ErrorHandler;
    $handler->display($exception);
}
catch(Opl_Exception $exception)
{
    Opl_Error_Handler($exception);
}