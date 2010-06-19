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
 * This class implements the standard compiler from OPT 2.0.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Parsers
 */
class Opt_Parser_Html implements Opt_Parser_Interface
{
	private $_rCDataExpression = '/(\<\!\[CDATA\[|\]\]\>)/msi';
	private $_rCommentExpression = '/(\<\!\-\-|\-\-\>)/si';
	private $_rCommentSplitExpression = '/(\<\!\-\-(.*?)\-\-\>)/si';
	private $_rOpeningChar = '[a-zA-Z\:\_]';
	private $_rNameChar = '[a-zA-Z0-9\:\.\_\-]';
	private $_rNameExpression;
	private $_rXmlTagExpression;
	private $_rTagExpandExpression;
	private $_rQuirksTagExpression = '';
	private $_rExpressionTag = '/(\{([^\}]*)\})/msi';
	private $_rAttributeTokens = '/(?:[^\=\"\'\s]+|\=|\"|\'|\s)/x';
	private $_rPrologTokens = '/(?:[^\=\"\'\s]+|\=|\'|\"|\s)/x';
	private $_rModifiers = 'si';
	private $_rXmlHeader = '/(\<\?xml.+\?\>)/msi';
	private $_rProlog = '/\<\?xml(.+)\?\>|/msi';
	private $_rEncodingName = '/[A-Za-z]([A-Za-z0-9.\_]|\-)*/si';

	/**
	 * The compiler instance.
	 *
	 * @var Opt_Compiler_Class
	 */
	protected $_compiler;

	/**
	 * The main class instance.
	 *
	 * @var Opt_Class
	 */
	protected $_tpl;

	/**
	 * The compiler handles both Html and Quirks mode. This
	 * variable is used to switch between them.
	 *
	 * @var integer
	 */
	protected $_mode = 0;

	/**
	 * The compiled file name.
	 * @var string
	 */
	protected $_filename;

	/**
	 * Sets the compiler instance.
	 *
	 * @param Opt_Compiler_Class $compiler The compiler object
	 */
	public function setCompiler(Opt_Compiler_Class $compiler)
	{
		$this->_compiler = $compiler;
		$this->_tpl = Opl_Registry::get('opt');

		if($this->_tpl->unicodeNames)
		{
			// Register unicode name regular expressions
			$this->_rOpeningChar = '(\p{Lu}|\p{Ll}|\p{Ll}|\p{Lt}|\p{Lm}|\p{Nl}|\_|\:)';
			$this->_rNameChar = '(\p{Lu}|\p{Ll}|\p{Ll}|\p{Lt}|\p{Lm}|\p{Nl}|\p{Mc}|\p{Me}|\p{Mn}|\p{Lm}|\p{Nd}|\_|\:|\.|\-)';
			$this->_rModifiers = 'msiu';
		}

		// Register the rest of the expressions
		$this->_rNameExpression = '/('.$this->_rOpeningChar.'?'.$this->_rNameChar.'*)/'.$this->_rModifiers;
		$this->_rXmlTagExpression = '/(\<((\/)?('.$this->_rOpeningChar.'?'.$this->_rNameChar.'*)( [^\<\>]*)?(\/)?)\>)/'.$this->_rModifiers;
		$this->_rTagExpandExpression = '/^(\/)?('.$this->_rOpeningChar.'?'.$this->_rNameChar.'*)( [^\<\>]*)?(\/)?$/'.$this->_rModifiers;

		$this->_rQuirksTagExpression = '/(\<((\/)?(('.implode('|', $this->_tpl->_getList('_namespaces')).')\:'.$this->_rNameChar.'*)( [^\<\>]+)?(\/)?)\>)/'.$this->_rModifiers;
	} // end setCompiler();

	/**
	 * The method should reset all the object references it possesses.
	 */
	public function dispose()
	{
		$this->_compiler = null;
		$this->_tpl = null;
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
		$this->_filename = $filename;

		$current = $tree = new Opt_Xml_Root;
		$codeSize = strlen($code);
		$encoding = $this->_tpl->charset;

		// First we have to find the prolog and DTD. Then we will be able to parse tags.
		if($this->_mode == 0)
		{
			// Find and parse XML prolog
			$endProlog = 0;
			$endDoctype = 0;
			if(substr($code, 0, 5) == '<?xml')
			{
				$endProlog = strpos($code, '?>', 5);

				if($endProlog === false)
				{
					throw new Opt_Parser_Exception(
						'Error while parsing XML prolog: prolog ending is missing',
						'HTML',
						$filename
					);
				}
				$values = $this->_compileProlog(substr($code, 5, $endProlog - 5));
				$endProlog += 2;
				if(!$this->_tpl->prologRequired)
				{
					// The prolog must be displayed
					$tree->setProlog(new Opt_Xml_Prolog($values));
				}
			}
			// Skip white spaces
			for($i = $endProlog; $i < $codeSize; $i++)
			{
				if($code[$i] != ' ' && $code[$i] != '	' && $code[$i] != "\r" && $code[$i] != "\n")
				{
					break;
				}
			}
			// Try to find doctype at the new position.
			$possibleDoctype = substr($code, $i, 9);

			if($possibleDoctype == '<!doctype' || $possibleDoctype == '<!DOCTYPE')
			{
				// OK, we've found it, now determine the doctype end.
				$bracketCounter = 0;
				$doctypeStart = $i;
				for($i += 9; $i < $codeSize; $i++)
				{
					if($code[$i] == '<')
					{
						$bracketCounter++;
					}
					else if($code[$i] == '>')
					{
						if($bracketCounter == 0)
						{
							$endDoctype = $i;
							break;
						}
						$bracketCounter--;
					}
				}
				if($endDoctype == 0)
				{
					throw new Opt_Parser_Exception(
						'Error while parsing XML doctype: doctype ending is missing',
						'HTML',
						$filename
					);
				}

				if(!$this->_tpl->prologRequired)
				{
					$tree->setDtd(new Opt_Xml_Dtd(substr($code, $doctypeStart, $i - $doctypeStart + 1)));
				}
				$endDoctype++;
			}
			else
			{
				$endDoctype = $endProlog;
			}
			// OK, now skip that part.
			$code = substr($code, $endDoctype, $codeSize);
			// In the quirks mode, some results from the regular expression parser are
			// moved by one position, so we must add some dynamics here.
			$attributeCell = 5;
			$endingSlashCell = 6;
			$tagExpression = $this->_rXmlTagExpression;
		}
		else
		{
			$tagExpression = $this->_rQuirksTagExpression;
			$attributeCell = 6;
			$endingSlashCell = 7;
		}

		// Split through the general groups (cdata-content)
		$groups = preg_split($this->_rCDataExpression, $code, 0, PREG_SPLIT_DELIM_CAPTURE);
		$groupCnt = sizeof($groups);
		$groupState = 0;
		Opt_Xml_Cdata::$mode = $this->_mode;
		for($k = 0; $k < $groupCnt; $k++)
		{
			// Process CDATA
			if($groupState == 0 && $groups[$k] == '<![CDATA[')
			{
				$cdata = new Opt_Xml_Cdata('');
				$cdata->set('cdata', true);
				$groupState = 1;
				continue;
			}
			if($groupState == 1)
			{
				if($groups[$k] == ']]>')
				{
					$current = $this->_treeTextAppend($current, $cdata);
					$groupState = 0;
				}
				else
				{
					$cdata->appendData($groups[$k]);
				}
				continue;
			}
			$subgroups = preg_split($this->_rCommentExpression, $groups[$k], 0, PREG_SPLIT_DELIM_CAPTURE);
			$subgroupCnt = sizeof($subgroups);
			$subgroupState = 0;
			for($i = 0; $i < $subgroupCnt; $i++)
			{
				// Process comments
				if($subgroupState == 0 && $subgroups[$i] == '<!--')
				{
					$commentNode = new Opt_Xml_Comment();
					$subgroupState = 1;
					continue;
				}
				if($subgroupState == 1)
				{
					if($subgroups[$i] == '-->')
					{
						$current->appendChild($commentNode);
						$subgroupState = 0;
					}
					else
					{
						$commentNode->appendData($subgroups[$i]);
					}
					continue;
				}
				elseif($subgroups[$i] == '-->')
				{
					throw new Opt_Parser_Exception(
						'XML Error: the static text "--&gt;" contains raw special XML characters.',
						'HTML',
						$filename
					);
				}
				// Find XML tags
				preg_match_all($tagExpression, $subgroups[$i], $result, PREG_SET_ORDER);
				/*
				 * Output field description for $result array:
				 *  0 - original content
				 *  1 - tag content (without delimiters)
				 *  2 - /, if enclosing tag
				 *  3 - name
				 *  4 - arguments (5 in quirks mode)
				 *  5 - /, if enclosing tag without subcontent (6 in quirks mode)
				 */

				$resultSize = sizeof($result);
				$offset = 0;
				for($j = 0; $j < $resultSize; $j++)
				{
					// Copy the remaining text to the text node
					$id = strpos($subgroups[$i], $result[$j][0], $offset);
					if($id > $offset)
					{
						$current = $this->_treeTextCompile($current, substr($subgroups[$i], $offset, $id - $offset));
					}
					$offset = $id + strlen($result[$j][0]);
					if(!isset($result[$j][$endingSlashCell]))
					{
						$result[$j][$endingSlashCell] = '';
					}
					// Process the argument list
					$attributes = array();
					if(!empty($result[$j][$attributeCell]))
					{
						// Just for sure...
						$result[$j][$attributeCell] = trim($result[$j][$attributeCell]);
						$oldLength = strlen($result[$j][$attributeCell]);
						$result[$j][$attributeCell] = rtrim($result[$j][$attributeCell], '/');
						if(strlen($result[$j][$attributeCell]) != $oldLength)
						{
							$result[$j][$endingSlashCell] = '/';
						}
						$attributes = $this->_compileAttributes($result[$j][$attributeCell]);
						if(!is_array($attributes))
						{
							throw new Opt_Parser_Exception(
								'XML Error: incorrect attribute format in tag: '.$result[$j][0],
								'HTML',
								$filename
							);
						}
					}
					// Recognize the tag type
					if($result[$j][3] != '/')
					{
						// Opening tag
						$node = new Opt_Xml_Element($result[$j][4]);
						$node->set('single', $result[$j][$endingSlashCell] == '/');
						foreach($attributes as $name => $value)
						{
							$node->addAttribute($anode = new Opt_Xml_Attribute($name, $value));
						}
						$current = $this->_treeNodeAppend($current, $node, $result[$j][$endingSlashCell] != '/');
					}
					elseif($result[$j][3] == '/')
					{
						if(sizeof($attributes) > 0)
						{
							throw new Opt_Parser_Exception(
								'XML Error: the following tag has an invalid structure: '.$result[$j][0],
								'HTML',
								$filename
							);
						}
						if($current instanceof Opt_Xml_Element)
						{
							if($current->getXmlName() != $result[$j][4])
							{
								throw new Opt_Parser_Exception(
									'XML Error: the following tag has been closed in the incorrect order: '.$result[$j][4].'; expected: '.$current->getXmlName().'.',
									'HTML',
									$filename
								);
							}
						}
						else
						{
							throw new Opt_Parser_Exception(
								'XML Error: the following tag has been closed in the incorrect order: '.$result[$j][4].'; expected: NULL.',
								'HTML',
								$filename
							);
						}
						$current = $this->_treeJumpOut($current);
					}
					else
					{
						throw new Opt_Parser_Exception(
							'XML Error: the following tag has an invalid structure: '.$result[$j][0],
							'HTML',
							$filename
						);
					}
				}
				if(strlen($subgroups[$i]) > $offset)
				{
					$current = $this->_treeTextCompile($current, substr($subgroups[$i], $offset, strlen($subgroups[$i]) - $offset));
				}
			}
		}
		// Testing if everything was closed.
		if($current !== $tree)
		{
			// Error handling - determine the name of the unclosed tag.
			while(! $current instanceof Opt_Xml_Element)
			{
				$current = $current->getParent();
			}
			throw new Opt_Parser_Exception('XML error: unclosed tag: '.$current->getXmlName(), 'HTML', $filename);
		}

		if($this->_mode == 0 && $this->_tpl->singleRootNode)
		{
			// TODO: The current code does not check the contents of Opt_Text_Nodes and other root elements
			// that may contain invalid and valid XML syntax at the same time.
			// For now, this code is frozen, we'll think a bit about it in the future. Maybe nobody
			// will notice this :)
			$elementFound = false;
			foreach($tree as $item)
			{
				if($item instanceof Opt_Xml_Element)
				{
					if($elementFound)
					{
						// Oops, there is already another root node!
						throw new Opt_Parser_Exception(
							'XML Error: too many root elements in the template: '.$item->getXmlName(),
							'HTML',
							$filename
						);
					}
					$elementFound = true;
				}
			}
		}
		return $tree;
	} // end parse();

	/**
	 * Compiles the attribute part of the opening tag and extracts the tag
	 * attributes to an array. Moreover, it performs the entity conversion
	 * to the corresponding characters.
	 *
	 * @internal
	 * @throws Opt_Parser_Exception
	 * @param string $attrList The attribute list string
	 * @return array The list of attributes with the values.
	 */
	protected function _compileAttributes($attrList)
	{
		// Tokenize the list
		preg_match_all($this->_rAttributeTokens, $attrList, $match, PREG_SET_ORDER);

		$size = sizeof($match);
		$result = array();
		for($i = 0; $i < $size; $i++)
		{
			/**
			 * The algorithm scans the tokens on the list and determines, where
			 * the beginning and the end of the attribute is. We do not use the
			 * regular expressions, because they are not able to capture the
			 * invalid content between the expressions.
			 *
			 * The sub-loops can modify the iteration variable to skip the found
			 * elements, white characters etc. This means that the main loop
			 * does only a few iteration number, equal approximately the number
			 * of attributes.
			 */
			if(!ctype_space($match[$i][0]))
			{

				if(!preg_match($this->_rNameExpression, $match[$i][0]))
				{
					return false;
				}

				$vret = false;
				$name = $match[$i][0];
				$value = null;
				for($i++; ctype_space($match[$i][0]) && $i < $size; $i++){}

				if($match[$i][0] != '=')
				{
					if($this->_tpl->htmlAttributes)
					{
						$result[$name] = $name;
						continue;
					}
					else
					{
						return false;
					}
				}
				// Look for the attribute value start
				for($i++; ctype_space($match[$i][0]) && $i < $size; $i++){}

				if($match[$i][0] != '"' && $match[$i][0] != '\'')
				{
					return false;
				}

				// Save the delimiter, because we will use it to make the error checking
				$delimiter = $match[$i][0];

				$value = '';
				for($i++; $i < $size; $i++)
				{
					if($match[$i][0] == $delimiter)
					{
						break;
					}
					$value .= $match[$i][0];
				}
				if(!isset($match[$i][0]))
				{
					return false;
				}
				if($match[$i][0] != $delimiter)
				{
					return false;
				}
				// We return the decoded attribute values, because they are
				// stored without the entities.
				if(isset($result[$name]))
				{
					throw new Opt_Parser_Exception(
						'XML Error: duplicated attribute '.$name.' in '.$tagName.'.',
						'HTML',
						$this->_filename
					);
				}
				$result[$name] = htmlspecialchars_decode($value);
			}
		}
		return $result;
	} // end _compileAttributes();

	/**
	 * Parses the XML prolog and returns its attributes as an array. The parsing
	 * algorith is the same, as in _compileAttributes().
	 *
	 * @internal
	 * @param string $prolog The prolog string.
	 * @return array
	 */
	protected function _compileProlog($prolog)
	{
		// Tokenize the list
		preg_match_all($this->_rPrologTokens, $prolog, $match, PREG_SET_ORDER);

		$size = sizeof($match);
		$result = array();
		for($i = 0; $i < $size; $i++)
		{
			if(!ctype_space($match[$i][0]))
			{
				// Traverse through a single attribute
				if(!preg_match($this->_rNameExpression, $match[$i][0]))
				{
					throw new Opt_Parser_Exception(
						'Error while parsing XML prolog: invalid attribute format.',
						'HTML',
						$this->_filename
					);
				}

				$vret = false;
				$name = $match[$i][0];
				$value = null;
				for($i++; $i < $size && ctype_space($match[$i][0]); $i++){}

				if($i >= $size || $match[$i][0] != '=')
				{
					throw new Opt_Parser_Exception(
						'Error while parsing XML prolog: invalid attribute format.',
						'HTML',
						$this->_filename
					);
				}
				for($i++; ctype_space($match[$i][0]) && $i < $size; $i++){}

				if($match[$i][0] != '"' && $match[$i][0] != '\'')
				{
					throw new Opt_Parser_Exception(
						'Error while parsing XML prolog: invalid attribute format.',
						'HTML',
						$this->_filename
					);
				}
				$opening = $match[$i][0];
				$value = '';
				for($i++; $i < $size; $i++)
				{
					if($match[$i][0] == $opening)
					{
						break;
					}
					$value .= $match[$i][0];
				}
				if(!isset($match[$i][0]) || $match[$i][0] != $opening)
				{
					throw new Opt_Parser_Exception(
						'Error while parsing XML prolog: invalid attribute format.',
						'HTML',
						$this->_filename
					);
				}
				// If we are here, the attribute is correct. No shit on the way detected.
				$result[$name] = $value;
			}
		}
		$returnedResult = $result;
		// Check, whether the arguments are correct.
		if(isset($result['version']))
		{
			// There is no other version so far, so report a warning. For 99,9% this is a mistake.
			if($result['version'] != '1.0')
			{
				$this->_tpl->debugConsole and Opt_Support::warning('OPT', 'XML prolog warning: strange XML version: '.$result['version']);
			}
			unset($result['version']);
		}
		if(isset($result['encoding']))
		{
			if(!preg_match($this->_rEncodingName, $result['encoding']))
			{
				throw new Opt_Parser_Exception(
					'Error while parsing XML prolog: invalid encoding name format.',
					'HTML',
					$this->_filename
				);
			}
			// The encoding should match the value we mentioned in the OPT configuration and sent to the browser.
			$result['encoding'] = strtolower($result['encoding']);
			$charset = is_null($this->_tpl->charset) ? null : strtolower($this->_tpl->charset);
			if($result['encoding'] != $charset && !is_null($charset))
			{
				$this->_tpl->debugConsole and Opt_Support::warning('OPT', 'XML prolog warning: the declared encoding: "'.$result['encoding'].'" differs from setContentType() setting: "'.$charset.'"');
			}
			unset($result['encoding']);
		}
		else
		{
			$this->_tpl->debugConsole and Opt_Support::warning('XML prolog warning: no encoding information. Remember your content must be pure UTF-8 or UTF-16 then.');
		}
		if(isset($result['standalone']))
		{
			if($result['standalone'] != 'yes' && $result['standalone'] != 'no')
			{
				throw new Opt_Parser_Exception(
					'Error while parsing XML prolog: invalid value for "standalone" attribute: "'.$result['standalone'].'"; expected: "yes", "no".',
					'HTML',
					$this->_filename
				);
			}
			unset($result['standalone']);
		}
		if(sizeof($result) > 0)
		{
			throw new Opt_Parser_Exception(
				'Error while parsing XML prolog: invalid attributes in the prolog.',
				'HTML',
				$this->_filename
			);
		}
		return $returnedResult;
	} // end _compileProlog();

	/**
	 * Compiles the current text block between two XML tags, creating a
	 * complete Opt_Xml_Text node. It looks for the expressions in the
	 * curly brackets, extracts them and packs as separate nodes.
	 *
	 * Moreover, it replaces the entities with the corresponding characters.
	 *
	 * @internal
	 * @throws Opt_Parser_Exception
	 * @param Opt_Xml_Node $current The current XML node.
	 * @param string $text The text block between two tags.
	 * @param boolean $noExpressions=false If true, do not look for the expressions.
	 * @return Opt_Xml_Node The current XML node.
	 */
	protected function _treeTextCompile($current, $text, $noExpressions = false)
	{
		// Yes, we parse entities, but the text itself should not contain
		// any special characters.
		if(strcspn($text, '<>') != strlen($text))
		{
			throw new Opt_Parser_Exception(
				'XML Error: the static text "'.$text.'" contains raw special XML characters.',
				'HTML',
				$this->_filename
			);
		}

		if($noExpressions)
		{
			$current = $this->_treeTextAppend($current, $this->_compiler->parseEntities($text));
		}

		preg_match_all($this->_rExpressionTag, $text, $result, PREG_SET_ORDER);

		$resultSize = sizeof($result);
		$offset = 0;
		for($i = 0; $i < $resultSize; $i++)
		{
			$id = strpos($text, $result[$i][0], $offset);
			if($id > $offset)
			{
				$current = $this->_treeTextAppend($current, $this->_compiler->parseEntities(substr($text, $offset, $id - $offset)));
			}
			$offset = $id + strlen($result[$i][0]);

			$current = $this->_treeTextAppend($current, new Opt_Xml_Expression($this->_compiler->parseEntities($result[$i][2])));
		}

		$i--;
		// Now the remaining content of the file
		if(strlen($text) > $offset)
		{
			$current = $this->_treeTextAppend($current, $this->_compiler->parseEntities(substr($text, $offset, strlen($text) - $offset)));
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

	/**
	 * A helper method for building the XML tree. It appends the
	 * node to the current node and returns the new node that should
	 * become the new current node.
	 *
	 * @internal
	 * @param Opt_Xml_Node $current The current node.
	 * @param Opt_Xml_Node $node The newly created node.
	 * @param boolean $goInto Whether we visit the new node.
	 * @return Opt_Xml_Node
	 */
	protected function _treeNodeAppend($current, $node, $goInto)
	{
		$current->appendChild($node);
		if($goInto)
		{
			return $node;
		}
		return $current;
	} // end _treeNodeAppend();

	/**
	 * A helper method for building the XML tree. It jumps out of the
	 * current node to the parent and switches to it.
	 *
	 * @internal
	 * @param Opt_Xml_Node $current The current node.
	 * @return Opt_Xml_Node
	 */
	protected function _treeJumpOut($current)
	{
		$parent = $current->getParent();

		if($parent !== null)
		{
			return $parent;
		}
		return $current;
	} // end _treeJumpOut();
} // end Opt_Parser_Html;
