<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_faq_ui.class.php,v 1.1 2021/01/13 11:19:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_configuration_faq_ui extends list_configuration_ui {
		
	public function __construct($filters=array(), $pager=array(), $applied_sort=array()) {
		static::$module = 'admin';
		static::$categ = 'faq';
		static::$sub = str_replace(array('list_configuration_faq_', '_ui'), '', static::class);
		parent::__construct($filters, $pager, $applied_sort);
	}
}