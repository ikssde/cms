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
 * The interface for writing caching systems for OPT.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Interfaces
 * @subpackage Public
 */
interface Opt_Caching_Interface
{
	/**
	 * The method executed during the template execution initialization.
	 * It is supposed to return false in order to regenerate the cache
	 * or the cached result.
	 *
	 * @param Opt_View $view The cached view
	 * @return boolean|string
	 */
	public function templateCacheStart(Opt_View $view);

	/**
	 * Executed during the cache rebuilding process. It must finish the
	 * capturing and store the result in a persistent storage.
	 *
	 * @param Opt_View $view The cached view
	 */
	public function templateCacheStop(Opt_View $view);
} // end Opt_Caching_Interface;

/**
 * The interface for writing output systems
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Interfaces
 * @subpackage Public
 */
interface Opt_Output_Interface
{
	/**
	 * Returns the output interface name
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Renders the specified view.
	 *
	 * @param Opt_View $view The view to render
	 */
	public function render(Opt_View $view);
} // end Opt_Output_Interface;

/**
 * The interface for writing inflectors.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Interfaces
 * @subpackage Public
 */
interface Opt_Inflector_Interface
{
	/**
	 * Returns the actual path to the source template suitable to use with
	 * the PHP filesystem functions.
	 *
	 * @param string $file The template file
	 * @return string
	 */
	public function getSourcePath($file);

	/**
	 * Returns the actual path to the compiled template suitable to use
	 * with the PHP filesystem functions.
	 *
	 * @param string $file The template file
	 * @param array $inheritance The dynamic template inheritance list
	 * @return string
	 */
	public function getCompiledPath($file, array $inheritance);
} // end Opt_Inflector_Interface;

/**
 * The main OPT class. It manages the configuration, initialization
 * and plugin loading issues. Usually, there is no need to create more
 * than one object of this class, and OPT assumes there is only one
 * object of Opt_Class at given time.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Public
 */
class Opt_Class extends Opl_Class
{
	// Constants
	const CM_DEFAULT = 0;
	const CM_REBUILD = 1;
	const CM_PERFORMANCE = 2;

	const CHOOSE_MODE = 'Opt_Parser_Html';
	const XML_MODE = 'Opt_Parser_Xml';
	const QUIRKS_MODE = 'Opt_Parser_Quirks';
	const HTML_MODE = 'Opt_Parser_Html';

	const OPT_INSTRUCTION = 1;
	const OPT_NAMESPACE = 2;
	const OPT_FORMAT = 3;
	const OPT_COMPONENT = 4;
	const OPT_BLOCK = 5;
	const PHP_FUNCTION = 6;
	const PHP_CLASS = 7;
	const XML_ENTITY = 8;
	const EXPR_ENGINE = 9;
	const MODIFIER = 10;

	const VERSION = '2.1-dev';
	const ERR_STANDARD = 6135; // E_ALL^E_NOTICE

	// Directory configuration
	public $sourceDir = NULL;
	public $compileDir = NULL;
	public $cacheDir = NULL;

	// Template configuration
	public $compileId = NULL;

	// Front-end configuration
	public $compileMode = self::CM_DEFAULT;
	public $charset = 'utf-8';
	public $contentType = 0;
	public $gzipCompression = true;
	public $headerBuffering = false;
	public $contentNegotiation = false;
	public $errorReporting = self::ERR_STANDARD;
	public $stdStream = 'file';
	public $debugConsole = false;
	public $allowRelativePaths = false;

	// Function configuration
	public $moneyFormat;
	public $numberDecimals;
	public $numberDecPoint;
	public $numberThousandSep;

	// Template language configuration
	public $parser = 'Opt_Parser_Xml';
	public $expressionEngine = 'parse';
	public $attributeModifier = 'a';
	public $defaultModifier = 'e';

	// Compiler configuration
	public $backwardCompatibility = false;
	public $unicodeNames = false;
	public $htmlAttributes = false;
	public $printComments = false;
	public $prologRequired = true;
	public $stripWhitespaces = true;
	public $singleRootNode = true;
	public $allowArrays = false;
	public $allowObjects = false;
	public $allowObjectCreation = false;
	public $backticks = null;
	public $translate = null;
	public $strictCallbacks = true;
	public $htmlEntities = true;
	public $escape = true;
	public $defaultFormat = 'Array';
	public $containerFormat = 'Container';

	/**
	 * The compiler object
	 * @var Opt_Compiler_Class
	 */
	protected $_compiler;
	/**
	 * The inflector object
	 * @var Opt_Inflector_Interface
	 */
	protected $_inflector;
	/**
	 * The translation interface
	 * @var Opl_Translation_Interface
	 */
	protected $_tf = NULL;

	/**
	 * The cache object
	 * @var Opt_Caching_Interface
	 */
	protected $_cache;

	/**
	 * The list of registered instruction processors.
	 * @var array
	 */
	protected $_instructions = array('Opt_Instruction_Section', 'Opt_Instruction_Tree',
		'Opt_Instruction_Grid', 'Opt_Instruction_Selector', 'Opt_Instruction_Repeat',
		'Opt_Instruction_Snippet', 'Opt_Instruction_Extend',
		'Opt_Instruction_For', 'Opt_Instruction_Foreach', 'Opt_Instruction_If',
		'Opt_Instruction_Put', 'Opt_Instruction_Capture', 'Opt_Instruction_Attribute',
		'Opt_Instruction_Tag', 'Opt_Instruction_Root', 'Opt_Instruction_Prolog',
		'Opt_Instruction_Dtd', 'Opt_Instruction_Literal', 'Opt_Instruction_Include',
		'Opt_Instruction_Dynamic', 'Opt_Instruction_Component', 'Opt_Instruction_Block',
		'Opt_Instruction_Switch', 'Opt_Instruction_Procedure'
	);

	/**
	 * The list of registered functions: assotiative parray of pairs:
	 * template function name => php function name
	 * @var array
	 */
	protected $_functions = array(
		'money' => 'Opt_Function::money', 'number' => 'Opt_Function::number', 'spacify' => 'Opt_Function::spacify',
		'firstof' => 'Opt_Function::firstof', 'indent' => 'Opt_Function::indent', 'strip' => 'Opt_Function::strip',
		'stripTags' => 'Opt_Function::stripTags', 'upper' => 'Opt_Function::upper', 'lower' => 'Opt_Function::lower',
		'capitalize' => 'Opt_Function::capitalize', 'countWords' => 'str_word_count', 'countChars' => 'strlen',
		'replace' => '#3,1,2#str_replace', 'repeat' => 'str_repeat', 'nl2br' => 'Opt_Function::nl2br', 'date' => 'date',
		'regexReplace' => '#3,1,2#preg_replace', 'truncate' => 'Opt_Function::truncate', 'wordWrap' => 'Opt_Function::wordwrap',
		'count' => 'sizeof', 'sum' => 'Opt_Function::sum', 'average' => 'Opt_Function::average',
		'absolute' => 'Opt_Function::absolute', 'stddev' => 'Opt_Function::stddev', 'range' => 'Opt_Function::range',
		'isUrl' => 'Opt_Function::isUrl', 'isImage' => 'Opt_Function::isImage', 'stddev' => 'Opt_Function::stddev',
		'entity' => 'Opt_Function::entity', 'scalar' => 'is_scalar', 'cycle' => 'Opt_Function::cycle',
		'containsKey' => 'Opt_Function::containsKey', 'autoLink' => 'Opt_Function::autoLink', 'pluralize' => 'Opt_Function::pluralize',
		'countSubstring' => 'Opt_Function::countSubstring', 'pad' => 'Opt_Function::pad', 'autoLink' => 'Opt_Function::autoLink',
		'position' => 'strpos'
	);
	/**
	 * The list of registered classes: assotiative array of pairs:
	 * template class name => php class name
	 * @var array
	 */
	protected $_classes = array();
	/**
	 * The list of registered components: assotiative array of
	 * pairs: XML tag => component class
	 * @var array
	 */
	protected $_components = array();
	/**
	 * The list of registered blocks: assotiative array of
	 * pairs: XML tag => component class
	 * @var array
	 */
	protected $_blocks = array();
	/**
	 * The list of recognized OPT namespaces
	 * @var array
	 */
	protected $_namespaces = array(1 => 'opt', 'com', 'parse');
	/**
	 * The list of recognized expression engines.
	 * @var array
	 */
	protected $_exprEngines = array(
		'parse' => 'Opt_Expression_Standard',
		'str' => 'Opt_Expression_String',
	);
	protected $_modifiers = array(
		'a' => 'htmlspecialchars',
		'e' => 'htmlspecialchars',
		'u' => null
	);
	/**
	 * The list of data formats: associative array of pairs:
	 * format name => format class
	 * @var array
	 */
	protected $_formats = array(
		'Array' => 'Opt_Format_Array',
		'Objective' => 'Opt_Format_Objective',
		'Global' => 'Opt_Format_Global',
		'SingleArray' => 'Opt_Format_SingleArray',
		'StaticGenerator' => 'Opt_Format_StaticGenerator',
		'RuntimeGenerator' => 'Opt_Format_RuntimeGenerator',
		'System' => 'Opt_Format_System',
		'SwitchEquals' => 'Opt_Format_SwitchEquals',
		'SwitchContains' => 'Opt_Format_SwitchContains',
		'Container' => 'Opt_Format_Container'
	);
	/**
	 * The extra entities replaced by OPT
	 * @var array
	 */
	protected $_entities = array('lb' => '{', 'rb' => '}');
	/**
	 * The output buffers for advisory output buffering information.
	 * @var array
	 */
	protected $_buffers = array();

	/**
	 * Was the library initialized?
	 * @var boolean
	 */
	protected $_init = false;

	/*
	 * Template parsing
	 */

	/**
	 * Returns the compiler object and optionally loads the necessary classes. Unless
	 * you develop instructions or reimplement various core features you do not have
	 * to use this method.
	 *
	 * @return Opt_Compiler_Class The compiler
	 */
	public function getCompiler()
	{
		if(!is_object($this->_compiler))
		{
			$this->_compiler = new Opt_Compiler_Class($this);
		}
		return $this->_compiler;
	} // end getCompiler();

	/*
	 * Extensions and configuration
	 */

	/**
	 * Performs the main initialization of OPT. If the optional argument `$config` is
	 * specified, it is transparently sent to Opt_Class::loadConfig(). Before using this
	 * method, we are obligated to configure the library and load the necessary extensions.
	 *
	 * @param mixed $config = null The optional configuration to be loaded
	 */
	public function setup($config = null)
	{
		if(is_array($config))
		{
			$this->loadConfig($config);
		}
		if($this->pluginDir !== null)
		{
			$this->loadPlugins();
		}

		if(Opl_Registry::exists('opl_translate'))
		{
			$this->setTranslationInterface(Opl_Registry::get('opl_translate'));
		}
		if(Opl_Registry::getValue('opl_debug_console') || $this->debugConsole)
		{
			$this->debugConsole = true;
			Opt_Support::initDebugConsole($this);
		}

		// Register a default inflector, if the programmer didn't set any.
		if(!$this->_inflector instanceof Opt_Inflector_Interface)
		{
			$this->_inflector = new Opt_Inflector_Standard($this);
		}
		$this->_securePath($this->compileDir);
		$this->_init = true;
		return true;
	} // end setup();

	/**
	 * Registers a new add-on in OPT identified by `$type`. The type is identified
	 * by the appropriate Opt_Class constant. The semantics of the next arguments
	 * depends on the registered add-on.
	 *
	 * Note that you may register several add-ons at the same time by passing an
	 * array as the second argument.
	 *
	 * @throws Opt_Exception
	 * @param int $type The type of registered item(s).
	 * @param mixed $item The item or a list of items to be registered
	 * @param mixed $addon = null Used in several types of add-ons
	 * @return void
	 */
	public function register($type, $item, $addon = null)
	{
		if($this->_init)
		{
			throw new Opt_Exception('Cannot register an item in OPT: the library has already been initialized.');
		}

		$map = array(1 => '_instructions', '_namespaces', '_formats', '_components', '_blocks', '_functions', '_classes', '_entities', '_exprEngines', '_modifiers');
		$whereto = $map[$type];
		// Massive registration
		if(is_array($item))
		{
			$this->$whereto = array_merge($this->$whereto, $item);
			return;
		}
		switch($type)
		{
			case self::OPT_FORMAT:
				if($addon === null)
				{
					$addon = 'Opt_Format_'.$item;
				}
				$a = &$this->$whereto;
				$a[$item] = $addon;
				break;
			case self::OPT_INSTRUCTION:
				if($addon === null)
				{
					$addon = 'Opt_Instruction_'.$item;
				}
				$a = &$this->$whereto;
				$a[$item] = $addon;
				break;
			case self::OPT_NAMESPACE:
				$a = &$this->$whereto;
				$a[] = $item;
				break;
			case self::MODIFIER:
				$a = &$this->$whereto;
				$a[$item] = $addon;
				break;
			default:
				if($addon === null)
				{
					throw new BadMethodCallException('Missing argument 3 for Opt_Class::register()');
				}
				$a = &$this->$whereto;
				$a[$item] = $addon;
		}
	} // end register();

	/**
	 * Registers a new translation interface to be used in templates. The translation
	 * interface must implement Opl_Translation_Interface. If the specified parameter
	 * is not a valid translation interface, the method unregisters the already set one
	 * and returns false.
	 *
	 * @param Opl_Translation_Interface $tf  The translation interface or "null".
	 * @return boolean True, if the translation interface was properly set.
	 */
	public function setTranslationInterface($tf)
	{
		if(!$tf instanceof Opl_Translation_Interface)
		{
			$this->_tf = null;
			return false;
		}
		$this->_tf = $tf;
		return true;
	} // end setTranslationInterface();

	/**
	 * Returns the current translation interface assigned to OPT.
	 *
	 * @return Opl_Translation_Interface The translation interface.
	 */
	public function getTranslationInterface()
	{
		return $this->_tf;
	} // end getTranslationInterface();

	/**
	 * Sets a new inflector for the OPT.
	 * @param Opt_Inflector_Interface $inflector The new inflector.
	 */
	public function setInflector(Opt_Inflector_Interface $inflector)
	{
		$this->_inflector = $inflector;
	} // end setInflector();

	/**
	 * Returns the current inflector. Note that before calling setup()
	 * this method may return NULL.
	 * @return Opt_Inflector_Interface
	 */
	public function getInflector()
	{
		return $this->_inflector;
	} // end getInflector();

	/**
	 * Sets the global caching system to use in all the views.
	 *
	 * @param Opt_Caching_Interface $cache=null The caching interface
	 */
	public function setCache(Opt_Caching_Interface $cache = null)
	{
		$this->_cache = $cache;
	} // end setCache();

	/**
	 * Returns the current global caching system.
	 *
	 * @return Opt_Caching_Interface
	 */
	public function getCache()
	{
		return $this->_cache;
	} // end getCache();

	/**
	 * An implementation of advisory output buffering which allows us
	 * to tell us, whether another part of the script opened the requested
	 * buffer.
	 *
	 * @param String $buffer The buffer name
	 * @param Boolean $state The new buffer state: true to open, false to close.
	 */
	public function setBufferState($buffer, $state)
	{
		if($state)
		{
			if(!isset($this->_buffers[$buffer]))
			{
				$this->_buffers[$buffer] = 1;
			}
			else
			{
				$this->_buffers[$buffer]++;
			}
		}
		else
		{
			if(isset($this->_buffers[$buffer]) && $this->_buffers[$buffer] > 0)
			{
				$this->_buffers[$buffer]--;
			}
		}
	} // end setBufferState();

	/**
	 * Returns the state of the specified output buffer.
	 *
	 * @param String $buffer Buffer name
	 * @return Boolean
	 */
	public function getBufferState($buffer)
	{
		if(!isset($this->_buffers[$buffer]))
		{
			return false;
		}
		return ($this->_buffers[$buffer] > 0);
	} // end getBufferState();

	/*
	 * Internal use
	 */

	/**
	 * Allows the read access to some of the internal structures for the
	 * template compiler.
	 *
	 * @internal
	 * @param string $name The structure to be returned.
	 * @return array The returned structure.
	 */
	public function _getList($name)
	{
		static $list;
		if($list === null)
		{
			$list = array('_instructions', '_namespaces', '_formats', '_components', '_blocks', '_functions', '_classes', '_tf', '_entities', '_exprEngines', '_modifiers');
		}
		if(in_array($name, $list))
		{
			return $this->$name;
		}
		return NULL;
	} // end _getList();

	/**
	 * The helper function for the plugin subsystem. It returns the
	 * PHP code that loads the specified plugin.
	 *
	 * @internal
	 * @param String $directory The plugin directory
	 * @param SplFileInfo $file The loaded file
	 * @return String
	 */
	protected function _pluginLoader($directory, SplFileInfo $file)
	{
		$ns = explode('.', $file->getFilename());
		if(end($ns) == 'php')
		{
			switch($ns[0])
			{
				case 'instruction':
					return 'Opl_Loader::mapAbsolute(\'Opt_Instruction_'.$ns[1].'\', \''.$directory.$file->getFilename().'\'); $this->register(Opt_Class::OPT_INSTRUCTION, \''.$ns[1].'\'); ';
				case 'format':
					return 'Opl_Loader::mapAbsolute(\'Opt_Format_'.$ns[1].'\', \''.$directory.$file->getFilename().'\'); $this->register(Opt_Class::OPT_FORMAT, \''.$ns[1].'\'); ';
				default:
					return ' require(\''.$directory.$file->getFilename().'\'); ';
			}
		}
	} // end _pluginLoader();

	/**
	 * Loads the template source code. Returns the template body or
	 * the array with two (false) values in case of problems.
	 *
	 * @internal
	 * @throws Opl_Filesystem_Exception
	 * @param string $filename The template filename
	 * @param boolean $exception Do we inform about the problems with exception?
	 * @return string|array
	 */
	public function _getSource($filename, $exception = true)
	{
		$item = $this->_inflector->getSourcePath($filename);
		if(!file_exists($item))
		{
			if(!$exception)
			{
				return array(false, false);
			}
			throw new Opl_Filesystem_Exception('The specified template: '.$item.' has not been found.');
		}
		return file_get_contents($item);
	} // end _getSource();

	/**
	 * The class constructor - registers the main object in the
	 * OPL registry.
	 */
	public function __construct()
	{
		Opl_Registry::set('opt', $this);
	} // end __construct();

	/**
	 * The destructor. Clears the output buffers and optionally
	 * displays the debug console.
	 */
	public function __destruct()
	{
		while(ob_get_level() > 0)
		{
			ob_end_flush();
		}
		if($this->debugConsole)
		{
			try
			{
				Opt_Support::updateTimers();
				Opl_Debug_Console::display();
			}
			catch(Opl_Exception $e)
			{
				die('<div style="background: #f77777;">Opt_Class destructor exception: '.$e->getMessage().'</div>');
			}
		}
	} // end __destruct();

	/**
	 * Frees the memory etc.
	 */
	public function dispose()
	{
		if($this->_compiler !== null)
		{
			$this->_compiler->dispose();
		}
		$this->_compiler = null;
		$this->_tf = null;
	} // end dispose();
} // end Opt_Class;

/**
 * The main view class.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Public
 */
class Opt_View
{
	const VAR_LOCAL = false;
	const VAR_GLOBAL = true;

	/**
	 * A reference to the main class object.
	 * @var Opt_Class
	 */
	private $_tpl;

	/**
	 * The template name
	 * @var string
	 */
	private $_template;

	/**
	 * Data format information storage
	 * @var array
	 */
	private $_formatInfo = array('system' => 'System', 'opt' => 'System');

	/**
	 * Template inheritance storage for the inflectors
	 * @var array
	 */
	private $_inheritance = array();

	/**
	 * View data
	 * @var array
	 */
	private $_data = array();

	/**
	 * Translation interface
	 * @var Opl_Translation_Interface
	 */
	private $_tf;

	/**
	 * The information for the debugger: processing time
	 * @var integer
	 */
	private $_processingTime = null;

	/**
	 * The branch name for the template inheritance.
	 * @var string
	 */
	private $_branch = null;

	/**
	 * The caching system used in the view.
	 * @var Opt_Caching_Interface
	 */
	private $_cache = null;

	/**
	 * The used parser.
	 * @var string
	 */
	private $_parser;
	/**
	 * Part of the caching system to integrate with opt:dynamic instruction.
	 * @var array
	 */
	private $_outputBuffer = array();

	/**
	 * The template variable storage
	 * @static
	 * @var array
	 */
	static private $_vars = array();

	/**
	 * The template procedures
	 * @static
	 * @var array
	 */
	static private $_procedures = array();

	/**
	 * The list of the captured content.
	 * @static
	 * @var array
	 */
	static private $_capture = array();

	/**
	 * The global template data
	 * @static
	 * @var array
	 */
	static private $_global = array();

	/**
	 * The global data format information
	 * @static
	 * @var array
	 */
	static private $_globalFormatInfo = array();

	/**
	 * Creates a new view object. The optional argument, $template
	 * may specify the template to be associated with this view.
	 * Please note that if you do not specify the template here,
	 * you have to do this manually later using Opt_View::setTemplate()
	 * method.
	 *
	 * @param string $template The template file.
	 */
	public function __construct($template = '')
	{
		$this->_tpl = Opl_Registry::get('opt');
		$this->_template = $template;
		$this->_parser = $this->_tpl->parser;
		$this->_cache = $this->_tpl->getCache();

		$this->_formatInfo['#container'] = $this->_tpl->containerFormat;
	} // end __construct();

	/**
	 * Associates a template file to the view.
	 *
	 * @param string $file The template file.
	 */
	public function setTemplate($file)
	{
		$this->_template = $file;
	} // end setTemplate();

	/**
	 * Returns a template associated with this view.
	 *
	 * @return string The template filename.
	 */
	public function getTemplate()
	{
		return $this->_template;
	} // end getTemplate();

	/**
	 * Sets the template mode (XML, Quirks, etc...)
	 *
	 * @deprecated
	 * @param int|string $mode The new mode
	 */
	public function setMode($mode)
	{
		$this->_parser = $mode;
	} // end setMode();

	/**
	 * Gets the current template mode.
	 *
	 * @deprecated
	 * @return int|string
	 */
	public function getMode()
	{
		return $this->_parser;
	} // end getMode();

	/**
	 * Sets the name of the parser used to compile the template.
	 *
	 * @param string $parser The parser name
	 */
	public function setParser($parser)
	{
		$this->_parser = (string)$parser;
	} // end setParser();

	/**
	 * Returns the name of the parsed used to compile the view template.
	 *
	 * @return string
	 */
	public function getParser()
	{
		return $this->_parser;
	} // end getParser();

	/**
	 * Sets a template inheritance branch that will be used
	 * in this view. If you want to disable branching, set
	 * the argument to NULL.
	 *
	 * @param string $branch The branch name.
	 */
	public function setBranch($branch)
	{
		$this->_branch = $branch;
	} // end setBranch();

	/**
	 * Returns a branch used in the template inheritance.
	 *
	 * @return string The branch name.
	 */
	public function getBranch()
	{
		return $this->_branch;
	} // end getBranch();

	/**
	 * Returns the view processing time for the debug purposes.
	 * The processing time is calculated only if the debug mode
	 * is enabled.
	 *
	 * @return float The processing time.
	 */
	public function getTime()
	{
		return $this->_processingTime;
	} // end getTime();

	/*
	 * Data management
	 */

	/**
	 * Creates a new local template variable.
	 *
	 * @param string $name The variable name.
	 * @param mixed $value The variable value.
	 */
	public function __set($name, $value)
	{
		$this->_data[(string)$name] = $value;
	} // end __set();

	/**
	 * Creates a new local template variable.
	 *
	 * @param string $name The variable name.
	 * @param mixed $value The variable value.
	 */
	public function assign($name, $value)
	{
		$this->_data[(string)$name] = $value;
	} // end assign();

	/**
	 * Creates a group of local template variables
	 * using an associative array, where the keys are
	 * the variable names.
	 *
	 * @param array $vars A list of variables.
	 */
	public function assignGroup($values)
	{
		$this->_data = array_merge($this->_data, $values);
	} // end assignGroup();

	/**
	 * Creates a new local template variable with
	 * the value assigned by reference.
	 *
	 * @param string $name The variable name.
	 * @param mixed &$value The variable value.
	 */
	public function assignRef($name, &$value)
	{
		$this->_data[(string)$name] = &$value;
	} // end assignRef();

	/**
	 * Returns the value of a template variable or
	 * null, if the variable does not exist.
	 *
	 * @param string $name The variable name.
	 * @return mixed The variable value or NULL.
	 */
	public function get($name)
	{
		if(!isset($this->_data[(string)$name]))
		{
			return null;
		}
		return $this->_data[(string)$name];
	} // end read();

	/**
	 * Returns the value of a local template variable or
	 * null, if the variable does not exist.
	 *
	 * @param string $name The variable name.
	 * @return mixed The variable value or NULL.
	 */
	public function &__get($name)
	{
		if(!isset($this->_data[(string)$name]))
		{
			// For returning by reference...
			$empty = null;
			return $empty;
		}
		return $this->_data[(string)$name];
	} // end __get();

	/**
	 * Returns TRUE, if the local template variable with the
	 * specified name is defined.
	 *
	 * @param string $name The variable name.
	 * @return boolean True, if the variable is defined.
	 */
	public function defined($name)
	{
		return isset($this->_data[(string)$name]);
	} // end defined();

	/**
	 * Returns TRUE, if the local template variable with the
	 * specified name is defined.
	 *
	 * @param string $name The variable name.
	 * @return boolean True, if the variable is defined.
	 */
	public function __isset($name)
	{
		return isset($this->_data[(string)$name]);
	} // end __isset();

	/**
	 * Removes a local template variable with the specified name.
	 *
	 * @param string $name The variable name.
	 * @return boolean True, if the variable has been removed.
	 */
	public function remove($name)
	{
		if(isset($this->_data[(string)$name]))
		{
			unset($this->_data[(string)$name]);
			if(isset($this->_formatInfo[(string)$name]))
			{
				unset($this->_formatInfo[(string)$name]);
			}
			return true;
		}
		return false;
	} // end remove();

	/**
	 * Removes a local template variable with the specified name.
	 *
	 * @param string $name The variable name.
	 * @return boolean True, if the variable has been removed.
	 */
	public function __unset($name)
	{
		return $this->remove((string)$name);
	} // end __unset();

	/**
	 * Creates a new global template variable.
	 *
	 * @static
	 * @param string $name The variable name.
	 * @param mixed $value The variable value.
	 */
	static public function assignGlobal($name, $value)
	{
		self::$_global[(string)$name] = $value;
	} // end assignGlobal();

	/**
	 * Creates a group of global template variables
	 * using an associative array, where the keys are
	 * the variable names.
	 *
	 * @static
	 * @param array $vars A list of variables.
	 */
	static public function assignGroupGlobal($values)
	{
		self::$_global = array_merge(self::$_global, $values);
	} // end assignGroupGlobal();

	/**
	 * Creates a new global template variable with
	 * the value assigned by reference.
	 *
	 * @static
	 * @param string $name The variable name.
	 * @param mixed &$value The variable value.
	 */
	static public function assignRefGlobal($name, &$value)
	{
		self::$_global[(string)$name] = &$value;
	} // end assignRefGlobal();

	/**
	 * Returns TRUE, if the global template variable with the
	 * specified name is defined.
	 *
	 * @static
	 * @param string $name The variable name.
	 * @return boolean True, if the variable is defined.
	 */
	static public function definedGlobal($name)
	{
		return isset(self::$_global[(string)$name]);
	} // end definedGlobal();

	/**
	 * Returns the value of a global template variable or
	 * null, if the variable does not exist.
	 *
	 * @static
	 * @param string $name The variable name.
	 * @return mixed The variable value or NULL.
	 */
	static public function getGlobal($name)
	{
		if(!isset(self::$_global[(string)$name]))
		{
			return null;
		}
		return self::$_global[(string)$name];
	} // end getGlobal();

	/**
	 * Removes a global template variable with the specified name.
	 *
	 * @static
	 * @param string $name The variable name.
	 * @return boolean True, if the variable has been removed.
	 */
	static public function removeGlobal($name)
	{
		if(isset(self::$_global[(string)$name]))
		{
			unset(self::$_global[(string)$name]);
			return true;
		}
		return false;
	} // end removeGlobal();

	/**
	 * Clears all the possible static private buffers.
	 */
	static public function clear()
	{
		self::$_vars = array();
		self::$_capture = array();
		self::$_global = array();
		self::$_globalFormatInfo = array();
	} // end clear();

	/**
	 * Returns the value of the internal template variable or
	 * NULL if it does not exist.
	 *
	 * @param string $name The internal variable name.
	 * @return mixed The variable value or NULL.
	 */
	public function getTemplateVar($name)
	{
		if(!isset(self::$_vars[(string)$name]))
		{
			return null;
		}
		return self::$_vars[(string)$name];
	} // end getTemplateVar();

	/**
	 * Sets the specified data format for the identifier that may
	 * identify a template variable or some other things. The details
	 * are explained in the OPT user manual.
	 *
	 * @param string $item The item name
	 * @param string $format The format to be used for the specified item.
	 */
	public function setFormat($item, $format)
	{
		$this->_formatInfo[(string)$item] = $format;
	} // end setFormat();

	/**
	 * Sets the specified data format for the identifier that may
	 * identify a global template variable or some other things. The details
	 * are explained in the OPT user manual.
	 *
	 * @static
	 * @param string $item The item name
	 * @param string $format The format to be used for the specified item.
	 * @param boolean $global Does it register the item in the "global." group?
	 */
	static public function setFormatGlobal($item, $format, $global = true)
	{
		if($global)
		{
			self::$_globalFormatInfo['global.'.$item] = $format;
		}
		else
		{
			self::$_globalFormatInfo[(string)$item] = $format;
		}
	} // end setFormatGlobal();

	/**
	 * Sets the caching interface that should be used with this view.
	 *
	 * @param Opt_Caching_Interface $iface The caching interface
	 */
	public function setCache(Opt_Caching_Interface $iface = null)
	{
		$this->_cache = $iface;
	} // end setCache();

	/**
	 * Returns the caching interface used with this view
	 *
	 * @return Opt_Caching_Interface
	 */
	public function getCache()
	{
		return $this->_cache;
	} // end getCache();

	/**
	 * A method for caching systems that tells, whether there is some
	 * dynamic content available in the captured part.
	 *
	 * @return Boolean
	 */
	public function hasDynamicContent()
	{
		return sizeof($this->_outputBuffer) > 0;
	} // end hasDynamicContent();

	/**
	 * Returns the static parts of the cached template, if the opt:dynamic
	 * is used. Please note that the returned array does not contain the
	 * last buffer, which must be closed and retrieved manually with
	 * ob_get_flush().
	 *
	 * @return Array
	 */
	public function getOutputBuffers()
	{
		return $this->_outputBuffer;
	} // end getBuffers();

	/*
	 * Dynamic inheritance
	 */

	/**
	 * Creates a dynamic template inheritance between the templates in the view.
	 * There are two possible uses of the method. If you specify only the one
	 * argument, the method will extend the main view template with the specified
	 * template.
	 *
	 * The two arguments can be used to extend other templates in the inheritance
	 * chain. In this case the first argument specifies the template that is going
	 * to extend something, and the second one - the extended template.
	 *
	 * @param string $source The extending template or the extended template in case of one-argument call.
	 * @param string $destination The extended template.
	 */
	public function inherit($source, $destination = null)
	{
		if($destination === null)
		{
			$this->_inheritance[$this->_template] = (string)$source;
		}
		else
		{
			$this->_inheritance[(string)$source] = (string)$destination;
		}
	} // end inherit();

	/*
	 * Internal use
	 */

	/**
	 * Executes, and optionally compiles the template represented by the view.
	 * Returns true, if the template was found and successfully executed.
	 *
	 * @param Opt_Output_Interface $output The output interface.
	 * @param Boolean $exception Should the exceptions be thrown if the template does not exist?
	 * @return Boolean
	 */
	public function _parse(Opt_Output_Interface $output, $exception = true)
	{
		if($this->_tpl->debugConsole)
		{
			$time = microtime(true);
		}

		$ctx = new Opt_InternalContext;
		$ctx->_data = &$this->_data;
		$ctx->_global = &self::$_global;
		$ctx->_vars = &self::$_vars;
		$ctx->_procs = &self::$_procedures;

		$cached = false;
		if($this->_cache !== null)
		{
			$result = $this->_cache->templateCacheStart($this);
			if($result !== false)
			{
				// For dynamic cache...
				if(is_string($result))
				{
					include($result);
				}
				return true;
			}
			$cached = true;
		}
		$this->_tf = $this->_tpl->getTranslationInterface();
		if($this->_tpl->compileMode != Opt_Class::CM_PERFORMANCE)
		{
			list($compileName, $compileTime) = $this->_preprocess($exception);
			if($compileName === null)
			{
				return false;
			}
		}
		else
		{
			$compileName = $this->_convert($this->_template);
			$compileTime = null;
			if(!$exception && !file_exists($compileName))
			{
				return false;
			}
		}

		$old = error_reporting($this->_tpl->errorReporting);
		require($this->_tpl->compileDir.$compileName);
		error_reporting($old);

		// The counter stops, if the time counting has been enabled for the debug console purposes
		if($this->_cache !== null)
		{
			$this->_cache->templateCacheStop($this);
		}
		if(isset($time))
		{
			Opt_Support::addView($this->_template, $output->getName(), $this->_processingTime = microtime(true) - $time, $cached);
		}
		return true;
	} // end _parse();

	/**
	 * The method checks whether the template exists and if it was modified by
	 * the template designer. In the second case, it loads and runs the template
	 * compiler to produce a new version. Returns an array with the template data:
	 *  - Compiled template name
	 *  - Compilation time
	 * They are needed by the template execution system or template inheritance. In
	 * case of problems, the array contains two NULL values.
	 *
	 * @internal
	 * @throws Opl_Filesystem_Exception
	 * @param boolean $exception Do we inform about unexisting template with exceptions?
	 * @return array
	 */
	protected function _preprocess($exception = true)
	{
		$inflector = $this->_tpl->getInflector();
		$item = $inflector->getSourcePath($this->_template);
		$compiled = $inflector->getCompiledPath($this->_template, $this->_inheritance);
		$compileTime = @filemtime($this->_tpl->compileDir.$compiled);
		$result = NULL;

		// Here the "rebuild" compilation mode is processed
		if($this->_tpl->compileMode == Opt_Class::CM_REBUILD)
		{
			if(!file_exists($item))
			{
				if(!$exception)
				{
					return array(NULL, NULL);
				}
				throw new Opl_Filesystem_Exception('The specified template: '.$item.' has not been found.');
			}
			$result = file_get_contents($item);
		}
		else
		{
			// Otherwise, we perform a modification test.
			$rootTime = @filemtime($item);
			if($rootTime === false)
			{
				if(!$exception)
				{
					return array(NULL, NULL);
				}
				throw new Opl_Filesystem_Exception('The specified template: '.$item.' has not been found.');
			}
			if($compileTime === false || $compileTime < $rootTime)
			{
				$result = file_get_contents($item);
			}
		}
		if($result === null)
		{
			return array($compiled, $compileTime);
		}

		$compiler = $this->_tpl->getCompiler();
		$compiler->setInheritance($this->_inheritance);
		$compiler->setFormatList(array_merge($this->_formatInfo, self::$_globalFormatInfo));
		$compiler->set('branch', $this->_branch);
		$compiler->compile($result, $this->_template, $compiled, $this->_parser);
		return array($compiled, $compileTime);
	} // end _preprocess();

	/**
	 * This method is used by the template with the template inheritance. It
	 * allows to check, whether one of the templates on the dependency list
	 * has been modified. The method takes the compilation time of the compiled
	 * template and the list of the source template names that it depends on.
	 *
	 * Returns true, if one if the templates is newer than the compilation time.
	 *
	 * @param int $compileTime Compiled template creation time.
	 * @param array $templates The list of dependencies
	 * @return boolean
	 */
	protected function _massPreprocess($compileTime, $templates)
	{
		if($this->_tpl->compileMode == Opt_Class::CM_DEFAULT)
		{
			$cnt = sizeof($templates);
			$inflector = $this->_tpl->getInflector();
			for($i = 0; $i < $cnt; $i++)
			{
				$templates[$i] = $inflector->getSourcePath($templates[$i]);
				$time = @filemtime($templates[$i]);
				if($time === null)
				{
					throw new Opl_Filesystem_Exception('The specified template: '.$templates[$i].' has not been found.');
				}
				if($time >= $compileTime)
				{
					return true;
				}
			}
		}
		// For CM_REBUILD we return false, too, because the compilation has already been done in _parse()
		return false;
	} // end _massPreprocess();

	/**
	 * Converts the source template file name to the compiled
	 * template file name.
	 *
	 * @internal
	 * @param String $filename The source file name
	 * @return String
	 */
	public function _convert($filename)
	{
		return $this->_tpl->getInflector()->getCompiledPath($filename, $this->_inheritance);
	} // end _convert();

	/**
	 * Compiles the specified template and returns the current
	 * time.
	 *
	 * @internal
	 * @param String $filename The file name.
	 * @return Integer
	 */
	public function _compile($filename)
	{
		$compiled = $this->_convert($filename);
		$compiler = $this->_tpl->getCompiler();
		$compiler->setInheritance($this->_inheritance);
		$compiler->setFormatList(array_merge($this->_formatInfo, self::$_globalFormatInfo));
		$compiler->set('branch', $this->_branch);
		$compiler->compile($this->_tpl->_getSource($filename), $filename, $compiled, $this->_parser);
		return time();
	} // end _compile();
} // end Opt_View;

/**
 * The internal execution context that allows the access to the template data
 * without breaking the view hermetization. The object of this class is not
 * accessible from the public code, but its properties are public, so that
 * they can be directly accessed by template procedures.
 * 
 * @internal
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Opt_InternalContext
{
	/**
	 * The script template data.
	 * @var array
	 */
	public $_data;

	/**
	 * The local template data.
	 * @var array
	 */
	public $_vars;

	/**
	 * The list of available procedures.
	 * @var array
	 */
	public $_procs;

	/**
	 * The global script template data.
	 * @var array
	 */
	public $_global;
} // end Opt_InternalContext;