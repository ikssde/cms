<?php
/**
 * The tests for Opl_ErrorHandler.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */


/**
 * @covers Opl_ErrorHandler
 */
class Package_ErrorHandlerTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers Opl_ErrorHandler::addPort
	 * @covers Opl_ErrorHandler::hasPort
	 */
	public function testRegisteringPorts()
	{
		$handler = new Opl_ErrorHandler;

		$registeredPort = $observer = $this->getMock('Opl_ErrorHandler_Port_Interface');
		$unregisteredPort = $observer = $this->getMock('Opl_ErrorHandler_Port_Interface');

		$handler->addPort($registeredPort);

		$this->assertTrue($handler->hasPort($registeredPort));
		$this->assertFalse($handler->hasPort($unregisteredPort));
	} // end testRegisteringPorts();

	/**
	 * @covers Opl_ErrorHandler::display
	 */
	public function testOutputCancellationOnMatchedException()
	{
		ob_start();

		ob_start();

		echo 'XYZ';

		$handler = new Opl_ErrorHandler;
		$handler->addPort(new Opl_ErrorHandler_Port_Opl());
		$handler->display(new Opl_Exception('Foo'));

		$out = ob_get_clean();

		if(strpos($out, 'XYZ') !== false)
		{
			$this->fail('The earlier string XYZ was not cancelled by the exception handler.');
		}
		return true;
	} // end testOutputCancellation();

	/**
	 * @covers Opl_ErrorHandler::display
	 */
	public function testOutputCancellationOnUnmatchedException()
	{
		ob_start();

		ob_start();

		echo 'XYZ';

		$handler = new Opl_ErrorHandler;
		// This exception will not be matched
		$handler->display(new Opl_Exception('Foo'));

		$out = ob_get_clean();

		if(strpos($out, 'XYZ') === false)
		{
			$this->fail('The earlier string XYZ was cancelled by the exception handler.');
		}
		return true;
	} // end testOutputCancellationOnUnmatchedException();
} // end Package_ErrorHandlerTest;