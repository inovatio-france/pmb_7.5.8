<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_node_input_integer.class.php,v 1.1.2.2 2023/12/20 08:57:01 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class interface_node_input_integer extends interface_node_input_text {
	
	protected $class = 'saisie-10em';
	
// 	protected $maxlength = 10;
	
}