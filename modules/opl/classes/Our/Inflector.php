<?php
	/**
	 * @author Agares
	 */

	class Our_Inflector implements Opt_Inflector_Interface
	{
		protected $_tpl = null;

		public function __construct()
		{
			$this->_tpl = Opl_Registry::get('opt');
		}

		/**
		 * Returns the real (filesystem) path to the specified
		 * template file. The path may be either relative or
		 * absolute.
		 *
		 * @throws Opt_Inflector_Exception
		 * @param string $file The file name
		 * @return string
		 */
		public function getSourcePath($name)
		{
			if(($path = Kohana::find_file('views', $name, 'tpl')))
				return $path;
			throw new Agares_Exception('View ' . $name . ' not found!');
		} // end getSourcePath();

		/**
		 * Returns the real (filesystem) and obfuscated path
		 * to the compiled version of the specified file.
		 *
		 * @param string $file The file name
		 * @param array $inheritance The list of templates used in the inheritance
		 * @return string
		 */
		public function getCompiledPath($file, array $inheritance)
		{
			if(sizeof($inheritance) > 0)
			{
				$list = $inheritance;
				sort($list);
			}
			else
			{
				$list = array();
			}
			$path = ($this->_tpl->compileId !== null ? $this->_tpl->compileId.'_' : '');
			foreach($list as $item)
			{
				$path .= strtr($item, '/:\\', '___').'/';
			}
			return $path.strtr((string)$file, '/:\\', '___').'.php';
		} // end getCompiledPath();
	}
