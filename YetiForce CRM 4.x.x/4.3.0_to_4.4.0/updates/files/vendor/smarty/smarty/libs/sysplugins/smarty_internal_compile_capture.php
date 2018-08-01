<?php
/**
 * Smarty Internal Plugin Compile Capture
 * Compiles the {capture} tag.
 *
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Capture Class.
 */
class Smarty_Internal_Compile_Capture extends Smarty_Internal_CompileBase
{
	/**
	 * Attribute definition: Overwrites base class.
	 *
	 * @var array
	 *
	 * @see Smarty_Internal_CompileBase
	 */
	public $shorttag_order = ['name'];

	/**
	 * Attribute definition: Overwrites base class.
	 *
	 * @var array
	 *
	 * @see Smarty_Internal_CompileBase
	 */
	public $optional_attributes = ['name', 'assign', 'append'];

	/**
	 * Compiles code for the {$smarty.capture.xxx}.
	 *
	 * @param array $args array with attributes from parser
	 * @param \Smarty_Internal_TemplateCompilerBase$compiler  compiler object
	 * @param array $parameter array with compilation parameter
	 *
	 * @throws \SmartyCompilerException
	 *
	 * @return string compiled code
	 */
	public static function compileSpecialVariable($args, Smarty_Internal_TemplateCompilerBase $compiler, $parameter = null)
	{
		$tag = trim($parameter[0], '"\'');
		$name = isset($parameter[1]) ? $compiler->getId($parameter[1]) : false;
		if (!$name) {
			$compiler->trigger_template_error("missing or illegal \$smarty.{$tag} name attribute", null, true);
		}
		return "\$_smarty_tpl->smarty->ext->_capture->getBuffer(\$_smarty_tpl, '{$name}')";
	}

	/**
	 * Compiles code for the {capture} tag.
	 *
	 * @param array                                 $args      array with attributes from parser
	 * @param \Smarty_Internal_TemplateCompilerBase $compiler  compiler object
	 * @param null                                  $parameter
	 *
	 * @return string compiled code
	 */
	public function compile($args, Smarty_Internal_TemplateCompilerBase $compiler, $parameter = null)
	{
		// check and get attributes
		$_attr = $this->getAttributes($compiler, $args, $parameter, 'capture');

		$buffer = $_attr['name'] ?? "'default'";
		$assign = $_attr['assign'] ?? 'null';
		$append = $_attr['append'] ?? 'null';

		$compiler->_cache['capture_stack'][] = [$compiler->nocache];
		// maybe nocache because of nocache variables
		$compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
		$_output = "<?php \$_smarty_tpl->smarty->ext->_capture->open(\$_smarty_tpl, $buffer, $assign, $append);?>";

		return $_output;
	}
}

/**
 * Smarty Internal Plugin Compile Captureclose Class.
 */
class Smarty_Internal_Compile_CaptureClose extends Smarty_Internal_CompileBase
{
	/**
	 * Compiles code for the {/capture} tag.
	 *
	 * @param array                                 $args      array with attributes from parser
	 * @param \Smarty_Internal_TemplateCompilerBase $compiler  compiler object
	 * @param null                                  $parameter
	 *
	 * @return string compiled code
	 */
	public function compile($args, Smarty_Internal_TemplateCompilerBase $compiler, $parameter)
	{
		// check and get attributes
		$_attr = $this->getAttributes($compiler, $args, $parameter, '/capture');
		// must endblock be nocache?
		if ($compiler->nocache) {
			$compiler->tag_nocache = true;
		}

		list($compiler->nocache) = array_pop($compiler->_cache['capture_stack']);

		return '<?php $_smarty_tpl->smarty->ext->_capture->close($_smarty_tpl);?>';
	}
}
