<?php
/**
 * The tests for Opl_Registry.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * @covers Opl_Registry
 */
class Package_RegistryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers Opl_Registry::setState
	 * @covers Opl_Registry::getState
	 */
	public function testSettingState()
	{
		Opl_Registry::setValue('foo', 'bar');

		$this->assertEquals(Opl_Registry::getValue('foo'), 'bar');
	} // end testSettingState();

	/**
	 * @covers Opl_Registry::register
	 * @covers Opl_Registry::get
	 */
	public function testSettingValue()
	{
		Opl_Registry::set('foo', 'bar');

		$this->assertEquals(Opl_Registry::get('foo'), 'bar');
	} // end testSettingValue();

	/**
	 * @covers Opl_Registry::exists
	 */
	public function testExistence()
	{
		Opl_Registry::set('foo', 'bar');
		$this->assertTrue(Opl_Registry::exists('foo'));
	} // end testExistence();
} // end Package_RegistryTest;