<?php
namespace phplinter\Report;
class Html extends Base {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function prepare() {
		$this->html = $this->config->check('html');
		$html = $this->config->check('html');
		if(empty($html['out'])) {
			return 'No output directory selected...';
		}
		if($html['out'] == $this->config->check('target')) {
			return 'Output directory same as target, aborting...';
		}
		if(file_exists($html['out'])
			&& !($this->config->check(OPT_OVERWRITE_REPORT)))
		{
			return 'Output directory not empty, will not overwrite...';
		}
		if(isset($this->html['dry_run']))
			$this->dry_run = $this->html['dry_run'];
		return true;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function create($report, $penaltys=null, $root=null) {
		$this->root = realpath($root);
		$output_dir = realpath($this->html['out']);
		if(!$output_dir) $output_dir = $this->html['out'];
		if($this->config->check(OPT_VERBOSE))
			echo "Generating HTML Report to '$output_dir'\n";
		
		if(file_exists($output_dir)) {
			if(!($this->config->check(OPT_OVERWRITE_REPORT))) {
				die("`$output_dir` not empty, aborting...\n");
			}
			if($this->config->check(OPT_VERBOSE)) 
				echo "Emptying `$output_dir`\n";
			if(!$this->dry_run)
				\phplinter\Path::del_recursive($output_dir);
		}
		
		if(!file_exists($output_dir)) { 
			$this->mkdir($output_dir, 0775);
		}
		
		$this->write($output_dir . '/html_report.css', $this->css());
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
			
			$dir = $output_dir . $path;
			if(!file_exists($dir)) {
				$this->mkdir($dir, 0775, true);
			}
			
			$pp = explode('/', substr(realpath(implode('/', $parts)), strlen($this->root)));
			$ofile = $dir . '/' . strtr($rfile, './', '__').'.html';
			$this->write($ofile, $this->_html($out, count($pp)));
			$url['phplinter___file'] = $file;
			$url['phplinter___url'] = strtr($rfile, './', '__').'.html';
			$url['phplinter___sort'] = strtolower($url['phplinter___url']);
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
		return file_get_contents(dirname(__FILE__) . '/html_report.css');
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
			if(isset($_['phplinter___file'])) {
				$score = (SCORE_FULL + $penaltys[$_['phplinter___file']]);
				$total += $score;
				$num++;
				$class = $this->get_score_class($score);
				$content .= '<td class="'.$class.'">'.sprintf('%.2f', $score).'</td>';
				$limit = $score == 10 ? 'perfect' : 'limit';
				$content .= '<td class="'.$limit.'">'.sprintf('%.2f', SCORE_FULL).'</td>';
				$content .= '<td><a href="'.$_['phplinter___url'].'">'.substr(realpath($_['phplinter___file']),
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
						,$class
						,$avarage);
		$out .= '<td>'.$path.'</td>';
		$out .= '</tr>';
		$out .= $content;
		$out .= '</table></div>';
	
		$path = ($path == '/')
			? $this->html['out']
			: $path;
		$dir = ($path == $this->html['out'])
			? $path
			: $this->html['out'] . $path;
		$dir = (!empty($dir) && $dir[strlen($dir)-1] == '/')
				? $dir : $dir . '/';
		if(!empty($path)) {
			$file = $dir . 'index.html';
			$this->write($file, $this->_html($out, $depth));
		}
		$this->dirs[] = $dir;
		return array($total, $num);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Create HTML report
	* @param	$content	HTML
	* @param	$depth		int
	* @return	HTML
	----------------------------------------------------------------------+
	*/
	protected function _html($content, $depth=0) {
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
			if(isset($_['phplinter___sort']))
			$files[] = $_;
			else $dirs[$k] = $_;
		}
		if(!empty($dirs)) {
			uksort($dirs, function($a, $b) {
				return strtolower($a) > strtolower($b);
			});
		}
		if(!empty($files)) {
			foreach($files as $_) $arr[] = $_['phplinter___sort'];
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
}