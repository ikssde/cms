%token_prefix T_
%name Opt_Expression_Standard_
%declare_class {class Opt_Expression_Standard_Parser}

%include_class
{
	/**
	 * The expression engine object.
	 * @var Opt_Expression_Standard
	 */
	private $_expr;

	/**
	 * Constructs the expression parser.
	 *
	 * @param Opt_Expression_Standard $expr The expression engine used for parsing.
	 */
	public function __construct(Opt_Expression_Standard $expr)
	{
		$this->_expr = $expr;
	} // end __construct();
}

%syntax_error {
	throw new Opt_Expression_Exception('Invalid token: '.$TOKEN);
}

%left	AND.
%left	OR.
%left	XOR.
%left	EQUALS EQUALS_T NEQUALS NEQUALS_T.
%left	GT GTE LT LTE.
%left	IS_BETWEEN IS_NOT_BETWEEN.
%left	IS_EITHER IS_NEITHER.
%left	CONTAINS CONTAINS_EITHER CONTAINS_NEITHER CONTAINS_BOTH.
%left	IS_IN IS_NOT_IN.
%left	IS_IN_EITHER IS_IN_NEITHER IS_IN_BOTH.
%left	ADD SUB MINUS CONCAT.
%left	MUL DIV MOD.
%left	COLON.
%right	EXP NOT.
%right	ASSIGN.
%right	INCREMENT DECREMENT.

// warning: in case of strange errors while parsing something with this grammar
// that seem to make no sense, please take a look at ParserGenerator/Data.php file,
// method buildshifts(). It must have been rewritten to a non-recursive version
// in order not to crash while parsing this grammar due to "maximum nesting level too deep".
// The bug may lie there.

overall_expr(res)	::= expr(ex).					{	res = $this->_expr->_finalize(ex);	}
expr(res)			::= expr(ex1) ADD expr(ex2).	{	res = $this->_expr->_stdOperator('+', ex1, ex2, Opt_Expression_Standard::MATH_OP_WEIGHT);	}
expr(res)			::= expr(ex1) SUB expr(ex2).					{	res = $this->_expr->_stdOperator('-', ex1,  ex2, Opt_Expression_Standard::MATH_OP_WEIGHT);	}
expr(res)			::= expr(ex1) MINUS expr(ex2).					{	res = $this->_expr->_stdOperator('-', ex1,  ex2, Opt_Expression_Standard::MATH_OP_WEIGHT);	}
expr(res)			::= expr(ex1) MUL expr(ex2).					{	res = $this->_expr->_stdOperator('*',  ex1,  ex2, Opt_Expression_Standard::MATH_OP_WEIGHT);	}
expr(res)			::= expr(ex1) DIV expr(ex2).					{	res = $this->_expr->_stdOperator('/',  ex1,  ex2, Opt_Expression_Standard::MATH_OP_WEIGHT);	}
expr(res)			::= expr(ex1) MOD expr(ex2).					{	res = $this->_expr->_stdOperator('%',  ex1,  ex2, Opt_Expression_Standard::MATH_OP_WEIGHT);	}
expr(res)			::= expr(ex1) AND expr(ex2).					{	res = $this->_expr->_stdOperator('&&',  ex1,  ex2, Opt_Expression_Standard::LOGICAL_OP_WEIGHT);	}
expr(res)			::= expr(ex1) OR expr(ex2).						{	res = $this->_expr->_stdOperator('||',  ex1,  ex2, Opt_Expression_Standard::LOGICAL_OP_WEIGHT);	}
expr(res)			::= expr(ex1) XOR expr(ex2).					{	res = $this->_expr->_stdOperator(' xor ',  ex1,  ex2, Opt_Expression_Standard::LOGICAL_OP_WEIGHT);	}
expr(res)			::= expr(ex1) EXP expr(ex2).					{	res = $this->_expr->_functionalOperator('pow', array( ex1,  ex2), Opt_Expression_Standard::FUNCTIONAL_OP_WEIGHT);	}
expr(res)			::= expr(ex1) EQUALS expr(ex2).					{	res = $this->_expr->_stdOperator('==',  ex1,  ex2, Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
expr(res)			::= expr(ex1) EQUALS_T expr(ex2).				{	res = $this->_expr->_stdOperator('===',  ex1,  ex2, Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
expr(res)			::= expr(ex1) NEQUALS expr(ex2).				{	res = $this->_expr->_stdOperator('!=',  ex1,  ex2, Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
expr(res)			::= expr(ex1) NEQUALS_T expr(ex2).				{	res = $this->_expr->_stdOperator('!==',  ex1,  ex2, Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
expr(res)			::= expr(ex1) GT expr(ex2).						{	res = $this->_expr->_stdOperator('>',  ex1,  ex2, Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
expr(res)			::= expr(ex1) GTE expr(ex2).					{	res = $this->_expr->_stdOperator('>=',  ex1,  ex2, Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
expr(res)			::= expr(ex1) LT expr(ex2).						{	res = $this->_expr->_stdOperator('<',  ex1,  ex2, Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
expr(res)			::= expr(ex1) LTE expr(ex2).					{	res = $this->_expr->_stdOperator('<=',  ex1,  ex2, Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
expr(res)			::= expr(ex1) CONCAT expr(ex2).					{	res = $this->_expr->_stdOperator('.',  ex1,  ex2, Opt_Expression_Standard::CONCAT_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) CONTAINS expr(ex2).							{	res = $this->_expr->_expressionOperator('contains', array(ex1, ex2), Opt_Expression_Standard::DF_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) CONTAINS_EITHER expr(ex2) OR expr(ex3).		{	res = $this->_expr->_expressionOperator('contains_either', array(ex1, ex2, ex3), Opt_Expression_Standard::DF_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) CONTAINS_NEITHER expr(ex2) NOR expr(ex3).		{	res = $this->_expr->_expressionOperator('contains_neither', array(ex1, ex2, ex3), Opt_Expression_Standard::DF_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) CONTAINS_BOTH expr(ex2) AND expr(ex3).		{	res = $this->_expr->_expressionOperator('contains_both', array(ex1, ex2, ex3), Opt_Expression_Standard::DF_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_BETWEEN expr(ex2) AND expr(ex3).			{	res = $this->_expr->_expressionOperator('between', array(ex1, ex2, ex3), 2 * Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_NOT_BETWEEN expr(ex2) AND expr(ex3).		{	res = $this->_expr->_expressionOperator('not_between', array(ex1, ex2, ex3), 2 * Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_EITHER expr(ex2) OR expr(ex3).				{	res = $this->_expr->_expressionOperator('either', array(ex1, ex2, ex3), 2 * Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_NEITHER expr(ex2) NOR expr(ex3).			{	res = $this->_expr->_expressionOperator('neither', array(ex1, ex2, ex3), 2 * Opt_Expression_Standard::COMPARE_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_IN expr(ex2).								{	res = $this->_expr->_expressionOperator('is_in', array(ex1, ex2), Opt_Expression_Standard::DF_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_NOT_IN expr(ex2).							{	res = $this->_expr->_expressionOperator('is_not_in', array(ex1, ex2), Opt_Expression_Standard::DF_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_EITHER_IN expr(ex2) OR expr(ex3).			{	res = $this->_expr->_expressionOperator('is_either_in', array(ex1, ex2, ex3), Opt_Expression_Standard::DF_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_NEITHER_IN expr(ex2) NOR expr(ex3).		{	res = $this->_expr->_expressionOperator('is_neither_in', array(ex1, ex2, ex3), Opt_Expression_Standard::DF_OP_WEIGHT);	}
cexpr(res)			::= expr(ex1) IS_BOTH_IN expr(ex2) AND expr(ex3).			{	res = $this->_expr->_expressionOperator('is_both_in', array(ex1, ex2, ex3), Opt_Expression_Standard::DF_OP_WEIGHT);	}
expr(res)			::= NOT expr(ex).					{	res = $this->_expr->_unaryOperator('!', ex, Opt_Expression_Standard::LOGICAL_OP_WEIGHT);	}
expr(res)			::= MINUS expr(ex).					{	res = $this->_expr->_unaryOperator('-', ex, Opt_Expression_Standard::LOGICAL_OP_WEIGHT);	}
expr(res)			::= L_BRACKET expr(ex) R_BRACKET.	{	res = $this->_expr->_package('(', ex, Opt_Expression_Standard::PARENTHESES_WEIGHT);	}
expr(res)			::= cexpr(ex).						{	res = ex;	}
expr(res)			::= variable(var) INCREMENT.
{
	if(var[1] == 0)
	{
		res = $this->_expr->_compileVariable(var[0][0], var[0][1],Opt_Expression_Standard::INCDEC_OP_WEIGHT, Opt_Expression_Standard::CONTEXT_POSTINCREMENT, null);
	}
	else
	{
		res = $this->_expr->_compilePhpOperation('postincrement', var[0], null, Opt_Expression_Standard::INCDEC_OP_WEIGHT);
	}
}
expr(res)			::= INCREMENT variable(var).
{
	if(var[1] == 0)
	{
		res = $this->_expr->_compileVariable(var[0][0], var[0][1],Opt_Expression_Standard::INCDEC_OP_WEIGHT, Opt_Expression_Standard::CONTEXT_PREINCREMENT, null);
	}
	else
	{
		res = $this->_expr->_compilePhpOperation('preincrement', var[0], null, Opt_Expression_Standard::INCDEC_OP_WEIGHT);
	}
}
expr(res)			::= variable(var) DECREMENT.
{
	if(var[1] == 0)
	{
		res = $this->_expr->_compileVariable(var[0][0], var[0][1],Opt_Expression_Standard::INCDEC_OP_WEIGHT, Opt_Expression_Standard::CONTEXT_POSTDECREMENT, null);
	}
	else
	{
		res = $this->_expr->_compilePhpOperation('postdecrement', var[0], null, Opt_Expression_Standard::INCDEC_OP_WEIGHT);
	}
}
expr(res)			::= DECREMENT variable(var).
{
	if(var[1] == 0)
	{
		res = $this->_expr->_compileVariable(var[0][0], var[0][1],Opt_Expression_Standard::INCDEC_OP_WEIGHT, Opt_Expression_Standard::CONTEXT_PREDECREMENT, null);
	}
	else
	{
		res = $this->_expr->_compilePhpOperation('predecrement', var[0], null, Opt_Expression_Standard::INCDEC_OP_WEIGHT);
	}
}
expr(res)			::= variable(var) ASSIGN expr(expr).
{
	if(var[1] == 0)
	{
		res = $this->_expr->_compileVariable(var[0][0], var[0][1],Opt_Expression_Standard::ASSIGN_OP_WEIGHT, Opt_Expression_Standard::CONTEXT_ASSIGN, expr);
	}
	else
	{
		res = $this->_expr->_compilePhpOperation('assign', var[0], expr, Opt_Expression_Standard::ASSIGN_OP_WEIGHT);
	}
}
expr(res)			::= variable(var) IS expr(expr).
{
	if(var[1] == 0)
	{
		res = $this->_expr->_compileVariable(var[0][0], var[0][1],Opt_Expression_Standard::ASSIGN_OP_WEIGHT, Opt_Expression_Standard::CONTEXT_ASSIGN, expr);
	}
	else
	{
		res = $this->_expr->_compilePhpOperation('assign', var[0], expr, Opt_Expression_Standard::ASSIGN_OP_WEIGHT);
	}
}
expr(res)			::= variable(var) EXISTS.
	{
		res = $this->_expr->_compileVariable(var[0][0], var[0][1],Opt_Expression_Standard::ASSIGN_OP_WEIGHT, Opt_Expression_Standard::CONTEXT_EXISTS, expr);
	}

expr(res)			::= CLONE expr(ex).			{	res = $this->_expr->_objective('clone', ex, Opt_Expression_Standard::CLONE_WEIGHT);	}

expr(res)			::= variable(var).
{
	if(var[1] == 0)
	{
		res = $this->_expr->_compileVariable(var[0][0], var[0][1], 0);
	}
	else
	{
		res = var[0];
	}
}
expr(res)			::= static_value(val).			{	res = val;	}
expr(res)			::= calculated(val).			{	res = val;	}
expr(res)			::= language_variable(val).		{	res = val;	}
expr(res)			::= container_creator(val).		{	res = val;	}
expr(res)			::= object_creator(val).		{	res = val;	}

variable(res)		::= simple_variable(val).		{	res = array(val, 0);	}
variable(res)		::= object_field_call(val).		{	res = array(val, 1);	}
variable(res)		::= array_call(val).			{	res = array(val, 1);	}

simple_variable(res)	::= script_variable(val).		{	res =   val;	}
simple_variable(res)	::= template_variable(val).		{	res =	val;	}
simple_variable(res)	::= container(val).				{	res =	val;	}

static_value(res)	::= number(n).				{	res = $this->_expr->_scalarValue(n, Opt_Expression_Standard::SCALAR_WEIGHT);	}
static_value(res)	::= string(s).				{	res = $this->_expr->_scalarValue(s, Opt_Expression_Standard::SCALAR_WEIGHT);	}
static_value(res)	::= BACKTICK_STRING(s).	{	res = $this->_expr->_backtick(s, Opt_Expression_Standard::BACKTICK_WEIGHT);	}
static_value(res)	::= boolean(b).			{	res = $this->_expr->_scalarValue(b, Opt_Expression_Standard::SCALAR_WEIGHT);	}
static_value(res)	::= NULL.				{	res = $this->_expr->_scalarValue('null', Opt_Expression_Standard::SCALAR_WEIGHT);	}

string(s)			::= STRING(val).			{ s = val; }
string(s)			::= IDENTIFIER(val).		{ s = '\''.val.'\''; }

number(n)			::= NUMBER(val).			{ n =  val; }
number(n)			::= MINUS NUMBER(val).		{ n = - val; }

boolean(b)			::= TRUE.			{ b = 'true'; }
boolean(b)			::= FALSE.			{ b = 'false'; }

// Note - this HAS to be above container_creator, otherwise
// there are lots of balls with function calls.

argument_list(a)	::= expr(e).							{	a = array(e);	}
argument_list(a)	::= expr(e) COMMA argument_list(nxt).	{	array_unshift(nxt, e); a = nxt; }

container_creator(res)	::= LSQ_BRACKET RSQ_BRACKET.					{	res = $this->_expr->_containerValue(null, Opt_Expression_Standard::CONTAINER_WEIGHT); }
container_creator(res)	::= LSQ_BRACKET container_def(p) RSQ_BRACKET.	{	res = $this->_expr->_containerValue(p, Opt_Expression_Standard::CONTAINER_WEIGHT); }
container_def(res)		::= single_container_def(def).						{	res = array(def);	}
container_def(res)		::= single_container_def(def) COMMA container_def(r).	{	array_unshift(r, def); res = r;	}

single_container_def(res)	::= expr(e1) COLON expr(e2).	{	res = $this->_expr->_pair(e1, e2);	}
single_container_def(res)	::= expr(e1).					{	res = $this->_expr->_pair(null, e1);	}

script_variable(res)	::= DOLLAR IDENTIFIER(name).	{	res = $this->_expr->_prepareScriptVar(name); }
template_variable(res)	::= AT IDENTIFIER(name).		{	res = $this->_expr->_prepareTemplateVar(name); }
language_variable(res)	::= DOLLAR IDENTIFIER(group) AT IDENTIFIER(id).	{	res = $this->_expr->_compileLanguageVar(group, id, Opt_Expression_Standard::LANGUAGE_VARIABLE_WEIGHT); }
container(res)			::= script_variable(var) container_call(cont).
		{
			array_unshift(cont, var[0][0]);
			res = new SplFixedArray(3);
			res[0] = cont;
			res[1] = '$';
		}
container(res)			::= template_variable(var) container_call(cont).
		{
			array_unshift(cont, var[0][0]);
			res = new SplFixedArray(3);
			res[0] = cont;
			res[1] = '@';
		}

container_call(res)		::= single_container_call(f).					{	res = array(f);	}
container_call(res)		::= single_container_call(f) container_call(c).	{	array_unshift(c, f); res = c;	}

single_container_call(res)	::= DOT IDENTIFIER(s).					{	res = s;	}
single_container_call(res)	::= DOT NUMBER(n).						{	res = n;	}
single_container_call(res)	::= DOT L_BRACKET expr(r) R_BRACKET.	{	res = r;	}

object_field_call(res)	::= simple_variable(prev) OBJECT_OPERATOR IDENTIFIER(cur).		{	res = $this->_expr->_buildObjectFieldDynamic(prev, cur);	}
object_field_call(res)	::= IDENTIFIER(prev) OBJECT_OPERATOR IDENTIFIER(cur).			{	res = $this->_expr->_buildObjectFieldStatic(prev, cur);	}
object_field_call(res)	::= object_field_call(prev) OBJECT_OPERATOR IDENTIFIER(cur).	{	res = $this->_expr->_buildObjectFieldNext(prev, cur);	}
object_field_call(res)	::= array_call(prev) OBJECT_OPERATOR IDENTIFIER(cur).		{	res = $this->_expr->_buildObjectFieldNext(prev, cur);	}
object_field_call(res)	::= method_call(prev) OBJECT_OPERATOR IDENTIFIER(cur).			{	res = $this->_expr->_buildObjectFieldNext(prev, cur);	}
object_field_call(res)	::= function_call(prev) OBJECT_OPERATOR IDENTIFIER(cur).				{	res = $this->_expr->_buildObjectFieldNext(prev, cur);	}

method_call(res)	::= simple_variable(prev) OBJECT_OPERATOR functional(cur).		{	res = $this->_expr->_buildMethodDynamic(prev, cur);	}
method_call(res)	::= IDENTIFIER(prev) OBJECT_OPERATOR functional(cur).			{	res = $this->_expr->_buildMethodStatic(prev, cur);	}
method_call(res)	::= object_field_call(prev) OBJECT_OPERATOR functional(cur).	{	res = $this->_expr->_buildMethodNext(prev, cur);	}
method_call(res)	::= array_call(prev) OBJECT_OPERATOR functional(cur).		{	res = $this->_expr->_buildMethodNext(prev, cur);	}
method_call(res)	::= method_call(prev) OBJECT_OPERATOR functional(cur).			{	res = $this->_expr->_buildMethodNext(prev, cur);	}
method_call(res)	::= function_call(prev) OBJECT_OPERATOR functional(cur).				{	res = $this->_expr->_buildMethodNext(prev, cur);	}

array_call(res)	::= simple_variable(prev) LSQ_BRACKET expr(cur) RSQ_BRACKET.	{	res = $this->_expr->_buildArrayDynamic(prev, cur);	}
array_call(res)	::= array_call(prev) LSQ_BRACKET expr(cur) RSQ_BRACKET.			{	res = $this->_expr->_buildArrayNext(prev, cur);	}
array_call(res)	::= object_field_call(prev) LSQ_BRACKET expr(cur) RSQ_BRACKET.	{	res = $this->_expr->_buildArrayNext(prev, cur);	}

calculated(res)		::= function_call(fc).		{	res = fc;	}
calculated(res)		::= method_call(oc).	{	res = oc;	}

function_call(res)	::= functional(fun).		{	res = $this->_expr->_makeFunction(fun);	}

functional(f)	::= IDENTIFIER(s) L_BRACKET container_def(a) R_BRACKET.	{	f = $this->_expr->_makeFunctional(s, array($this->_expr->_containerValue(a, Opt_Expression_Standard::CONTAINER_WEIGHT)));	}
functional(f)	::= IDENTIFIER(s) L_BRACKET argument_list(a) R_BRACKET.	{	f = $this->_expr->_makeFunctional(s, a); }
functional(f)	::= IDENTIFIER(s) L_BRACKET R_BRACKET.	{	f = $this->_expr->_makeFunctional(s, array()); }

object_creator(res)	::= NEW IDENTIFIER(id).											{	res = $this->_expr->_objective('new', array(id, array()), Opt_Expression_Standard::NEW_WEIGHT); }
object_creator(res)	::= NEW IDENTIFIER(id) L_BRACKET argument_list(args) R_BRACKET.	{	res = $this->_expr->_objective('new', array(id, args), Opt_Expression_Standard::NEW_WEIGHT); }
