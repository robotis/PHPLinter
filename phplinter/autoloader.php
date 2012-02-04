<?php
/**
----------------------------------------------------------------------+
*  @desc			PHPLinter autoloader.
----------------------------------------------------------------------+
*  @file 			Lint_function.php
*  @author 			Jóhann T. Maríusson <jtm@robot.is>
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
namespace phplinter;
function PHPLinter_autoload($class) {
	$dir = dirname(__FILE__) . '/../';
	if(mb_strpos($class, '\\') !== false) {
		$parts = explode('\\', $class);
		$file = $dir . implode('/', $parts) . '.php';
		if(file_exists($file)) {
			require($file);
			return;
		}
	}
}
spl_autoload_register('phplinter\PHPLinter_autoload');