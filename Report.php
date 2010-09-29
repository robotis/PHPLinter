<?php
/**
----------------------------------------------------------------------+
*  @desc			Report generator
*  @file 			Report.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
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
*
----------------------------------------------------------------------+
*/
class Report {
	protected $output_dir;
	protected $options;
	/**
	----------------------------------------------------------------------+
	* @desc 	Create report
	* @param	$output_dir	String (Path)
	----------------------------------------------------------------------+
	*/
	public function __construct($output_dir=null, $OPTIONS=null) {
		if(empty($output_dir)) {
			$this->output_dir = dirname(__FILE__) . '/' . 'html_report/';
		} else {
			$this->output_dir = $output_dir;
		}
		$this->options = $OPTIONS;
	} 
	/**
	----------------------------------------------------------------------+
	* @desc 	Derive final score for one file
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$penalty	Float
	----------------------------------------------------------------------+
	*/
	public function score($penalty, $num=null) {
		$full = (is_numeric($num))
			? ($num * SCORE_FULL) : SCORE_FULL;
		echo sprintf("Score: %.2f out of %.2f\n", 
					($full + $penalty), $full);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Derive final score for one file
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$penalty	Float
	----------------------------------------------------------------------+
	*/
	public function average($penalty, $num) {
		$full = $num * SCORE_FULL;
		echo sprintf("Average: %.2f\n", 
					 (($full + $penalty) / $num));
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	CLI report
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$report		Array
	----------------------------------------------------------------------+
	*/
	public function toCli($report) {
		foreach($report as $_) {
			echo "| {$_['flag']} | {$_['message']}\t| ";
			echo "`{$_['where']}` Line: {$_['line']}\n";
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	HTML Report
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$report		Array
	* @param	$penaltys	Array
	----------------------------------------------------------------------+
	*/
	public function toHtml($root, $report, $penaltys) {
		$this->root = realpath($root);
		if(file_exists($this->output_dir)) {
			if($this->options & OPT_VERBOSE) 
				echo "Deleting `$this->output_dir`\n";
			Path::del_recursive($this->output_dir);
		}
		if($this->options & OPT_VERBOSE) 
			echo "Creating `$this->output_dir`\n";
		if(!file_exists($this->output_dir) 
			&& !mkdir($this->output_dir, 0775)) {
			die("Unable to create `$this->output_dir`...\n");
		}
		$this->output_dir = realpath($this->output_dir);

		foreach($report as $file => $rep) {
			$out = '<div class="wrapper"><table border="1" cellpadding="0" cellspacing="0">';
			$content = '';
			
			foreach($rep as $_) {
				$content .= '<tr>';
				$content .= '<td align="center" class="fl_';
				$content .= $_['flag'][0].'">'.$_['flag'].'</td>';
				$content .= '<td class="message">'.$_['message'].'</td>';
				$content .= "<td class=\"where\">'`{$_['where']}` Line: {$_['line']}</td>\n";
				$content .= '</tr>';
			}
			$out .= '<tr>';
			$score = (SCORE_FULL + $penaltys[$file]);
			$class = $this->get_score_class($score);
			$out .= '<td colspan="2" align="center" class="'.$class.'">';
			$out .= sprintf('Score: %.2f', $score);
			$out .= '</td>';
			$parts = explode('/', $file);
			$rfile = array_pop($parts);
			$depth = count($parts);
			$path = $depth > 1
				? implode('/', $parts)
				: '';
			$path = substr(realpath($path), strlen($this->root));
			$out .= '<td class="filename">';
			$out .= "$path/$rfile";
			$out .= '</td></tr>';
			$out .= $content;
			$out .= '</table></div>';
			
			$dir = $this->output_dir . $path;
			if(!file_exists($dir) 
				&& !mkdir($dir, 0775, true)) {
				die("Unable to create `$dir`...\n");
			}
			
			$pp = explode('/', substr(realpath(implode('/', $parts)), strlen($this->root)));
			$ofile = $dir . '/' . strtr($rfile, './', '__').'.html';
			if($this->options & OPT_VERBOSE) 
				echo "Wrote to file `$ofile`\n";
			Path::write_file($ofile, $this->html($out, count($pp)));
			$url['file'] = $file;
			$url['url'] = strtr($rfile, './', '__').'.html';
			$url['sort'] = strtolower($url['url']);
			$this->parts($pp, $url, $urls);
		}
		$urls = $this->sort($urls);
		$this->output_indexes($urls, $penaltys);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Create index files for report
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$urls		Array
	* @param	$penaltys	Array
	* @param	$path		String
	* @param	$depth		int
	* @return	Array
	----------------------------------------------------------------------+
	*/
	protected function output_indexes($urls, $penaltys, $path='', $depth=0) {
		$out = '<div class="wrapper"><table border="1" cellpadding="0" cellspacing="0">';
		$out .= '<tr><td align="center">'.date("d / M / Y").'</td>';
		$out .= '<td colspan="2" align="center">'.$this->root.'</td></tr>';
		$content = '';
		$total = 0; $num = 0;

		foreach($urls as $k => $_) {
			$content .= '<tr>';
			if(isset($_['file'])) {
				$score = (SCORE_FULL + $penaltys[$_['file']]);
				$total += $score;
				$num++;
				$class = $this->get_score_class($score);
				$content .= '<td class="'.$class.'">'.sprintf('%.2f', $score).'</td>';
				$limit = $score == 10 ? 'perfect' : 'limit';
				$content .= '<td class="'.$limit.'">'.sprintf('%.2f', SCORE_FULL).'</td>';
				$content .= '<td><a href="'.$_['url'].'">'.substr(realpath($_['file']), 
																  strlen($this->root . $path))
							. '</a></td>';
			} else {
				list($ototal, $onum) = $this->output_indexes($urls[$k], $penaltys, 
															 $path . $k.'/', $depth+1);
				$avarage = ($ototal / $onum);
				$class = $this->get_score_class($avarage);
				$content .= sprintf('<td colspan="2" class="%s">Average: %.2f</td>',
								$class, $avarage);
				$content .= '<td class="folder"><a href="'.$k.'">'.$k.'</a></td>';
				$total += $ototal;
				$num += $onum;
			}
			$content .= '</tr>';
		}
		$out .= '<tr>';
		$avarage = ($total / $num);
		$class = $this->get_score_class($avarage);
		$out .= sprintf('<td colspan="2" align="center" class="%s">Average: %.2f</td>'
			,$class, $avarage);
		$out .= '<td>'.$path.'</td>';
		$out .= '</tr>';
		$out .= $content;
		$out .= '</table></div>';

		$path = ($path == '/')
			? $this->output_dir
			: $path;
		$dir = ($path == $this->output_dir) 
			? $path
			: $this->output_dir . $path;
		$dir = (!empty($dir) && $dir[strlen($dir)-1] == '/')
			? $dir : $dir . '/';
		if(!empty($path)) {
			$file = $dir . 'index.html';
			Path::write_file($file, $this->html($out, $depth));
			if($this->options & OPT_VERBOSE) 
				echo "Wrote to file `$file`\n";
		}
		$this->dirs[] = $dir;
		return array($total, $num);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	CSS Class for score
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$score	float
	* @return	String
	----------------------------------------------------------------------+
	*/
	protected function get_score_class($score) {
		if($score < 0)
			return 'terrible';
		elseif($score < 5)
			return 'bad';
		elseif($score < 7)
			return 'average';
		elseif($score < 9)
			return 'good';
		elseif($score < 10)
			return 'vgood';
		return 'perfect';
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Fills in the correct directorys
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$parts	Array
	* @param	$url	Array
	* @param	$urls	Reference (Array)
	----------------------------------------------------------------------+
	*/
	protected function parts($parts, $url, &$urls) {
		if(empty($parts)) return;
		$part = array_shift($parts);
		
		if(!isset($urls[$part])) {
			$urls[$part] = array();
		}
		
		if(empty($parts)) {
			$urls[$part][] = $url;
		} else $this->parts($parts, $url, $urls[$part]);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Sort the result array
	* @param	Array
	* @return	Array
	----------------------------------------------------------------------+
	*/
	protected function sort($urls) {
		$files = array();
		$dirs = array();
		foreach($urls as $k => $_) {
			if(isset($_['sort']))
				$files[] = $_;
			else $dirs[$k] = $_;
		}
		if(!empty($dirs)) {
			if(!function_exists("isort")) {
				function isort($a,$b) {
					return strtolower($a)>strtolower($b);
				}
			}
			uksort($dirs, "isort");
		}
		if(!empty($files)) {
			$arr = Set::column($files, 'sort');
			array_multisort($files, SORT_ASC, $arr);
		}
		$urls = array_merge($dirs, $files);
		foreach($urls as $k => $_) {
			if(!is_numeric($k)) {
				$urls[$k] = $this->sort($urls[$k]);
			}
		}
		return $urls;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Create HTML report
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$content	HTML
	* @param	$depth		int
	* @return	HTML
	----------------------------------------------------------------------+
	*/
	protected function html($content, $depth=0) {
		$r = $depth > 0 ? str_pad('', $depth*3, '../') : '../';
		$out = '<!DOCTYPE html>';
		$out .= '<html>';
		$out .= '<head>';
		$out .= '<link rel="stylesheet" type="text/css" href="'.$r.'style.css"/>';
		$out .= '</head>';
		$out .= '<body>';
		$out .= $content;
		$out .= '</body>';
		$out .= '</html>';
		return $out;
	}
}