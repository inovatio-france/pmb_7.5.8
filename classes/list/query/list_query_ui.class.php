<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_query_ui.class.php,v 1.1.4.2 2024/08/08 08:12:02 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_query_ui extends list_ui {
	
    protected static $SQL_query;
    
    protected static $formated_columns;
    
    public static function set_SQL_query($SQL_query) {
        static::$SQL_query = $SQL_query;
    }
    
    public function __construct($filters=array(), $pager=array(), $applied_sort=array()) {
        if(empty($this->objects_type)) {
            $this->objects_type = str_replace('list_', '', get_class($this)).'_'.md5(static::$SQL_query);
        }
        parent::__construct($filters, $pager, $applied_sort);
    }
    
    protected function _get_query_base() {
        $SQL_query = static::$SQL_query;
        //on définit les limites
        $last_limit_position = strripos($SQL_query, ' LIMIT ');
        if($last_limit_position !== false) {
            $last_parenthesis_position = strripos($SQL_query, ')');
            //le dernier limit ne doit pas être dans une sous-requête
            if($last_parenthesis_position === false || $last_parenthesis_position < $last_limit_position) {
                $SQL_query = substr($SQL_query, 0, $last_limit_position);
            }
        }
        $SQL_array = array();
        preg_match("/(.+)(order by.+)$/isU", $SQL_query, $SQL_array);
        if(count($SQL_array)) {
            $SQL_query = $SQL_array[1];
        }
        return $SQL_query;
    }
    
    /**
	 * Champ(s) du tri SQL
	 */
	protected function _get_query_field_order($sort_by) {
	    return static::$formated_columns[$sort_by];
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('default', 'align', 'left');
	}
	
	protected function init_default_selected_filters() {
	    if (!empty($this->available_filters['main_fields'])) {
	        foreach ($this->available_filters['main_fields'] as $property=>$label) {
	            $this->add_selected_filter($property, $label);
	        }
	    }
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $arraySql=array();
// 		preg_match('`^(.+)( order by .+)$`i',$sql,$arraySql);
	    preg_match("/(.+)(order by.+)$/isU", static::$SQL_query, $arraySql);
	    if(count($arraySql) && !empty($arraySql[2])) {
	        $order_by = trim(str_replace('order by', '', pmb_strtolower($arraySql[2])));
	        $sorts = explode(',', $order_by);
	        foreach ($sorts as $sort) {
	            $sort = trim($sort);
	            $exploded_sort = explode(' ', $sort);
	            $sort_by = trim($exploded_sort[0]);
	            $sort_asc_desc = (!empty($exploded_sort[1]) ? $exploded_sort[1] : 'asc');
	            if(is_numeric($sort_by)) {
	                $i=1;
	                foreach ($this->available_columns['main_fields'] as $property=>$label) {
	                    if($i == $sort_by) {
	                        $this->add_applied_sort($property, $sort_asc_desc);
	                    }
	                    $i++;
	                }
	            } else {
	                $this->add_applied_sort(static::format_query_property($sort_by));
	            }
	        }
	    } else {
	       $this->add_applied_sort(array_key_first($this->available_columns['main_fields']));
	    }
	}
	
	/**
	 * Initialisation de la pagination par défaut
	 */
	protected function init_default_pager() {
	    parent::init_default_pager();
	    $this->pager['nb_per_page'] = 25;
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		parent::set_filters_from_form();
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
	    $this->available_columns['main_fields'] = array();
	    $result = pmb_mysql_query($this->_get_query());
		$nbr_champs = pmb_mysql_num_fields($result);
		for($i=0; $i < $nbr_champs; $i++) {
		    $field_name = pmb_mysql_field_name($result, $i);
		    $this->available_columns['main_fields'][static::format_query_property($field_name)] = $field_name;
		}
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
	    if (!empty($this->available_columns['main_fields'])) {
	        foreach ($this->available_columns['main_fields'] as $property=>$label) {
	            $this->add_column($property, $label);
	        }
	    }
	}
	
	/**
	 * Contenu d'une colonne
	 * @param object $object
	 * @param string $property
	 */
	protected function get_cell_content($object, $property) {
	    return parent::get_cell_content($object, static::$formated_columns[$property]);
	}
	
	public static function format_query_property($property) {
	    $formated_column = str_replace(' ', '_', strip_empty_chars(convert_diacrit($property)));
	    if(!isset(static::$formated_columns[$formated_column])) {
	        static::$formated_columns[$formated_column] = $property;
	    }
	    return $formated_column;
	}
	
}