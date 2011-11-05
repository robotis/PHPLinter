<?php
/**
----------------------------------------------------------------------+
*  @desc			Report generator
*  @file 			Report.php
*  @author 			Jóhann T. Maríusson <jtm@robot.is>
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
namespace PHPLinter;
class Report {
	/* @var String */
	protected $output_dir;
	/* @var int */
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
	* @param	$penalty	Float
	----------------------------------------------------------------------+
	*/
	public function score($penalty, $num=null) {
		if($penalty === false)
			return "No score available\n";
		$full = (is_numeric($num))
			? ($num * SCORE_FULL) : SCORE_FULL;
		return sprintf("Score: %.2f out of %.2f\n", 
					  ($full + $penalty), $full);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Derive final score for one file
	* @param	$penalty	Float
	----------------------------------------------------------------------+
	*/
	public function average($penalty, $num) {
		$full = $num * SCORE_FULL;
		return sprintf("Average score: %.2f\n", 
					 (($full + $penalty) / $num));
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Color format for bash shell.
	* 			colors = black/red/green/brown
	* 					 blue/purple/cyan/white
	* 			attrs = 1-8
	* @param	String
	* @param	String
	* @param	Mixed		color OR array(attr, color)
	* @param	Bool
	* @param	Int
	* @return 	String
	----------------------------------------------------------------------+
	*/
	public function color($msg, $color="black", $nl=false) {
		if($this->options & OPT_USE_COLOR) {
			$attr = 0;
			if(is_array($color)) {
				$attr = intval($color[0]);
				$color = $color[1];
			}
			$tpl = "\033[%d;%dm%s\033[0m";
			if($nl) $tpl .= "\n";
			$codes = array(
				'black' => 30, 'red' => 31, 'green' => 32,
				'brown' => 33, 'blue' => 34, 'purple' => 35,
				'cyan' => 36, 'white' => 37,
			);
			if(!isset($codes[$color])) $color = 'black';
			return sprintf($tpl, $attr, $codes[$color], $msg);
		}
		return $msg;
	} 
	/**
	----------------------------------------------------------------------+
	* @desc 	CLI report
	* @param	$report		Array
	----------------------------------------------------------------------+
	*/
	public function toCli($report, $format="| {F} | {M} | `{W}` Line: {L}\n") {
		$fcolors = array(
			'E' => 'red', 'W' => 'blue', 'C' =>'brown', 'D' =>array(1,'brown'),
			'I' => 'green', 'R' => 'purple', 'S' => 'cyan'
		);
		foreach($report as $_) {
			$out = str_replace('{F}', $this->color(str_pad($_['flag'], 3), 
							   $fcolors[$_['flag'][0]]), $format);
			$out = str_replace('{M}', str_pad($_['message'], 50), $out);
			$out = str_replace('{W}', $_['where'], $out);
			$out = str_replace('{L}', $_['line'], $out);
			echo $out;
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	HTML Report
	* @param	$report		Array
	* @param	$penaltys	Array
	----------------------------------------------------------------------+
	*/
	public function toHtml($root, $report, $penaltys) {
		$this->root = realpath($root);
		if(file_exists($this->output_dir)) {
			$output_dir = realpath($this->output_dir);
			if(!($this->options & OPT_OVERWRITE_REPORT)) {
				die("`$output_dir` not empty, aborting...\n");
			}
			if($this->options & OPT_VERBOSE) 
				echo "Emptying `$output_dir`\n";
			Path::del_recursive($this->output_dir);
		}
		if($this->options & OPT_VERBOSE) 
			echo "Creating `$this->output_dir`\n";
		if(!file_exists($this->output_dir) && !mkdir($this->output_dir, 0775)) {
			die("Unable to create `$this->output_dir`...\n");
		}
		$this->output_dir = realpath($this->output_dir);
		Path::write_file($this->output_dir . '/html_report.css', $this->css());
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
	* @return	String
	----------------------------------------------------------------------+
	*/
	protected function css() {
		return file_get_contents(dirname(__FILE__) . '/../html_report.css');
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Create index files for report
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
			uksort($dirs, function($a, $b) {
				return strtolower($a) > strtolower($b);
			});
		}
		if(!empty($files)) {
			foreach($files as $_) $arr[] = $_['sort'];
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
	* @param	$content	HTML
	* @param	$depth		int
	* @return	HTML
	----------------------------------------------------------------------+
	*/
	protected function html($content, $depth=0) {
		$r = str_pad('', ($depth-1)*3, '../');
		$out = "<!DOCTYPE html>\n";
		$out .= "<html>\n";
		$out .= "<head>\n";
		$out .= '<link rel="stylesheet" type="text/css" href="'.$r.'html_report.css"/>';
		$out .= "</head>\n";
		$out .= "<body>\n";
		$out .= $content;
		$out .= "</body>\n";
		$out .= '</html>';
		return $out;
	}
}