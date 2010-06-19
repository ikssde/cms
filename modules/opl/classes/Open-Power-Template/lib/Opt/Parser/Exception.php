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
 * The class for template parsing errors.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opt_Parser_Exception extends Opt_Exception
{
	/**
	 * The parser name.
	 * @var string
	 */
	private $_parserName;

	/**
	 * The template file name.
	 * @var string
	 */
	private $_templateFile;

	/**
	 * The template line where the error occured.
	 * @var integer
	 */
	private $_templateLine = null;

	/**
	 * Creates a new parser exception. The parser should specify its name and
	 * the name of the template where the error occured. It is also recommended
	 * to specify the line number.
	 *
	 * @param string $message The error message.
	 * @param string $parser The parser name.
	 * @param string $fileName The template file name.
	 * @param integer $line The template line number.
	 */
	public function __construct($message, $parser, $fileName, $line = null)
	{
		$this->message = (string)$message;
		$this->_parserName = (string)$parser;
		$this->_templateFile = (string)$fileName;

		if($line !== null)
		{
			$this->_templateLine = (int)$line;
		}
	} // end __construct();

	/**
	 * Returns the name of the parser that caused the exception.
	 *
	 * @return string
	 */
	public function getParserName()
	{
		return $this->_parserName;
	} // end getParserName();

	/**
	 * Returns the name of the template where the error occured.
	 *
	 * @return string
	 */
	public function getTemplateFile()
	{
		return $this->_templateFile;
	} // end getTemplateFile();

	/**
	 * Returns the number of the line where the error occured. If
	 * the parser does not set the line number, it returns NULL.
	 *
	 * @return integer
	 */
	public function getTemplateLine()
	{
		return $this->_templateLine;
	} // end getTemplateLine();
} // end Opt_Parser_Exception;