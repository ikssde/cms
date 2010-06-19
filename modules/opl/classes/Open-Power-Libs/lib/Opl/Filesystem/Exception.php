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
 * The exception class for filesystem-related exceptions.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opl_Filesystem_Exception extends Opl_Exception
{
	/**
	 * The name of the file or directory that the exception concerns.
	 * @var string
	 */
	private $_filename = null;

	/**
	 * Sets the file or directory name the exception concerns.
	 *
	 * @param string $filename The file or directory name
	 */
	public function setFilesystemItem($filename)
	{
		$this->_filename = (string)$filename;
	} // end setFilesystemItem();

	/**
	 * Returns the name of the file or directory the exception concerns.
	 *
	 * @return string The name of file or directory.
	 */
	public function getFilesystemItem()
	{
		return $this->_filename;
	} // end getFilesystemItem();
} // end Opl_Filesystem_Exception;