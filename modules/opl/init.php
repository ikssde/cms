<?php
	/**
	 * @author Agares
	 */

	$currentPath = dirname(__FILE__);
	require_once __DIR__.'classes/Open-Power-Libs/lib/Opl/Base.php';
	// Opl_Loader::setHandleUnknownLibraries(false);
	$loader = new Opl_Loader('_');
	$loader -> addLibrary('Opl', __DIR__.'classes/Open-Power-Libs/lib/');
	$loader -> addLibrary('Opt', __DIR__.'classes/Open-Power-Template/lib/');
	$loader -> register();

	$opc = new Opc_Class;
	$opt = new Opt_Class;
	$opf = new Opf_Class;
	$opt -> sourceDir = APPPATH.'views';
	$opt -> compileDir = $currentPath.'/cache/';
	$opt -> contentType = Opt_Output_Http::XHTML;
	$opt -> charset = 'utf8';
	$opt -> advancedOOP = false;
	$opt -> basicOOP = false;
	$opt -> setInflector(new Our_Inflector);
	$opt -> register(Opt_Class::PHP_FUNCTION, '__', '__');
	$opt -> register
	(
		Opt_Class::PHP_FUNCTION,
		array
		(
			'HTML_anchor' => 'HTML::anchor',
			'HTML_fileAnchor' => 'HTML::file_anchor',
			'HTML_obfuscate' => 'HTML::obfuscate',
			'HTML_email' => 'HTML::email',
			'HTML_mailto' => 'HTML::mailto',
			'HTML_style' => 'HTML::style',
			'HTML_script' => 'HTML::script',
			'HTML_image' => 'HTML::image',
		)
	);

	$opt -> register
	(
		Opt_Class::PHP_FUNCTION,
		array
		(
			'URL_base' => 'URL::base',
			'URL_site' => 'URL::site',
			'URL_query' => 'URL::query',
			'URL_title' => 'URL::title',
		)
	);
	$opt -> setup();
	