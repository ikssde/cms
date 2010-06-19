<?php
/**
 * The tests for Opl_Getopt.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * @covers Opl_Getopt
 */
class Package_GetoptTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testAutomaticHelpRecognition()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::AUTO_HELP);
		$this->assertFalse($getopt->parse(array('--help')));
	} // end testAutomaticHelpRecognition();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testAutomaticHelpRecognitionWhenDisabled()
	{
		$getopt = new Opl_Getopt();
		$getopt->parse(array('--help'));
	} // end testAutomaticHelpRecognitionWhenDisabled();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testLongOptionsPassedByIteration()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo', null, 'foo'));
		$getopt->addOption(new Opl_Getopt_Option('bar', null, 'bar'));
		$getopt->addOption(new Opl_Getopt_Option('joe', null, 'joe'));
		$this->assertTrue($getopt->parse(array('--foo', '--bar')));

		$recognized = 0;
		$opts = array('foo', 'bar');
		foreach($getopt as $option)
		{
			$this->assertTrue($option instanceof Opl_Getopt_Option);
			if(in_array($option->getName(), $opts))
			{
				$recognized++;
			}
		}
		if($recognized != sizeof($opts))
		{
			$this->fail('Getopt did not recognize 2 options, but '.$recognized);
		}
		return true;
	} // end testLongOptionsPassedByIteration();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testLongOptionsWithUnknown()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo', null, 'foo'));
		$getopt->addOption(new Opl_Getopt_Option('bar', null, 'bar'));
		$getopt->addOption(new Opl_Getopt_Option('joe', null, 'joe'));
		$getopt->parse(array('--foo', '--goo'));
	} // end testLongOptionsWithUnknown();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testLongOptionsDoNotTakeArguments()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo', null, 'foo'));
		$getopt->addOption(new Opl_Getopt_Option('bar', null, 'bar'));
		$getopt->addOption(new Opl_Getopt_Option('joe', null, 'joe'));
		$getopt->parse(array('--foo', '--bar=joe'));
	} // end testLongOptionsDoNotTakeArguments();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testLongOptionalAttributeNotSet()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setArgument(Opl_Getopt_Option::OPTIONAL, Opl_Getopt_Option::ANYTHING);
		$this->assertTrue($getopt->parse(array('--bar')));

		$this->assertTrue($option->isFound());
		$this->assertSame(null, $option->getValue());
	} // end testLongOptionalAttributeNotSet();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testLongOptionalAttributeSet()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setArgument(Opl_Getopt_Option::OPTIONAL, Opl_Getopt_Option::ANYTHING);
		$this->assertTrue($getopt->parse(array('--bar=foo')));

		$this->assertTrue($option->isFound());
		$this->assertEquals('foo', $option->getValue());
	} // end testLongOptionalAttributeSet();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testLongRequiredAttributeNotSet()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::ANYTHING);
		$this->assertTrue($getopt->parse(array('--bar')));
	} // end testRequiredAttributeNotSet();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testLongRequiredAttributeSet()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::ANYTHING);
		$this->assertTrue($getopt->parse(array('--bar=foo')));

		$this->assertTrue($option->isFound());
		$this->assertEquals('foo', $option->getValue());
	} // end testLongRequiredAttributeSet();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testLongAttributeUsedTwiceWithoutIncrementation()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setMaxOccurences(2);
		$getopt->parse(array('--bar', '--bar'));
	} // end testLongAttributeUsedTwiceWithoutIncrementation();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testLongAttributeUsedTwiceWithIncrementation()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_INCREMENTING);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setMaxOccurences(2);
		$this->assertTrue($getopt->parse(array('--bar', '--bar')));
		
		$this->assertEquals(2, $option->getOccurences());
	} // end testLongAttributeUsedTwiceWithIncrementation();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testLongAttributeUsedMoreThanPossible()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_INCREMENTING);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setMaxOccurences(2);
		$getopt->parse(array('--bar', '--bar', '--bar'));
	} // end testLongAttributeUsedMoreThanPossible();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testLongAttributeUsedFewerThanPossible()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_INCREMENTING);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setMinOccurences(2);
		$option->setMaxOccurences(5);
		$getopt->parse(array('--bar'));
	} // end testLongAttributeUsedFewerThanPossible();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testLongAttributeUsedTwiceWithArguments()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_INCREMENTING);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', null, 'bar'));
		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::ANYTHING);
		$option->setMaxOccurences(2);
		$this->assertTrue($getopt->parse(array('--bar=foo', '--bar=bar')));

		$this->assertEquals(2, $option->getOccurences());
		$this->assertTrue(is_array($option->getValue()));
		$this->assertContains('foo', $option->getValue());
		$this->assertContains('bar', $option->getValue());
	} // end testLongAttributeUsedTwiceWithArguments();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testShortOptionsPassedByIteration()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('a', 'a'));
		$getopt->addOption(new Opl_Getopt_Option('b', 'b'));
		$getopt->addOption(new Opl_Getopt_Option('c', 'c'));
		$this->assertTrue($getopt->parse(array('-ab', '-c')));

		$recognized = 0;
		$opts = array('a', 'b', 'c');
		foreach($getopt as $option)
		{
			$this->assertTrue($option instanceof Opl_Getopt_Option);
			if(in_array($option->getName(), $opts))
			{
				$recognized++;
			}
		}
		if($recognized != sizeof($opts))
		{
			$this->fail('Getopt did not recognize 3 options, but '.$recognized);
		}
		return true;
	} // end testShortOptionsPassedByIteration();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testShortOptionsWithUnknown()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo', 'a'));
		$getopt->addOption(new Opl_Getopt_Option('bar', 'b'));
		$getopt->addOption(new Opl_Getopt_Option('joe', 'c'));
		$getopt->parse(array('-d'));
	} // end testShortOptionsWithUnknown();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testShortOptionsDoNotTakeArguments()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo', 'a'));
		$getopt->addOption(new Opl_Getopt_Option('bar', 'b'));
		$getopt->addOption(new Opl_Getopt_Option('joe', 'c'));
		$getopt->parse(array('-a', 'argument', '-b'));
	} // end testShortOptionsDoNotTakeArguments();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testShortRequiredAttributeNotSet()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_SHORT_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('bar', 'a'));
		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::ANYTHING);
		$getopt->parse(array('-a'));
	} // end testShortRequiredAttributeNotSet();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testShortRequiredAttributeSet()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_SHORT_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('a', 'a'));
		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::ANYTHING);
		$this->assertTrue($getopt->parse(array('-a', 'argument')));

		$this->assertTrue($option->isFound());
		$this->assertEquals('argument', $option->getValue());
	} // end testShortRequiredAttributeSet();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testShortAttributeUsedTwiceWithoutIncrementation1()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption($option = new Opl_Getopt_Option('a', 'a'));
		$option->setMaxOccurences(2);
		$getopt->parse(array('-a', '-a'));
	} // end testShortAttributeUsedTwiceWithoutIncrementation1();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testShortAttributeUsedTwiceWithoutIncrementation2()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption($option = new Opl_Getopt_Option('a', 'a'));
		$option->setMaxOccurences(2);
		$getopt->parse(array('-aa'));
	} // end testShortAttributeUsedTwiceWithoutIncrementation2();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testShortAttributeUsedTwiceWithIncrementation1()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_INCREMENTING);
		$getopt->addOption($option = new Opl_Getopt_Option('a', 'a'));
		$option->setMaxOccurences(2);
		$this->assertTrue($getopt->parse(array('-a', '-a')));

		$this->assertEquals(2, $option->getOccurences());
	} // end testShortAttributeUsedTwiceWithIncrementation1();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testShortAttributeUsedTwiceWithIncrementation2()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_INCREMENTING);
		$getopt->addOption($option = new Opl_Getopt_Option('a', 'a'));
		$option->setMaxOccurences(2);
		$this->assertTrue($getopt->parse(array('-aa')));

		$this->assertEquals(2, $option->getOccurences());
	} // end testShortAttributeUsedTwiceWithIncrementation2();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testPlainArguments()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('a', 'a'));
		$getopt->addOption(new Opl_Getopt_Option('foo', null, 'foo'));
		$getopt->addOption($option = new Opl_Getopt_Option('plain'));
		$this->assertTrue($getopt->parse(array('-a', '--foo', 'goo')));

		$this->assertTrue($option->isFound());
		$this->assertSame(array('goo'), $option->getValue());
	} // end testPlainArguments();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 */
	public function testPlainArgumentsVersusShortArguments()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_SHORT_ARGS);
		$getopt->addOption($short = new Opl_Getopt_Option('a', 'a'));

		$short->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::ANYTHING);

		$getopt->addOption(new Opl_Getopt_Option('foo', null, 'foo'));
		$getopt->addOption($option = new Opl_Getopt_Option('plain'));
		$this->assertTrue($getopt->parse(array('-a', 'moo', '--foo', 'goo')));


		$this->assertTrue($short->isFound());
		$this->assertSame('moo', $short->getValue());

		$this->assertTrue($option->isFound());
		$this->assertSame(array('goo'), $option->getValue());
	} // end testPlainArgumentsVersusShortArguments();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @covers Opl_Getopt::_validateArgument
	 */
	public function testArgumentsInteger()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('foo', null, 'foo'));

		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::INTEGER);

		try
		{
			$getopt->parse(array('--foo=42'));
			$this->assertEquals(42, $option->getValue());
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$this->fail('The integer 42 should be valid with Opl_Getopt_Option::INTEGER');
		}

		$ok = false;
		try
		{
			$getopt->parse(array('--foo=42:'));
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$ok = true;
		}
		if(!$ok)
		{
			$this->fail('The string "42:" is not a valid Opl_Getopt_Option::INTEGER');
		}
	} // end testArgumentsIntegerValid();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @covers Opl_Getopt::_validateArgument
	 */
	public function testArgumentsBoolean()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('foo', null, 'foo'));

		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::BOOLEAN);

		try
		{
			$getopt->parse(array('--foo=true'));
			$this->assertSame(true, $option->getValue());
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$this->fail('The boolean true should be valid with Opl_Getopt_Option::BOOLEAN');
		}

		try
		{
			$getopt->parse(array('--foo=false'));
			$this->assertSame(false, $option->getValue());
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$this->fail('The boolean false should be valid with Opl_Getopt_Option::BOOLEAN');
		}

		try
		{
			$getopt->parse(array('--foo=yes'));
			$this->assertSame(true, $option->getValue());
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$this->fail('The boolean yes should be valid with Opl_Getopt_Option::BOOLEAN');
		}

		try
		{
			$getopt->parse(array('--foo=no'));
			$this->assertSame(false, $option->getValue());
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$this->fail('The boolean no should be valid with Opl_Getopt_Option::BOOLEAN');
		}

		$ok = false;
		try
		{
			$getopt->parse(array('--foo=hello'));
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$ok = true;
		}
		if(!$ok)
		{
			$this->fail('The string "hello" is not a valid Opl_Getopt_Option::BOOLEAN');
		}
	} // end testArgumentsBoolean();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @covers Opl_Getopt::_validateArgument
	 */
	public function testArgumentsEnabled()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('foo', null, 'foo'));

		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::ENABLED);

		try
		{
			$getopt->parse(array('--foo=enabled'));
			$this->assertSame(true, $option->getValue());
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$this->fail('The boolean enabled should be valid with Opl_Getopt_Option::ENABLED');
		}

		try
		{
			$getopt->parse(array('--foo=disabled'));
			$this->assertSame(false, $option->getValue());
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$this->fail('The boolean disabled should be valid with Opl_Getopt_Option::ENABLED');
		}

		$ok = false;
		try
		{
			$getopt->parse(array('--foo=hello'));
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$ok = true;
		}
		if(!$ok)
		{
			$this->fail('The string "hello" is not a valid Opl_Getopt_Option::ENABLED');
		}
	} // end testArgumentsEnabled();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::parse
	 * @covers Opl_Getopt::_validateArgument
	 */
	public function testArgumentsString()
	{
		$getopt = new Opl_Getopt(Opl_Getopt::ALLOW_LONG_ARGS);
		$getopt->addOption($option = new Opl_Getopt_Option('foo', null, 'foo'));

		$option->setArgument(Opl_Getopt_Option::REQUIRED, Opl_Getopt_Option::STRING);

		try
		{
			$getopt->parse(array('--foo=hello'));
			$this->assertSame('hello', $option->getValue());
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$this->fail('The string "hello" should be valid with Opl_Getopt_Option::STRING');
		}
		$ok = false;
		try
		{
			$getopt->parse(array('--foo='));
		}
		catch(Opl_Getopt_Exception $exception)
		{
			$ok = true;
		}
		if(!$ok)
		{
			$this->fail('The empty value should not be valid with Opl_Getopt_Option::STRING');
		}
	} // end testArgumentsString();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::addOption
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testDetectDuplicatedLongOption()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo', null, 'foo'));
		$getopt->addOption(new Opl_Getopt_Option('bar', null, 'foo'));
	} // end testDetectDuplicatedLongOption();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::addOption
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testDetectDuplicatedName()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo', null, 'foo'));
		$getopt->addOption(new Opl_Getopt_Option('foo', null, 'bar'));
	} // end testDetectDuplicatedName();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::addOption
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testDetectDuplicatedShortOption()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo', 'a'));
		$getopt->addOption(new Opl_Getopt_Option('bar', 'a'));
	} // end testDetectDuplicatedShortOption();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::addOption
	 * @expectedException Opl_Getopt_Exception
	 */
	public function testDetectDuplicatedPlainOption()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo'));
		$getopt->addOption(new Opl_Getopt_Option('bar'));
	} // end testDetectDuplicatedPlainOption();

	/**
	 * @covers Opl_Getopt::__construct
	 * @covers Opl_Getopt::addOption
	 */
	public function testHasOption()
	{
		$getopt = new Opl_Getopt();
		$getopt->addOption(new Opl_Getopt_Option('foo'));
		$this->assertTrue($getopt->hasOption('foo'));
		$this->assertFalse($getopt->hasOption('bar'));
	} // end testHasOption();
} // end Package_GetoptTest;