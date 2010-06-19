<?php
/**
 * The tests for Opl_Getopt.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * @covers Opl_Getopt_Option
 */
class Package_Getopt_OptionTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers Opl_Getopt_Option::__construct
	 * @covers Opl_Getopt_Option::getName
	 */
	public function testSettingName()
	{
		$option = new Opl_Getopt_Option('foo');
		$this->assertEquals('foo', $option->getName());
	} // end testSettingName();

	/**
	 * @covers Opl_Getopt_Option::__construct
	 * @covers Opl_Getopt_Option::getShortFlag
	 */
	public function testSettingShortFlagValid()
	{
		$option = new Opl_Getopt_Option('foo', 'a');
		$this->assertEquals('a', $option->getShortFlag());
	} // end testSettingShortFlagValid();

	/**
	 * @covers Opl_Getopt_Option::__construct
	 * @covers Opl_Getopt_Option::getShortFlag
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testSettingShortFlagInvalid()
	{
		$option = new Opl_Getopt_Option('foo', 'ab');
	} // end testSettingShortFlagInvalid();

	/**
	 * @covers Opl_Getopt_Option::__construct
	 * @covers Opl_Getopt_Option::getLongFlag
	 */
	public function testSettingLongFlagValid()
	{
		$option = new Opl_Getopt_Option('foo', null, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_');
		$this->assertEquals('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_', $option->getLongFlag());
	} // end testSettingLongFlagValid();

	/**
	 * @covers Opl_Getopt_Option::__construct
	 * @covers Opl_Getopt_Option::getLongFlag
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testSettingLongFlagInvalidSymbol()
	{
		$option = new Opl_Getopt_Option('foo', null, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_.');
	} // end testSettingShortFlagInvalid();

	/**
	 * @covers Opl_Getopt_Option::__construct
	 * @covers Opl_Getopt_Option::getLongFlag
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testSettingLongFlagInvalidLength()
	{
		$option = new Opl_Getopt_Option('foo', null, '');
	} // end testSettingLongFlagInvalidLength();

	/**
	 * @covers Opl_Getopt_Option::setFound
	 * @covers Opl_Getopt_Option::isFound
	 */
	public function testFoundStatus()
	{
		$option = new Opl_Getopt_Option('foo', 'x');
		$option->setFound(true);
		$this->assertTrue($option->isFound());

		$option->setFound(false);
		$this->assertFalse($option->isFound());

		$option->setFound(17);
		$this->assertTrue($option->isFound());
	} // end testFoundStatus();

	/**
	 * @covers Opl_Getopt_Option::setValue
	 * @covers Opl_Getopt_Option::getValue
	 */
	public function testArgumentValue()
	{
		$option = new Opl_Getopt_Option('foo', 'x');
		$option->setValue('foo');
		$this->assertEquals('foo', $option->getValue());

		$option->setValue(42);
		$this->assertEquals(42, $option->getValue());
	} // end testArgumentValue();

	/**
	 * @covers Opl_Getopt_Option::setMinOccurences
	 * @covers Opl_Getopt_Option::getMinOccurences
	 */
	public function testMinOccurencesSetting()
	{
		$option = new Opl_Getopt_Option('foo', 'x');
		$option->setMinOccurences(5);
		$this->assertEquals(5, $option->getMinOccurences());
	} // end testMinOccurencesSetting();

	/**
	 * @covers Opl_Getopt_Option::setMinOccurences
	 * @covers Opl_Getopt_Option::getMinOccurences
	 */
	public function testMaxOccurencesSetting()
	{
		$option = new Opl_Getopt_Option('foo', 'x');
		$option->setMaxOccurences(5);
		$this->assertEquals(5, $option->getMaxOccurences());
	} // end testMaxOccurencesSetting();

	/**
	 * @covers Opl_Getopt_Option::setMinOccurences
	 * @covers Opl_Getopt_Option::getMinOccurences
	 */
	public function testCountingOccurencesByFoundStatus()
	{
		$option = new Opl_Getopt_Option('foo', 'x');
		$this->assertEquals(0, $option->getOccurences());

		$option->setFound(true);
		$this->assertEquals(1, $option->getOccurences());

		$option->setFound(true);
		$this->assertEquals(2, $option->getOccurences());

		$option->setFound(true);
		$this->assertEquals(3, $option->getOccurences());

		$option->setFound(false);
		$this->assertEquals(0, $option->getOccurences());
	} // end testMaxOccurencesSetting();
} // end Package_Getopt_OptionTest;