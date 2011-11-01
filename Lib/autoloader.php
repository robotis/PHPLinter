<?php
/**
----------------------------------------------------------------------+
*  @desc			Autoloader
----------------------------------------------------------------------+
*  @copyright 		Copyright 2011, RHÍ
*  @file 			autoloader.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    Oct 29, 2011
*  @package 		PHPLinter
----------------------------------------------------------------------+
*/
namespace PHPLinter;
function PHPLinter_autoload($class) {
	$dir = dirname(__FILE__);
	$cls = preg_split('/\\\/u', $class);
	switch($class) {
		case 'PHPLinter\Report':
		case 'PHPLinter\Tokenizer':
		case 'PHPLinter\Path':
		case 'PHPLinter\Set':
			require "$dir/{$cls[1]}.php";
			break;
		case 'PHPLinter\PHPLinter':
			require "$dir/Linter.php";
			break;
		case 'PHPLinter\ILint':
		case 'PHPLinter\BaseLint':
		case 'PHPLinter\Lint_file':
		case 'PHPLinter\Lint_method':
		case 'PHPLinter\Lint_function':
		case 'PHPLinter\Lint_class':
		case 'PHPLinter\Lint_interface':
		case 'PHPLinter\Lint_security':
		case 'PHPLinter\Lint_comment':
			require "$dir/Lints/{$cls[1]}.php";
			break;
	}
}
spl_autoload_register('PHPLinter\PHPLinter_autoload');