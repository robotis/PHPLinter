<?php
/**
----------------------------------------------------------------------+
*  @desc			PHPLinter autoloader.
----------------------------------------------------------------------+
*  @file 			Lint_function.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    Oct 29, 2011
*  @package 		PHPLinter
*  @copyright     
*    phplinter is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
		case 'PHPLinter\CLI':
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
		case 'PHPLinter\Lint_anon_function':
		case 'PHPLinter\Lint_class':
		case 'PHPLinter\Lint_interface':
		case 'PHPLinter\Lint_security':
		case 'PHPLinter\Lint_comment':
			require "$dir/Lints/{$cls[1]}.php";
			break;
	}
}
spl_autoload_register('PHPLinter\PHPLinter_autoload');