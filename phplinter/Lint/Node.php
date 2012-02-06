<?php
/**
----------------------------------------------------------------------+
*  @desc			Parse Node
*  @file 			Node.php
*  @author 			Jóhann T. Maríusson <jtm@robot.is>
*  @since 		    Feb 6, 2012
*  @package 		phplinter
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
namespace phplinter\Lint;
class Node {
	/**
	----------------------------------------------------------------------+
	* @desc 	Create blank node
	----------------------------------------------------------------------+
	*/
	public function __construct() {
		$this->visibility 	= null;
		$this->abstract 	= null;
		$this->static 		= null;
		$this->extends 		= null;
		$this->implements 	= null;
		$this->namespace 	= null;
		$this->arguments 	= null;
		$this->type 		= null;
		$this->file	 		= null;
		$this->parent		= null;
		$this->name 		= null;
		$this->owner 		= null;
		$this->depth 		= null;
		$this->end_line 	= null;
		$this->length 		= null;
		$this->token_count 	= null;
		
		$this->comments 	= array();
		$this->tokens 		= array();
		$this->nodes 		= array();
	}
}