<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_animationslist.class.php,v 1.1 2021/03/26 13:47:45 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_animationslist extends cms_module_common_module {
	
	public function __construct($id = 0) {
		$this->module_path = str_replace(basename(__FILE__), "", __FILE__);
		parent::__construct($id);
	}
}