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
 * The class provides various utility functions to perform
 * some operations on OPT-XML tree and other elements.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 * @package Compiler
 */
class Opt_Compiler_Utils
{
	/**
	 * This utility function helps removing the CDATA state from the
	 * specified node and their descendants. If the extra attribute is
	 * set to false, the compiler does not replace to entities the special
	 * symbols. By default, they are entitized.
	 *
	 * @static
	 * @param Opt_Xml_Node $node The starting node.
	 * @param boolean $entitize Replace the special symbols to entities?
	 */
	static public function removeCdata(Opt_Xml_Node $node, $entitize = true)
	{
		// Do not use true recursion.
		$queue = new SplQueue;
		$queue->enqueue($node);
		do
		{
			$current = $queue->dequeue();

			if($current instanceof Opt_Xml_Cdata)
			{
				if($current->get('cdata') === true)
				{
					$current->set('cdata', false);
				}
				if(!$entitize)
				{
					$current->set('noEntitize', true);
				}
			}
			// Add the children of the node to the queue for furhter processing
			foreach($current as $subnode)
			{
				$queue->enqueue($subnode);
			}
		}
		while($queue->count() > 0);
	} // end removeCdata();

	/**
	 * This utility function helps removing the COMMENT state from the
	 * specified node and their descendants. If the extra attribute is
	 * set to false, the compiler does not replace to entities the special
	 * symbols. By default, they are entitized.
	 *
	 * @static
	 * @param Opt_Xml_Node $node The starting node.
	 * @param boolean $entitize Replace the special symbols to entities?
	 */
	static public function removeComments(Opt_Xml_Node $node, $entitize = true)
	{
		// Do not use true recursion.
		$queue = new SplQueue;
		$queue->enqueue($node);
		do
		{
			$current = $queue->dequeue();

			if($current instanceof Opt_Xml_Cdata)
			{
				if($current->get('commented') === true)
				{
					$current->set('commented', false);
				}
				if(!$entitize)
				{
					$current->set('noEntitize', true);
				}
			}
			// Add the children of the node to the queue for furhter processing
			foreach($current as $subnode)
			{
				$queue->enqueue($subnode);
			}
		}
		while($queue->count() > 0);
	} // end removeComments();
} // end Opt_Compiler_Utils;