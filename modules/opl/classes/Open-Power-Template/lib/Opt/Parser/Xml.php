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
 */

/**
 * This class uses XMLReader to generate the XML tree which is
 * later converted to OPT nodes.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Parsers
 */
class Opt_Parser_Xml implements Opt_Parser_Interface
{
	/**
	 * The expression finding regular expression.
	 * @var string
	 */
	private $_rExpressionTag = '/(\{([^\}]*)\})/msi';

	/**
	 * The compiler class
	 * @var Opt_Compiler_Class
	 */
	private $_compiler;

	/**
	 * Sets the compiler instance. Note that this compiler can be instantiated
	 * only if XMLReader PHP extension is installed.
	 *
	 * @throws Opl_Dependency_Exception
	 * @param Opt_Compiler_Class $compiler The compiler object
	 */
	public function setCompiler(Opt_Compiler_Class $compiler)
	{
		if(!extension_loaded('XMLReader'))
		{
			throw new Opl_Dependency_Exception('XMLReader extension is not loaded', Opl_Dependency_Exception::PHP);
		}
		$this->_compiler = $compiler;
	} // end setCompiler();

	/**
	 * The method should reset all the object references it possesses.
	 */
	public function dispose()
	{
		$this->_compiler = null;
	} // end dispose();

	/**
	 * Parses the input code and returns the OPT XML tree.
	 *
	 * @throws Opt_Parser_Exception
	 * @param string $filename The file name (for debug purposes)
	 * @param string &$code The code to parse
	 * @return Opt_Xml_Root
	 */
	public function parse($filename, &$code)
	{
		$debug = array(
			XMLReader::NONE => 'NONE',
			XMLReader::ELEMENT => 'ELEMENT',
			XMLReader::ATTRIBUTE => 'ATTRIBUTE',
			XMLReader::TEXT => 'TEXT',
			XMLReader::CDATA => 'CDATA',
			XMLReader::ENTITY_REF => 'ENTITY_REF',
			XMLReader::ENTITY => 'ENTITY',
			XMLReader::PI => 'PI',
			XMLReader::COMMENT => 'COMMENT',
			XMLReader::DOC => 'DOC',
			XMLReader::DOC_TYPE => 'DOC_TYPE',
			XMLReader::DOC_FRAGMENT => 'DOC_FRAGMENT',
			XMLReader::NOTATION => 'NOTATION',
			XMLReader::WHITESPACE => 'WHITESPACE',
			XMLReader::SIGNIFICANT_WHITESPACE => 'SIGNIFICANT_WHITESPACE',
			XMLReader::END_ELEMENT => 'END_ELEMENT',
			XMLReader::END_ENTITY => 'END_ENTITY',
			XMLReader::XML_DECLARATION => 'XML_DECLARATION'
		);

		libxml_use_internal_errors(true);

		$reader = new XMLReader;
		$reader->xml($code);
	//	$reader->setParserProperty(XMLReader::LOADDTD, true);
	//	$reader->setParserProperty(XMLReader::VALIDATE, true);
		$reader->setParserProperty(XMLReader::SUBST_ENTITIES, true);

		$root = $current = new Opt_Xml_Root;
		$firstElementMatched = false;
		$depth = 0;
		// Thanks, Oh Great PHP for your excellent WARNINGS!!! >:(
		while(@$reader->read())
		{
			if($reader->depth < $depth)
			{
				$current = $current->getParent();
			}
			elseif($reader->depth > $depth)
			{
				$current = $optNode;
			}
			switch($reader->nodeType)
			{
				// XML elements
				case XMLReader::ELEMENT:
					$optNode = new Opt_Xml_Element($reader->name);
					// Parse element attributes, if you manage to get there
					if($reader->moveToFirstAttribute())
					{
						do
						{
							// "xmlns" special namespace must be handler somehow differently.
							if($reader->prefix == 'xmlns')
							{
								$ns = str_replace('xmlns:', '', $reader->name);
								$root->addNamespace($ns, $reader->value);

								// Let this attribute to appear, if it does not represent an OPT special
								// namespace
								if(!$this->_compiler->isNamespace($ns))
								{
									$optAttribute = new Opt_Xml_Attribute($reader->name, $reader->value);
									$optNode->addAttribute($optAttribute);
								}
							}
							else
							{
								$optAttribute = new Opt_Xml_Attribute($reader->name, $reader->value);
								$optNode->addAttribute($optAttribute);
							}
						}
						while($reader->moveToNextAttribute());
						$reader->moveToElement();
					}
					// Set "rootNode" flag
					if(!$firstElementMatched)
					{
						$optNode->set('rootNode', true);
						$firstElementMatched = true;
					}
					// Set "single" flag
					if($reader->isEmptyElement)
					{
						$optNode->set('single', true);
					}
					$current->appendChild($optNode);

					break;
				case XMLReader::TEXT:
				case XMLReader::WHITESPACE:
				case XMLReader::SIGNIFICANT_WHITESPACE:
					$this->_treeTextCompile($current, $reader->value);
					break;
				case XMLReader::COMMENT:
					$optNode = new Opt_Xml_Comment($reader->value);
					$current->appendChild($optNode);
					break;
				case XMLReader::CDATA:
					$cdata = new Opt_Xml_Cdata($reader->value);
					$cdata->set('cdata', true);

					if($current instanceof Opt_Xml_Text)
					{
						$current->appendChild($cdata);
					}
					else
					{
						$text = new Opt_Xml_Text();
						$text->appendChild($cdata);
						$current->appendChild($text);
						$current = $text;
					}
					break;
			}
			$depth = $reader->depth;
		}
		// Error checking
		$errors = libxml_get_errors();
		if(sizeof($errors) > 0)
		{
			libxml_clear_errors();
			$msg = current($errors);

			throw new Opt_Parser_Exception(
				$msg->message,
				'XML',
				$filename,
				$msg->line
			);
		}

		return $root;
	} // end parse();

	/**
	 * Compiles the current text block between two XML tags, creating a
	 * complete Opt_Xml_Text node. It looks for the expressions in the
	 * curly brackets, extracts them and packs as separate nodes.
	 *
	 * Moreover, it replaces the entities with the corresponding characters.
	 *
	 * @internal
	 * @param Opt_Xml_Node $current The current XML node.
	 * @param string $text The text block between two tags.
	 * @param boolean $noExpressions=false If true, do not look for the expressions.
	 * @return Opt_Xml_Node The current XML node.
	 */
	protected function _treeTextCompile($current, $text, $noExpressions = false)
	{
		if($noExpressions)
		{
			$current = $this->_treeTextAppend($current, $text);
		}

		preg_match_all($this->_rExpressionTag, $text, $result, PREG_SET_ORDER);

		$resultSize = sizeof($result);
		$offset = 0;
		for($i = 0; $i < $resultSize; $i++)
		{
			$id = strpos($text, $result[$i][0], $offset);
			if($id > $offset)
			{
				$current = $this->_treeTextAppend($current, substr($text, $offset, $id - $offset));
			}
			$offset = $id + strlen($result[$i][0]);

			$current = $this->_treeTextAppend($current, new Opt_Xml_Expression($result[$i][2]));
		}

		$i--;
		// Now the remaining content of the file
		if(strlen($text) > $offset)
		{
			$current = $this->_treeTextAppend($current, substr($text, $offset, strlen($text) - $offset));
		}
		return $current;
	} // end _treeTextCompile();

	/**
	 * An utility method that simplifies inserting the text to the XML
	 * tree. Depending on the last child type, it can create a new text
	 * node or add the text to the existing one.
	 *
	 * @internal
	 * @param Opt_Xml_Node $current The currently built XML node.
	 * @param string|Opt_Xml_Node $text The text or the expression node.
	 * @return Opt_Xml_Node The current XML node.
	 */
	protected function _treeTextAppend($current, $text)
	{
		$last = $current->getLastChild();
		if(!is_object($last) || !($last instanceof Opt_Xml_Text))
		{
			if(!is_object($text))
			{
				$node = new Opt_Xml_Text($text);
			}
			else
			{
				$node = new Opt_Xml_Text();
				$node->appendChild($text);
			}
			$current->appendChild($node);
		}
		else
		{
			if(!is_object($text))
			{
				$last->appendData($text);
			}
			else
			{
				$last->appendChild($text);
			}
		}
		return $current;
	} // end _treeTextAppend();
} // end Opt_Parser_SimpleXml;