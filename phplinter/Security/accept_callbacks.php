<?php
return array(
//	Function                     => Position of callback arguments
	'ob_start'                   => array(0),
	'array_diff_uassoc'          => array(-1),
	'array_diff_ukey'            => array(-1),
	'array_filter'               => array(1),
	'array_intersect_uassoc'     => array(-1),
	'array_intersect_ukey'       => array(-1),
	'array_map'                  => array(0),
	'array_reduce'               => array(1),
	'array_udiff_assoc'          => array(-1),
	'array_udiff_uassoc'         => array(-1, -2),
	'array_udiff'                => array(-1),
	'array_uintersect_assoc'     => array(-1),
	'array_uintersect_uassoc'    => array(-1, -2),
	'array_uintersect'           => array(-1),
	'array_walk_recursive'       => array(1),
	'array_walk'                 => array(1),
	'uasort'                     => array(1),
	'uksort'                     => array(1),
	'usort'                      => array(1),
	'preg_replace_callback'      => array(1),
	'spl_autoload_register'      => array(0),
	'iterator_apply'             => array(1),
	'call_user_func'             => array(0),
	'call_user_func_array'       => array(0),
	'register_shutdown_function' => array(0),
	'register_tick_function'     => array(0),
	'set_error_handler'          => array(0),
	'set_exception_handler'      => array(0),
	'session_set_save_handler'   => array(0, 1, 2, 3, 4, 5),
);