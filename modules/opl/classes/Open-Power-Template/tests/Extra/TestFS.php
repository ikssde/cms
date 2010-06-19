<?php
/**
 * The test filesystem wrapper used in many syntax tests in order to keep
 * the whole test case in one file.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

/**
 * The test filesystem class, registered as a stream.
 */
class Extra_TestFS
{
	/**
	 * The list of files.
	 * @var Array
	 */
	static private $files;

	/**
	 * The opened file.
	 * @var String
	 */
	private $file;

	/**
	 * The number of bytes read.
	 * @var Integer
	 */
	private $read;

	/**
	 * Loads the filesystem into memory.
	 * @param String $fs The path to the file with the filesystem.
	 */
	static public function loadFilesystem($fs)
	{
		self::$files = array();
		$lines = file($fs);
		$currentFile = null;
		foreach($lines as $line)
		{
			if(strpos($line, '>>>>') === 0)
			{
				$currentFile = 'test://'.trim(substr($line, 4, strlen($line)));
				self::$files[$currentFile] = '';
				continue;
			}

			if(!is_null($currentFile))
			{
				self::$files[$currentFile] .= $line;
			}
		}
	} // end loadFilesystem();

	/**
	 * PHP stream function - opens a stream
	 * @param String $path The opened file path
	 * @param Integer $mode
	 * @param Mixed $options
	 * @param Mixed $opened_path
	 * @return Boolean
	 */
	public function stream_open($path, $mode, $options, $opened_path)
	{
		$this->file = $path;
		if(!isset(self::$files[$path]))
		{
			return false;
		}
		return true;
	} // end stream_open();

	/**
	 * PHP stream function - closes a stream.
	 */
	public function stream_close()
	{

	} // end stream_close();

	/**
	 * Checks, if the cursor reached end of file.
	 * @return Boolean
	 */
	public function stream_eof()
	{
		return ($this->read >= strlen(self::$files[$this->file]));
	} // end stream_eof();

	/**
	 * PHP stream function - reads the specified number of bytes.
	 * @param Integer $count The number of bytes to read.
	 * @return String
	 */
	public function stream_read($count)
	{
		$return = substr(self::$files[$this->file], $this->read, $count);
		$this->read += $count;
		return $return;
	} // end stream_read();

	/**
	 * PHP stream function - writes the data to a file (not supported)
	 * @param String $data The data to write.
	 */
	public function stream_write($data)
	{

	} // end stream_write();

	/**
	 * PHP stream function - returns the current cursor position.
	 * @return Integer
	 */
	public function stream_tell()
	{
		return $this->read;
	} // end stream_tell();

	/**
	 * PHP stream function - seek operation (not supported)
	 * @param Integer $offset
	 * @param Integer $whence
	 */
	public function stream_seek($offset, $whence)
	{

	} // end stream_seek();

	/**
	 * PHP stream function - reports some data about the current file.
	 * @return Array
	 */
	public function stream_stat()
	{
		if(!isset(self::$files[$this->file]))
		{
			return array();
		}
		return array('size' => strlen($this->file));
	} // end stream_stat();

	/**
	 * PHP stream function - reports some data about the current file.
	 * @return Array
	 */
	public function url_stat($path, $flags)
	{
		if(!isset(self::$files[$path]))
		{
			return false;
		}
		return array('size' => strlen($path));
	} // end url_stat();
} // end Extra_TestFS;

stream_register_wrapper('test', 'Extra_TestFS');