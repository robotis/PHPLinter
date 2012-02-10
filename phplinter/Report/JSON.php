<?php
/**
----------------------------------------------------------------------+
*  @desc			JSON Reporter
*  @file 			JSON.php
*  @author 			Jóhann T. Maríusson <jtm@robot.is>
*  @since 		    Feb 6, 2012
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
namespace phplinter\Report;
class JSON extends Base {
	/**
	----------------------------------------------------------------------+
	* @desc 	Prepare JSON report
	----------------------------------------------------------------------+
	*/
	public function prepare() {
		$this->json = $this->config->check('report');
		if(isset($this->json['dry_run']))
			$this->dry_run = $this->json['dry_run'];
		return true;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Create JSON report
	* @param	Array	Lint report
	* @param	Mixed	Penalty
	* @param	String	Root directory or filename
	----------------------------------------------------------------------+
	*/
	public function create($report, $penaltys=null, $root=null) {
		$out = array();
		if(count($report) === 1) {
			$out[$root] = array(
				'report' => $this->_parse($report[0]),
				'score' => SCORE_FULL + $penaltys
			);
		} else {
			foreach($report as $file => $_) {
				$parts = explode('/', trim($file, './'));
				$name = array_pop($parts);
				$this->_insert($out, $parts, $name, array(
					'report' => $this->_parse($_),
					'score' => SCORE_FULL + $penaltys[$file]
				));
			}
		}
		$this->_out(json_encode($out));
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Parse file report
	* @param	Array	Resport array
	* @return   Array	output
	----------------------------------------------------------------------+
	*/
	protected function _parse($report) {
		$out = array();
		foreach($report as $_) {
			$t = array(
				'message' => $_['message'],
				'line' => $_['line'],
				'flag' => $_['flag']
			);
			if($_['where'] != 'commment') {
				$t['where'] = $_['where'];
			}
			$out[] = $t;
		}
		return $out;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	* @param	FIXME
	----------------------------------------------------------------------+
	*/
	protected function _out($out) {
		if(isset($this->json['out'])) {
			if(is_dir($this->json['out'])) {
				$filename = rtrim('/', $this->json['out']) . '/phplinter.report.json';
			} else {
				$filename = $this->json['out'];
			}
			$this->write($filename, $out);
		} else {
			echo $out;
		}
	}
}
