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
 * The lexer class for the expression parser. Note: this file
 * is automatically generated by PHP Parser Generator lexer
 * by Gregory Beaver. Do not modify it manually. Instead,
 * use the file /tools/lexer/expression_lexer.plex and parse
 * it with /tools/lexer/generateExpression.php.
 */
class Opt_Expression_Standard_Lexer
{
	/**
	 * The data field for the lexical analyzer.
	 */
	private $_data;
	/**
	 * The token counter for the lexical analyzer.
	 */
	private $_counter;
	/**
	 * The line counter for the lexical analyzer.
	 */
	private $_line;

	/**
	 * The recognized token number for parser.
	 * @var integer
	 */
	public $token;

	/**
	 * The recognized token value for parser.
	 * @var string
	 */
	public $value;

	/**
	 * Constructs the lexer object for parsing the specified
	 * expression.
	 *
	 * @param string $expression The expression to parse.
	 */
	public function __construct($expression)
	{
		$this->_data = $expression;
		$this->_line = 1;
		$this->_counter = 0;
	} // end __construct();



    private $_yy_state = 1;
    private $_yy_stack = array();

    function yylex()
    {
        return $this->{'yylex' . $this->_yy_state}();
    }

    function yypushstate($state)
    {
        array_push($this->_yy_stack, $this->_yy_state);
        $this->_yy_state = $state;
    }

    function yypopstate()
    {
        $this->_yy_state = array_pop($this->_yy_stack);
    }

    function yybegin($state)
    {
        $this->_yy_state = $state;
    }



    function yylex1()
    {
        $tokenMap = array (
              1 => 0,
              2 => 1,
              4 => 0,
              5 => 0,
              6 => 0,
              7 => 0,
              8 => 0,
              9 => 0,
              10 => 0,
              11 => 0,
              12 => 0,
              13 => 0,
              14 => 0,
              15 => 0,
              16 => 0,
              17 => 0,
              18 => 0,
              19 => 0,
              20 => 0,
              21 => 0,
              22 => 0,
              23 => 0,
              24 => 0,
              25 => 0,
              26 => 0,
              27 => 0,
              28 => 0,
              29 => 0,
              30 => 0,
              31 => 0,
              32 => 0,
              33 => 0,
              34 => 0,
              35 => 0,
              36 => 0,
              37 => 0,
              38 => 0,
              39 => 0,
              40 => 0,
              41 => 0,
              42 => 0,
              43 => 0,
              44 => 0,
              45 => 0,
            );
        if ($this->_counter >= strlen($this->_data)) {
            return false; // end of input
        }
        $yy_global_pattern = "/^(\\s+)|^((\\+\\+|--))|^(\\+)|^(-)|^(\\*)|^(\/)|^(%)|^(\\^)|^(!)|^(==)|^(===)|^(!=)|^(!=)|^(~)|^(is\\s+between)|^(is\\s+not\\s+between)|^(is\\s+either)|^(is\\s+neither)|^(contains\\s+both)|^(contains\\s+either)|^(contains\\s+neither)|^(is\\s+in)|^(is\\s+not\\s+in)|^(is\\s+both\\s+in)|^(is\\s+either\\s+in)|^(is\\s+neither\\s+in)|^(\\$)|^(@)|^(\\.)|^(=)|^(\\()|^(\\))|^(<)|^(>)|^(\\[)|^(\\])|^(::)|^(,)|^(:)|^([a-zA-Z_][a-zA-Z0-9_]*)|^('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|^(`[^`\\\\]*(?:\\\\.[^`\\\\]*)*`)|^([0-9]+\\.?[0-9]*)|^(0[xX][0-9a-fA-F]+)/";

        do {
            if (preg_match($yy_global_pattern, substr($this->_data, $this->_counter), $yymatches)) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception('Error: lexing failed because a rule matched' .
                        'an empty string.  Input "' . substr($this->_data,
                        $this->_counter, 5) . '... state CODE');
                }
                next($yymatches); // skip global match
                $this->token = key($yymatches); // token number
                if ($tokenMap[$this->token]) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, $this->token + 1,
                        $tokenMap[$this->token]);
                } else {
                    $yysubmatches = array();
                }
                $this->value = current($yymatches); // token value
                $r = $this->{'yy_r1_' . $this->token}($yysubmatches);
                if ($r === null) {
                    $this->_counter += strlen($this->value);
                    $this->_line += substr_count($this->value, "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    $this->_counter += strlen($this->value);
                    $this->_line += substr_count($this->value, "\n");
                    if ($this->_counter >= strlen($this->_data)) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Opt_Expression_Exception('Unexpected input at line '.$this->_line.': '.$this->_data[$this->_counter]);
            }
            break;
        } while (true);

    } // end function


    const CODE = 1;
    function yy_r1_1($yy_subpatterns)
    {

	$this->token = 'w';
    }
    function yy_r1_2($yy_subpatterns)
    {

	if($this->value == '++')
	{
		$this->token = Opt_Expression_Standard_Parser::T_INCREMENT;
	}
	else
	{
		$this->token = Opt_Expression_Standard_Parser::T_DECREMENT;
	}
    }
    function yy_r1_4($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_ADD;
    }
    function yy_r1_5($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_MINUS;
    }
    function yy_r1_6($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_MUL;
    }
    function yy_r1_7($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_DIV;
    }
    function yy_r1_8($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_MOD;
    }
    function yy_r1_9($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_EXP;
    }
    function yy_r1_10($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_NOT;
    }
    function yy_r1_11($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_EQUALS;
    }
    function yy_r1_12($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_EQUALS_T;
    }
    function yy_r1_13($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_NEQUALS;
    }
    function yy_r1_14($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_NEQUALS_T;
    }
    function yy_r1_15($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_CONCAT;
    }
    function yy_r1_16($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_BETWEEN;
    }
    function yy_r1_17($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_NOT_BETWEEN;
    }
    function yy_r1_18($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_EITHER;
    }
    function yy_r1_19($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_NEITHER;
    }
    function yy_r1_20($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_CONTAINS_BOTH;
    }
    function yy_r1_21($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_CONTAINS_EITHER;
    }
    function yy_r1_22($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_CONTAINS_NEITHER;
    }
    function yy_r1_23($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_IN;
    }
    function yy_r1_24($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_NOT_IN;
    }
    function yy_r1_25($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_BOTH_IN;
    }
    function yy_r1_26($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_EITHER_IN;
    }
    function yy_r1_27($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_IS_NEITHER_IN;
    }
    function yy_r1_28($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_DOLLAR;
    }
    function yy_r1_29($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_AT;
    }
    function yy_r1_30($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_DOT;
    }
    function yy_r1_31($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_ASSIGN;
    }
    function yy_r1_32($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_L_BRACKET;
    }
    function yy_r1_33($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_R_BRACKET;
    }
    function yy_r1_34($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_DLSQ_BRACKET;
    }
    function yy_r1_35($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_DRSQ_BRACKET;
    }
    function yy_r1_36($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_LSQ_BRACKET;
    }
    function yy_r1_37($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_RSQ_BRACKET;
    }
    function yy_r1_38($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_OBJECT_OPERATOR;
    }
    function yy_r1_39($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_COMMA;
    }
    function yy_r1_40($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_COLON;
    }
    function yy_r1_41($yy_subpatterns)
    {

	switch($this->value)
	{
		case 'add':
			$this->token = Opt_Expression_Standard_Parser::T_ADD;
			break;
		case 'sub':
			$this->token = Opt_Expression_Standard_Parser::T_SUB;
			break;
		case 'mul':
			$this->token = Opt_Expression_Standard_Parser::T_MUL;
			break;
		case 'div':
			$this->token = Opt_Expression_Standard_Parser::T_DIV;
			break;
		case 'mod':
			$this->token = Opt_Expression_Standard_Parser::T_MOD;
			break;
		case 'exp':
			$this->token = Opt_Expression_Standard_Parser::T_EXP;
			break;
		case 'and':
			$this->token = Opt_Expression_Standard_Parser::T_AND;
			break;
		case 'or':
			$this->token = Opt_Expression_Standard_Parser::T_OR;
			break;
		case 'nor':
			$this->token = Opt_Expression_Standard_Parser::T_NOR;
			break;
		case 'not':
			$this->token = Opt_Expression_Standard_Parser::T_NOT;
			break;
		case 'xor':
			$this->token = Opt_Expression_Standard_Parser::T_XOR;
			break;
		case 'eq':
			$this->token = Opt_Expression_Standard_Parser::T_EQUALS;
			break;
		case 'neq':
			$this->token = Opt_Expression_Standard_Parser::T_NEQUALS;
			break;
		case 'eqt':
			$this->token = Opt_Expression_Standard_Parser::T_EQUALS_T;
			break;
		case 'neqt':
			$this->token = Opt_Expression_Standard_Parser::T_NEQUALS_T;
			break;
		case 'gt':
			$this->token = Opt_Expression_Standard_Parser::T_GT;
			break;
		case 'lt':
			$this->token = Opt_Expression_Standard_Parser::T_LT;
			break;
		case 'gte':
			$this->token = Opt_Expression_Standard_Parser::T_GTE;
			break;
		case 'lte':
			$this->token = Opt_Expression_Standard_Parser::T_LTE;
			break;
		case 'contains':
			$this->token = Opt_Expression_Standard_Parser::T_CONTAINS;
			break;
		case 'exists':
			$this->token = Opt_Expression_Standard_Parser::T_EXISTS;
			break;
		case 'is':
			$this->token = Opt_Expression_Standard_Parser::T_ASSIGN;
			break;
		case 'true':
			$this->token = Opt_Expression_Standard_Parser::T_TRUE;
			break;
		case 'false':
			$this->token = Opt_Expression_Standard_Parser::T_FALSE;
			break;
		case 'null':
			$this->token = Opt_Expression_Standard_Parser::T_NULL;
			break;
		case 'new':
			$this->token = Opt_Expression_Standard_Parser::T_NEW;
			break;
		case 'clone':
			$this->token = Opt_Expression_Standard_Parser::T_CLONE;
			break;
		default:
			$this->token = Opt_Expression_Standard_Parser::T_IDENTIFIER;
	}
    }
    function yy_r1_42($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_STRING;
    }
    function yy_r1_43($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_BACKTICKS;
    }
    function yy_r1_44($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_NUMBER;
    }
    function yy_r1_45($yy_subpatterns)
    {

	$this->token = Opt_Expression_Standard_Parser::T_NUMBER;
    }


} // end Opt_Expression_Standard_Lexer;