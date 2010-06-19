<?php
/**
 * Extra tester for Opt_Xml_Scannable
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

class Extra_Wrapper_ScannableTester extends Opt_Xml_Scannable
{
		/**
		 * Accept all the nodes
		 *
		 * @internal
		 * @param Opt_Xml_Node $node
		 */
		protected function _testNode(Opt_Xml_Node $node)
		{
			/* null */
		} // end _testNode();

		public function postLink(Opt_Compiler_Class $compiler)
		{
			/* null */
		} // end postLink();

		public function preLink(Opt_Compiler_Class $compiler)
		{
			/* null */
		} // end preLink();

		public function postProcess(Opt_Compiler_Class $compiler)
		{
			/* null */
		} // end postProcess();
		
		public function preProcess(Opt_Compiler_Class $compiler)
		{
			/* null */
		} // end preProcess();
} // end Extra_Wrapper_ScannableTester;