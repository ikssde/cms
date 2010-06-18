<?php
	/**
	 * @author Agares
	 */

	$currentPath = dirname(__FILE__);
	require_once 'D:/xampp/htdocs/invenzzia/Open-Power-Libs/lib/Opl/Base.php';
	// Opl_Loader::setHandleUnknownLibraries(false);
	$loader = new Opl_Loader('_');
	$loader -> addLibrary('Opl', 'D:/xampp/htdocs/invenzzia/Open-Power-Libs/lib/');
	$loader -> addLibrary('Opt', 'D:/xampp/htdocs/invenzzia/Open-Power-Template/lib/');
	$loader -> addLibrary('Opc', 'D:/xampp/htdocs/invenzzia/Open-Power-Classes/lib/');
	$loader -> addLibrary('Opf', 'D:/xampp/htdocs/invenzzia/Open-Power-Forms/lib/');
	// Opl_Loader::addLibrary('Opm', array('basePath' => $currentPath.'/classes/'));
	// Opl_Loader::addLibrary('Opf', array('basePath' => $currentPath.'/classes/'));
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
	$opt -> setInflector(new Opl_Inflector);
//	$tpl -> register(Opt_Class::OPT_NAMESPACE, 'form');
//	$tpl -> register(Opt_Class::OPT_COMPONENT, 'form:input', 'Component_Input');
//	$tpl -> register(Opt_Class::OPT_COMPONENT, 'form:select', 'Component_Select');
//	$tpl -> register(Opt_Class::OPT_COMPONENT, 'form:textarea', 'Component_Textarea');
//	$tpl -> register(Opt_Class::OPT_COMPONENT, 'form:password', 'Component_Password');
	$opt -> register(Opt_Class::PHP_FUNCTION, '__', '__');
//	$tpl -> register
//	(
//		Opt_Class::PHP_FUNCTION,
//		array
//		(
//			'Form_open' => 'Form::open',
//			'Form_close' => 'Form::close',
//			'Form_input' => 'Form::input',
//			'Form_hidden' => 'Form::hidden',
//			'Form_password' => 'Form::password',
//			'Form_file' => 'Form::file',
//			'Form_checkbox' => 'Form::checkbox',
//			'Form_radio' => 'Form::radio',
//			'Form_textarea' => 'Form::textarea',
//			'Form_select' => 'Form::select',
//			'Form_submit' => 'Form::submit',
//			'Form_image' => 'Form::image',
//			'Form_button' => 'Form::button',
//			'Form_label' => 'Form::label',
//		)
//	);

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
	