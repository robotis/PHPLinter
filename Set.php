<?php
/**
----------------------------------------------------------------------+
*  @desc			Set. Array functions
----------------------------------------------------------------------+
*  @file 			Set.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @package 		Set
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
class Set {
	/**
	----------------------------------------------------------------------+
	* @desc 	Is neddle in array column
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$array		Array
	* @param	$needle		Mixed
	* @param	$key		Mixed
	* @return	Bool
	----------------------------------------------------------------------+
	*/
	public static function inColumn($array, $needle, $key) {
		if(!is_array($array)) return false;
		return in_array($needle, self::column($array, $key));
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	True if array is associative.
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$array		Array
	* @return	Bool
	----------------------------------------------------------------------+
	*/
	public static function isAssoc($array) {
		if(!is_array($array) || empty($array)) return false;
    	return array_keys($array) !== range(0, count($array) - 1);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Extract Key => value array from $array
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$array		Array
	* @param	$key		String
	* @param	$value		String
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public static function toSelect($array, $key, $value) {
		if(is_array($array)) {
			foreach($array as $_) {
				$out[$_[$key]] = $_[$value];
			}
		} 
		return isset($out) ? $out : array();
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Key indexed array
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$array		Array
	* @param	$key		String
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public static function keyArray($array, $key = 'id') {
		if(is_array($array)) {
			foreach($array as $_) 
				if(isset($_[$key]))
					$out[$_[$key]] = $_;
		}
		return isset($out) ? $out : array();
	} 
	/**
	----------------------------------------------------------------------+
	* @desc 	Validate that all $keys exist in $array
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$array	Array
	* @param	$keys	Mixed
	* @return 	Bool
	----------------------------------------------------------------------+
	*/
	public static function keysExist($array, $keys) {
		if(!is_array($array)) 
			return false;
		if(empty($keys))
			return false;
		if(!is_array($keys)) 
			return array_key_exists($keys, $array);
		foreach($keys as $_)
			if(!array_key_exists($_, $array))
				return false;
		return true;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Extract column from Array
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$array	Array
	* @param	$key	Mixed
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public static function column($array, $key) {
		if(is_array($array)) {
			foreach($array as $_) 
				if(isset($_[$key]))
					$out[] = $_[$key];
		}
		return isset($out) ? $out : array();
	}
	/**
	----------------------------------------------------------------------+
	* @desc		Merge many into one. Takes any number of arguments, of any 
	* 			type, and merges them into one array.
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	Mixed
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public static function merge() {
		$out = array();
		foreach(func_get_args() as $_) {
			if(is_array($_))
				$out = array_merge($out, $_);
			else $out[] = $_;
		}
		return $out;
	}
	/**
	----------------------------------------------------------------------+
	* @desc		Find the FIRST key(index) of $value in $array
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$array	Array
	* @param	$value	Mixed
	* @return 	Mixed
	----------------------------------------------------------------------+
	*/
	public static function indexOf($array, $value) {
		if(is_array($array)) {
			foreach($array as $k=>$v)
				if($v === $value) return $k;
		}
		return false;
	}
	/**
	----------------------------------------------------------------------+
	* @desc		Clean all "empty" values from array
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$array	Array
	* @return 	Array
	----------------------------------------------------------------------+
	*/
	public static function clean($array) {
		if(is_array($array)) {
			foreach($array as $_) 
				if(!empty($_))
					$out[] = $_;
		}
		return isset($out) ? $out : array();
    }
    /**
    ----------------------------------------------------------------------+
    * @desc 	Compute the numeric difference between the same column in 
    * 			two arrays.
    * @author 	Jóhann T. Maríusson <jtm@hi.is>
    * @param	$array		Array
    * @param	$array1		Array
    * @param	$on			Mixed
    * @param	$diff		Mixed
    * @return	Array
    ----------------------------------------------------------------------+
    */
	public static function diffColumns($array, $array1, $on, $diff) {
		if(!(is_array($array) && is_array($array1))) return array();
		$c1 = count($array);
		$c2 = count($array1);
		if($c1 > $c2) {
			// $array is longer
			foreach($array as $_) {
				foreach($array1 as $_1) {
					if($_[$on] == $_1[$on]) {
						$out[$_[$on]] = $_1[$diff] - $_[$diff];
						continue 2;
					}
				}
				$out[$_[$on]] = -$_[$diff];
			}
		} else {
			// $array is longer or both are equal
			foreach($array1 as $_) {
				foreach($array as $_1) {
					if($_[$on] == $_1[$on]) {
						$out[$_[$on]] = $_[$diff] - $_1[$diff];
						continue 2;
					}
				}
				$out[$_[$on]] = $_[$diff];
			}
			
		}
		return isset($out) ? $out : array();
	}
    /**
     * @desc    Merge array1's values with array1's.
     *
     * @TODO:   Memory management.  I can do the same thing with 50% of the 
     *          memory allotted by this function
     *
     * @param   $array0 Array
     * @param   $array1 Array
     *
     * @return  $array Array
     * @author Helgi Möller
     **/
    public static function softMerge($array0, $array1) {
        if (is_null($array1)) return $array0;
        if (is_null($array0)) return $array1;

        $out = array();
        if (!is_array($array1)) {
            if (is_array($array0))
                return ($array0[] = $array1);
            else  
                if (is_numeric($array1) && is_numeric($array0))
                    return $array1 + $array0;
                else
                    return $array1;
        } else {
            if (!is_array($array0)) 
                return ($array1[] = $array0);
            // Both params are arrays:
            foreach($array1 as $k => $val)  {
                if (array_key_exists($k, $array0))  
                    $out[$k] = self::softMerge($val, $array0[$k]);
                else
                    $out[$k] = $array1[$k];
            }
        }
        return array_merge($array0, $out);
    }
    /**
     * Recursive array comparison
     *
     * @return  Array of difference [empty array on no difference]
     * @return  NULL upon bad parameter values          
     * @author 55.php@imars.com 
     *         php.notes@dwarven.co.uk
     *         URL: http://is.php.net/manual/en/function.array-diff-assoc.php
     **/
    public static function compare($array1, $array2) { 
        $diff = array(); 
        if (!is_array($array1)) return NULL;
        if (!is_array($array2)) return NULL;

        // Left-to-right 
      	foreach ($array1 as $key => $value) { 
            if (!array_key_exists($key,$array2)) { 
                $diff[0][$key] = $value; 
            } elseif (is_array($value)) { 
                 if (!is_array($array2[$key])) { 
                        $diff[0][$key] = $value; 
                        $diff[1][$key] = $array2[$key]; 
                 } else { 
                        $new = self::compare($value, $array2[$key]); 
                        if ($new !== false) { 
                             if (isset($new[0])) $diff[0][$key] = $new[0]; 
                             if (isset($new[1])) $diff[1][$key] = $new[1]; 
                        }; 
                 }; 
            } elseif ($array2[$key] !== $value) { 
                 $diff[0][$key] = $value; 
                 $diff[1][$key] = $array2[$key]; 
            }; 
	     }; 
	     // Right-to-left 
	     foreach ($array2 as $key => $value) { 
	            if (!array_key_exists($key,$array1)) { 
	                 $diff[1][$key] = $value; 
	            }; 
	            // No direct comparsion because matching keys were compared in the 
	            // left-to-right loop earlier, recursively. 
	     }; 
	     return $diff; 
    }
}
?>