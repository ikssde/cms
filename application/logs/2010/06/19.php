<?php defined('SYSPATH') or die('No direct script access.'); ?>

2010-06-19 08:03:28 --- ERROR: ErrorException [ 2048 ]: Declaration of Model_Test::get() should be compatible with that of Jelly_Model_Core::get() ~ APPPATH/classes\model\test.php [ 3 ]
2010-06-19 08:03:54 --- ERROR: ErrorException [ 2 ]: Missing argument 1 for Jelly_Core::select(), called in C:\wamp\www\application\classes\model\test.php on line 16 and defined ~ MODPATH/jelly\classes\jelly\core.php [ 81 ]
2010-06-19 08:04:07 --- ERROR: ErrorException [ 1 ]: Class 'Database' not found ~ MODPATH/jelly\classes\jelly\core.php [ 83 ]
2010-06-19 09:12:51 --- ERROR: ErrorException [ 1 ]: Class 'Database' not found ~ SYSPATH/classes\kohana\model.php [ 54 ]
2010-06-19 09:13:58 --- ERROR: ErrorException [ 1 ]: Call to undefined method Model_Test::getsth() ~ APPPATH/classes\controller\welcome.php [ 10 ]
2010-06-19 09:25:38 --- ERROR: ErrorException [ 4 ]: parse error, expecting `T_VARIABLE' ~ APPPATH/classes\model\test.php [ 5 ]
2010-06-19 10:27:31 --- ERROR: ErrorException [ 1 ]: Class 'Controller_Frontend_Common' not found ~ APPPATH/classes\controller\welcome.php [ 3 ]
2010-06-19 10:28:17 --- ERROR: ErrorException [ 4096 ]: Argument 1 passed to Kohana_Controller::__construct() must be an instance of Kohana_Request, none given, called in C:\wamp\www\modules\frontend\classes\controller\frontend\common.php on line 9 and defined ~ SYSPATH/classes\kohana\controller.php [ 37 ]
2010-06-19 10:28:58 --- ERROR: ErrorException [ 4096 ]: Argument 1 passed to Kohana_Controller::__construct() must be an instance of Kohana_Request, none given, called in C:\wamp\www\modules\frontend\classes\controller\frontend\common.php on line 9 and defined ~ SYSPATH/classes\kohana\controller.php [ 37 ]
2010-06-19 10:29:04 --- ERROR: ErrorException [ 4 ]: parse error ~ MODPATH/frontend\classes\controller\frontend\common.php [ 9 ]
2010-06-19 10:36:20 --- ERROR: Kohana_Request_Exception [ 0 ]: Unable to find a route to match the URI: favicon.ico ~ SYSPATH/classes\kohana\request.php [ 635 ]
2010-06-19 10:36:21 --- ERROR: Kohana_Request_Exception [ 0 ]: Unable to find a route to match the URI: favicon.ico ~ SYSPATH/classes\kohana\request.php [ 635 ]