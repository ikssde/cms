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
 * XML tree root node.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package XML
 */
class Opt_Xml_Root extends Opt_Xml_Scannable
{
	private $_prolog = NULL;
	private $_dtd = NULL;
	private $_namespaces = array();

	/**
	 * Constructs the root node.
	 */
	public function __construct()
	{
		parent::__construct();
	} // end __construct();

	/**
	 * Overwritten parent setter - the root node cannot have parents.
	 *
	 * @param Opt_Xml_Node $parent The new parent (ignored).
	 */
	public function setParent($parent)
	{
		/* null */
	} // end setParent();

	/**
	 * Sets the new document prolog.
	 * @param Opt_Xml_Prolog $prolog The new prolog.
	 */
	public function setProlog(Opt_Xml_Prolog $prolog)
	{
		$this->_prolog = $prolog;
	} // end setProlog();

	/**
	 * Sets the new DTD.
	 * @param Opt_Xml_Dtd $dtd The new DTD
	 */
	public function setDtd(Opt_Xml_Dtd $dtd)
	{
		$this->_dtd = $dtd;
	} // end setDtd();

	/**
	 * Returns true, if the document has a prolog.
	 * @return Boolean
	 */
	public function hasProlog()
	{
		return !is_null($this->_prolog);
	} // end hasProlog();

	/**
	 * Returns true, if the document has DTD.
	 * @return Boolean
	 */
	public function hasDtd()
	{
		return !is_null($this->_dtd);
	} // end hasDtd();

	/**
	 * Returns the existing document prolog
	 * @return Opt_Xml_Prolog
	 */
	public function getProlog()
	{
		return $this->_prolog;
	} // end getProlog();

	/**
	 * Returns the existing DTD.
	 * @return Opt_Xml_Dtd
	 */
	public function getDtd()
	{
		return $this->_dtd;
	} // end getDtd();

	/**
	 * Adds a new namespace to the list.
	 *
	 * @param String $prefix The namespace prefix
	 * @param String $uri The namespace URI
	 */
	public function addNamespace($prefix, $uri)
	{
		if(isset($this->_namespaces[$prefix]))
		{
			// TODO: Exception here!
			return false;
		}
		$this->_namespaces[$prefix] = $uri;
	} // end addNamespace();

	/**
	 * Returns the list of namespaces.
	 * @return Array
	 */
	public function getNamespaces()
	{
		return $this->_namespaces;
	} // end getNamespaces();

	/**
	 * Returns the URI of the specified namespace prefix.
	 * @param String $prefix The namespace prefix
	 * @return String
	 */
	public function lookupNamespaceURI($prefix)
	{

	} // end lookupNamespaceURI();

	/**
	 * Returns the prefix for the specified namespace URI.
	 * @param String $uri The namespace URI
	 * @return String
	 */
	public function lookupPrefix($uri)
	{

	} // end lookupPrefix();

	/**
	 * Imports the namespaces from another root node.
	 * @param Opt_Xml_Root $root The other root node.
	 */
	public function importNamespaces(Opt_Xml_Root $root)
	{
		$this->_namespaces = array_merge($this->_namespaces, $root->_namespaces);
	} // end importNamespaces();

	/**
	 * Tests, if the specified node can be a child of root.
	 * @param Opt_Xml_Node $node The node to test.
	 */
	protected function _testNode(Opt_Xml_Node $node)
	{
		if($node->getType() == 'Opt_Xml_Expression' && $node->getType() == 'Opt_Xml_Cdata')
		{
			throw new Opt_APIInvalidNodeType_Exception('Opt_Xml_Root', $node->getType());
		}
	} // end _testNode();

	/**
	 * This function is executed by the compiler before the second compilation stage.
	 */
	public function preMigrate(Opt_Compiler_Class $compiler)
	{
		$this->set('hidden', false);
		$compiler->setChildren($this);
	} // end preMigrate();

	/**
	 * This function is executed by the compiler before the second compilation stage,
	 * after processing the child nodes.
	 */
	public function postMigrate(Opt_Compiler_Class $compiler)
	{

	} // end postMigrate();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing.
	 */
	public function preProcess(Opt_Compiler_Class $compiler)
	{
		$this->set('hidden', false);
		$compiler->setChildren($this);
	} // end preProcess();

	/**
	 * This function is executed by the compiler during the second compilation stage,
	 * processing, after processing the child nodes.
	 */
	public function postProcess(Opt_Compiler_Class $compiler)
	{

	} // end postProcess();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking.
	 */
	public function preLink(Opt_Compiler_Class $compiler)
	{
		$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_BEFORE));

		// Display the prolog and DTD, if it was set in the node.
		// Such construct ensures us that they will appear in the
		// valid place in the output document.
		if($this->hasProlog())
		{
			$compiler->appendOutput(str_replace('<?xml', '<<?php echo \'?\'; ?>xml', $this->getProlog()->getProlog()))."\r\n";
		}
		if($this->hasDtd())
		{
			$compiler->appendOutput($this->getDtd()->getDoctype()."\r\n");
		}
		$compiler->setChildren($this);
	} // end preLink();

	/**
	 * This function is executed by the compiler during the third compilation stage,
	 * linking, after linking the child nodes.
	 */
	public function postLink(Opt_Compiler_Class $compiler)
	{
		$compiler->appendOutput($this->buildCode(Opt_Xml_Buffer::TAG_AFTER));
	} // end postLink();
} // end Opt_Xml_Root;
