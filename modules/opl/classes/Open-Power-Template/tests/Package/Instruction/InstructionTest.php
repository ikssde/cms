<?php
/**
 * The tests for instructions.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */

require_once('./Extra/TestFS.php');
require_once('./Extra/TestFSBase.php');

/**
 * @covers Opt_Compiler_Class
 * @covers Opt_Compiler_Format
 * @covers Opt_Compiler_Processor
 * @covers Opt_Instruction_Attribute
 * @covers Opt_Instruction_BaseSection
 * @covers Opt_Instruction_Block
 * @covers Opt_Instruction_Capture
 * @covers Opt_Instruction_Component
 * @covers Opt_Instruction_Dtd
 * @covers Opt_Instruction_Dynamic
 * @covers Opt_Instruction_Extend
 * @covers Opt_Instruction_For
 * @covers Opt_Instruction_Foreach
 * @covers Opt_Instruction_Grid
 * @covers Opt_Instruction_If
 * @covers Opt_Instruction_Include
 * @covers Opt_Instruction_Literal
 * @covers Opt_Instruction_Loop
 * @covers Opt_Instruction_Prolog
 * @covers Opt_Instruction_Put
 * @covers Opt_Instruction_Repeat
 * @covers Opt_Instruction_Root
 * @covers Opt_Instruction_Section
 * @covers Opt_Instruction_Selector
 * @covers Opt_Instruction_Snippet
 * @covers Opt_Instruction_Tag
 * @covers Opt_Instruction_Tree
 * @runTestsInSeparateProcesses
 */
class Package_Instruction_InstructionTest extends Extra_TestFSBase
{

	/**
	 * Configuration method.
	 * @param Opt_Class $tpl 
	 */
	public function configure(Opt_Class $tpl)
	{
		$tpl->parser = 'Opt_Parser_Xml';
		$tpl->register(Opt_Class::OPT_COMPONENT, 'opt:myComponent', 'Extra_Mock_Component');
		$tpl->register(Opt_Class::OPT_BLOCK, 'opt:myBlock', 'Extra_Mock_Block');
	} // end configure();

	/**
	 * Provides the list of test cases.
	 * @return Array
	 */
	public static function dataProvider()
	{
		return array(0 =>
			// BLOCK
			array('Block/block_basic.txt'),
			array('Block/block_single.txt'),
			array('Block/block_arguments.txt'),
			array('Block/block_single_args.txt'),
			array('Block/block_static.txt'),
			array('Block/block_empty.txt'),
			array('Block/block_nesting.txt'),

			// COMPONENT
			array('Component/component_basic.txt'),
			array('Component/component_datasource.txt'),
			array('Component/component_display.txt'),
			array('Component/component_display_attr.txt'),
			array('Component/component_events.txt'),
			array('Component/component_management.txt'),
			array('Component/component_multiple.txt'),
			array('Component/component_defined.txt'),
			array('Component/component_param_empty.txt'),
			array('Component/component_skipping_opt.txt'),
			array('Component/component_inject_procedure.txt'),
			array('Component/component_inject_snippet.txt'),
			array('Component/component_nesting.txt'),

			// CONTENT
			array('Content/content_basic.txt'),
			array('Content/content_complex.txt'),
			array('Content/content_prepend.txt'),
			array('Content/content_append.txt'),
			array('Content/content_display.txt'),
			array('Content/content_all.txt'),

			// DTD
			array('Dtd/dtd_custom.txt'),
			array('Dtd/dtd_multiple.txt'),
			array('Dtd/dtd_unknown.txt'),
			array('Dtd/dtd_xhtml10transitional.txt'),
			array('Dtd/dtd_xhtml10strict.txt'),
			array('Dtd/dtd_xhtml10frameset.txt'),
			array('Dtd/dtd_xhtml11.txt'),
			array('Dtd/dtd_html40.txt'),
			array('Dtd/dtd_html5.txt'),

			// IF
			array('If/if_basic.txt'),
			array('If/if_basic_failure.txt'),
			array('If/if_basic_else.txt'),
			array('If/if_basic_else_failure.txt'),
			array('If/if_basic_elseif_first.txt'),
			array('If/if_basic_elseif.txt'),
			array('If/if_basic_elseif_failure.txt'),
			array('If/if_basic_sorting.txt'),
			array('If/if_basic_sorting_2.txt'),
			array('If/if_basic_sorting_3.txt'),
			array('If/if_new_one_condition.txt'),
			array('If/if_new_more_conditions.txt'),
			array('If/if_new_no_matching.txt'),
			array('If/if_new_else.txt'),
			array('If/if_new_long.txt'),
			array('If/if_new_long_else.txt'),
			array('If/if_new_attribute.txt'),
			array('If/if_new_attribute_nocondition.txt'),
			array('If/if_new_condition_without_if.txt'),
			array('OmitTag/omit_tag_basic.txt'),

			// PROLOG
			array('Prolog/prolog_default.txt'),
			array('Prolog/prolog_in_content.txt'),
			array('Prolog/prolog_custom_args.txt'),
			array('Prolog/prolog_dynamic_version.txt'),
			array('Prolog/prolog_dynamic_standalone.txt'),
			array('Prolog/prolog_dynamic_encoding.txt'),

			// REPEAT
			array('Repeat/repeat_basic.txt'),
			array('Repeat/repeat_separator.txt'),
			array('Repeat/repeat_separator_2.txt'),
			array('Repeat/repeat_iterator.txt'),

			// SECTION
			array('Section/section_basic.txt'),
			array('Section/section_else.txt'),
			array('Section/section_else_body.txt'),
			array('Section/section_else_multi.txt'),
			array('Section/section_order.txt'),
			array('Section/section_auto_relation.txt'),
			array('Section/section_parent.txt'),
			array('Section/section_parent_complex.txt'),
			array('Section/section_parent_complex2.txt'),
			array('Section/section_datasource.txt'),
			array('Section/section_datasource_nested.txt'),
			array('Section/section_if_coop.txt'),
			array('Section/section_display.txt'),
			array('Section/section_special_var.txt'),

			// TREE
			array('Tree/tree_basic.txt'),
			array('Tree/tree_complex.txt'),
			array('Tree/tree_else.txt'),
			array('Tree/tree_body.txt'),
			array('Tree/tree_body_else_correct.txt'),
			array('Tree/tree_body_else_invalid.txt'),
			array('Tree/tree_else_nested.txt'),
			array('Tree/tree_list_loop.txt'),
			array('Tree/tree_node_loop.txt'),
			array('Tree/tree_different_depth.txt'),
			array('Tree/tree_depth_error.txt'),
			/*
				array('attribute_1.txt'),
				array('attribute_2.txt'),
				array('attribute_3.txt'),
				array('attribute_4.txt'),
				array('attribute_5.txt'),
				array('attribute_6.txt'),
				array('attribute_7.txt'),
				array('attribute_8.txt'),
				array('attribute_9.txt'),
				array('attribute_10.txt'),
				array('attribute_11.txt'),
				array('attribute_12.txt'),
				array('attribute_13.txt'),
				array('attribute_14.txt'),
				array('attribute_15.txt'),
				array('attribute_16.txt'),
				array('attributes_build_1.txt'),
				array('attributes_build_2.txt'),
				array('attributes_build_3.txt'),
				array('block_1.txt'),
				array('block_2.txt'),
				array('block_3.txt'),
				array('block_4.txt'),
				array('block_5.txt'),
				array('capture_1.txt'),
				array('capture_2.txt'),
				array('capture_3.txt'),
				array('capture_4.txt'),
				array('capture_5.txt'),
				array('component_1.txt'),
				array('component_2.txt'),
				array('component_3.txt'),
				array('component_4.txt'),
				array('component_5.txt'),
				array('component_6.txt'),
				array('component_7.txt'),
				array('component_8.txt'),
				array('component_9.txt'),
				array('component_10.txt'),
				array('component_11.txt'),
				array('content_1.txt'),
				array('content_2.txt'),
				array('dtd_1.txt'),
				array('dtd_2.txt'),
				array('dtd_3.txt'),
				array('dtd_4.txt'),
				array('dtd_5.txt'),
				array('dtd_6.txt'),
				array('dtd_7.txt'),
				array('extend_1.txt'),
				array('extend_2.txt'),
				array('extend_3.txt'),
				array('extend_4.txt'),
				array('extend_5.txt'),
				array('extend_6.txt'),
				array('extend_7.txt'),
				array('extend_8.txt'),
				array('extend_9.txt'),
				array('extend_10.txt'),
				array('extend_11.txt'),
				array('extend_12.txt'),
				array('for_1.txt'),
				array('for_2.txt'),
				array('for_3.txt'),
				array('foreach_1.txt'),
				array('foreach_2.txt'),
				array('foreach_3.txt'),
				array('foreach_4.txt'),
				array('foreach_5.txt'),
				array('foreach_6.txt'),
				array('foreach_7.txt'),
				array('foreach_8.txt'),
				array('foreach_9.txt'),
				array('foreach_10.txt'),
				array('grid_1.txt'),
				array('grid_2.txt'),
				array('grid_3.txt'),
				array('grid_4.txt'),
				array('grid_5.txt'),
				array('grid_6.txt'),
				array('grid_7.txt'),
				array('grid_8.txt'),
				array('grid_9.txt'),
				array('if_1.txt'),
				array('if_2.txt'),
				array('if_3.txt'),
				array('if_4.txt'),
				array('if_5.txt'),
				array('if_6.txt'),
				array('if_7.txt'),
				array('if_8.txt'),
				array('if_9.txt'),
				array('if_10.txt'),
				array('include_1.txt'),
				array('include_2.txt'),
				array('include_3.txt'),
				array('include_4.txt'),
				array('include_5.txt'),
				array('include_6.txt'),
				array('include_7.txt'),
				array('include_8.txt'),
				array('insert_1.txt'),
				array('insert_2.txt'),
				array('insert_3.txt'),
				array('insert_4.txt'),
				array('insert_5.txt'),
				array('literal_1.txt'),
				array('literal_2.txt'),
				array('literal_3.txt'),
				array('literal_4.txt'),
				array('literal_5.txt'),
				array('literal_6.txt'),
				array('on_1.txt'),
				array('prolog_1.txt'),
				array('prolog_2.txt'),
				array('prolog_3.txt'),
				array('put_1.txt'),
				array('put_2.txt'),
				array('put_3.txt'),
				array('repeat_1.txt'),
				array('repeat_2.txt'),
				array('repeat_3.txt'),
				array('repeat_4.txt'),
				array('root_1.txt'),
				array('root_2.txt'),
				array('root_3.txt'),
				array('root_4.txt'),
				array('root_5.txt'),
				array('section_1.txt'),
				array('section_2.txt'),
				array('section_3.txt'),
				array('section_4.txt'),
				array('section_5.txt'),
				array('section_6.txt'),
				array('section_7.txt'),
				array('section_8.txt'),
				array('section_9.txt'),
				array('section_10.txt'),
				array('section_11.txt'),
				array('section_12.txt'),
				array('selector_1.txt'),
				array('selector_2.txt'),
				array('selector_3.txt'),
				array('selector_4.txt'),
				array('selector_5.txt'),
				array('selector_6.txt'),
				array('selector_7.txt'),
				array('selector_8.txt'),
				array('show_1.txt'),
				array('show_2.txt'),
				array('show_3.txt'),
				array('show_4.txt'),
				array('show_5.txt'),
				array('show_6.txt'),
				array('show_7.txt'),
				array('show_8.txt'),
				array('show_9.txt'),
				array('show_10.txt'),
				array('single_1.txt'),
				array('single_2.txt'),
				array('single_3.txt'),
				array('snippet_1.txt'),
				array('snippet_2.txt'),
				array('snippet_3.txt'),
				array('snippet_4.txt'),
				array('snippet_5.txt'),
				array('snippet_6.txt'),
				array('tag_1.txt'),
				array('tag_2.txt'),
				array('tag_3.txt'),
				array('tag_4.txt'),
				array('tag_5.txt'),
				array('tree_1.txt'),
				array('tree_2.txt'),
				array('tree_3.txt'),
				array('tree_4.txt'),
				array('tree_5.txt'),
				array('tree_6.txt'),
				array('tree_7.txt'),
			*/
		);
	} // end dataProvider();

 	/**
 	 * @dataProvider dataProvider
	 * @runInSeparateProcess
 	 */
	public function testInstructions($testCase)
	{
		return $this->_checkTest(dirname(__FILE__).'/Tests/'.$testCase);
	} // end testInstructions();
} // end Package_Instruction_InstructionTest;