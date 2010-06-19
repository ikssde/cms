<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 *
 */

/**
 * The generic error handler for OPL libraries and other exceptions.
 * It generates a convenient error screen and displays it in the
 * browser.
 *
 * @author Jacek Jędrzejewski
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_ErrorHandler
{
	/**
	 * The port list.
	 * @var array
	 */
	protected $_ports = array();

	/**
	 * The informer list.
	 * @var array
	 */
	protected $_informers = array();

	/**
	 * Creates an instance of the error handler.
	 */
	public function __construct()
	{
		$this->addInformer('backtrace', 'Opl_ErrorHandler_Informer_Backtrace');
		$this->addInformer('stack', 'Opl_ErrorHandler_Informer_Stack');
		$this->addInformer('information', 'Opl_ErrorHandler_Informer_ErrorInformation');
		$this->addInformer('dependency', 'Opl_ErrorHandler_Informer_Dependency');
	} // end __construct();

	/**
	 * Registers a new port for exception handling.
	 * 
	 * @param Opl_ErrorHandler_Port_Interface $port The registered port.
	 */
	public function addPort(Opl_ErrorHandler_Port_Interface $port)
	{
		$this->_ports[] = $port;
	} // end addPort();

	/**
	 * Returns true, if the specified port has been registered in the
	 * error handler.
	 *
	 * @return boolean
	 */
	public function hasPort(Opl_ErrorHandler_Port_Interface $port)
	{
		foreach($this->_ports as $matchedPort)
		{
			if($matchedPort === $port)
			{
				return true;
			}
		}
		return false;
	} // end hasPort();

	/**
	 * Registers a new informer under the specified string identifier. If the
	 * identifier is not free, the method returns false. The informer may be
	 * either an object of Opl_ErrorHandler_Informer_Interface or the class
	 * name that implements this interface.
	 *
	 * @throws InvalidArgumentException
	 * @param string $name The informer identifier
	 * @param Opl_ErrorHandler_Informer_Interface|string $informer The informer interface
	 * @return boolean True on success.
	 */
	public function addInformer($name, $informer)
	{
		if(!is_string($informer) && !$informer instanceof Opl_ErrorHandler_Informer_Interface)
		{
			throw new InvalidArgumentException('The second argument is neither a class name string nor an object implementing Opl_ErrorHandler_Informer_Interface.');
		}

		if(isset($this->_informers[(string)$name]))
		{
			return false;
		}
		$this->_informers[(string)$name] = $informer;
		return true;
	} // end addInformer();

	/**
	 * Returns the informer object under the specified identifier. If the
	 * informer does not exist or does not implement Opl_ErrorHandler_Informer_Interface,
	 * it returns NULL.
	 * 
	 * @param string $name The informer identifier
	 * @return Opl_ErrorHandler_Informer_Interface|NULL
	 */
	public function getInformer($name)
	{
		if(!isset($this->_informers[(string)$name]))
		{
			return NULL;
		}

		// Lazy-load the informer in the string representation.
		if(is_string($this->_informers[(string)$name]))
		{
			$name = $this->_informers[(string)$name];
			$object = new $name('http');
			if(!$object instanceof Opl_ErrorHandler_Informer_Interface)
			{
				// Invalid value, skip it!
				unset($this->_informers[(string)$name]);
				return NULL;
			}
			$this->_informers[(string)$name] = $object;
		}
		return $this->_informers[(string)$name];
	} // end getInformer();

	/**
	 * Displays an exception error message. The returned value reports
	 * if the error handler managed to handle the exception.
	 *
	 * @param Exception $exception The exception to be displayed.
	 * @return boolean
	 */
	public function display(Exception $exception)
	{
		$debug = false;
		if(Opl_Registry::getValue('opl_extended_errors'))
		{
			$debug = true;
		}

		// Match the port to the exception.
		foreach($this->_ports as $port)
		{
			if($port->match($exception))
			{
				$libraryName = $port->getName();
				if($debug === true)
				{
					$context = $port->getContext($exception);
				}
				break;
			}
		}
		if(!isset($libraryName))
		{
			return false;
		}
		// Display the error.
		if(ob_get_level() > 0)
		{
			ob_end_clean();
		}
echo <<<EOF
<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>{$libraryName} error</title>
<style type="text/css">
/* <![CDATA[ */
html, body{  margin: 0; padding: 0; font-size: 10pt; background: #ffffff;  }
div#oplErrorFrame { font-family: Arial, Verdana, Tahoma, Helvetica, sans-serif; color: #222222; width: 700px; margin-top: 100px; margin-left: auto; margin-right: auto; padding: 2px; }
div#oplErrorFrame h1{ font-size: 16pt; text-align: center; padding: 10px; margin: 2px 0; background: #ffffff; border-top: 4px solid #e60066; }
div#oplErrorFrame div.object{ border: 1px solid #ffdecc; margin: 2px 0;background: #ffeeee; padding: 0; }
div#oplErrorFrame div.object div{ /*border-left: 15px solid #e33a3a;*/ margin: 0; padding: 1px; }
div#oplErrorFrame p{padding: 5px; margin: 5px 0;}
div#oplErrorFrame p.message { font-size: 13pt; }
div#oplErrorFrame p.code{ font-weight: bold; }
div#oplErrorFrame p span{ margin-right: 6px; }
div#oplErrorFrame p.call{ border-top: 1px solid #e33a3a; margin: 5px; padding: 5px 0; }
div#oplErrorFrame p.call span{ float: none; margin-right: 0; font-family: 'Courier New', Courier, monospaced;  font-size: 12px; }
div#oplErrorFrame p.directive span{ font-weight: bold; }
div#oplErrorFrame p.directive span.good{ color: #009900; }
div#oplErrorFrame p.directive span.maybe{ color: #777700; }
div#oplErrorFrame p.directive span.bad{ color: #770000; }
div#oplErrorFrame p.important{ font-weight: bold; text-align: center; width:100%; }
div#oplErrorFrame p.warning span{	float: left; margin-right: 12px; font-weight: bold; }
div#oplErrorFrame a {font-weight: bold; color: #000000}
div#oplErrorFrame a:hover {}
div#oplErrorFrame ul {list-style: none; margin: 5px 15px; padding: 0}
div#oplErrorFrame ul li {margin: 0; padding: 0}
div#oplErrorFrame ul li p {padding:0;}

div#oplErrorFrame li { margin-top: 2px; margin-bottom: 2px; padding: 0; }
div#oplErrorFrame li.value { font-weight: bold; }
div#oplErrorFrame li span{  margin-right: 6px; }
div#oplErrorFrame li.value span.good{ color: #009900; }
div#oplErrorFrame li.value span.maybe{ color: #777700; }
div#oplErrorFrame li.value span.bad{ color: #770000; }

div#oplErrorFrame code{ font-family: 'Courier New', Courier, monospaced; background: #ffdddd;  }
/* ]]> */
</style>  
</head>
<body>

<div id="oplErrorFrame">
<h1>{$libraryName} error</h1>
<div class="object"><div>

EOF;
echo '  			<p class="message">'.htmlspecialchars($exception->getMessage())."</p>\r\n";
echo '  			<p class="code">'.get_class($exception)."</p>\r\n";
if($debug)
{
	echo '  			<p class="call"><span>'.$exception->getFile().'</span> [<span>'.$exception->getLine()."</span>]</p>\r\n";
}
else
{
	echo "  			<p class=\"call\">Debug mode is disabled. No additional information provided.</p>\r\n";
}
echo "  		</div></div>\r\n";

if($debug)
{
	echo "			<div class=\"object\"><div>\r\n";
	foreach($context as $name => $params)
	{
		$informer = $this->getInformer($name);
		if($informer !== null)
		{
			$informer->display($exception, $params);
		}
		else
		{
			echo "		<p class=\"directive\"><strong>Unknown informer:</strong> ".$name."</p>\r\n";
		}
	}
	echo "  		</div></div>\r\n";
}
echo <<<EOF
</div>
</body>
</html>
EOF;
		return true;
	} // end display();
} // end Opl_ErrorHandler;
