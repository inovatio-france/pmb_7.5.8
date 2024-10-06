<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_query_proc_ui.class.php,v 1.1.4.2 2024/08/08 08:12:02 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_query_proc_ui extends list_query_ui {
	
    protected static $id_proc;
    
    protected static $proc;
    
    protected static $parameters;
    
    public static function get_parameters() {
        if (!isset(static::$parameters)) {
            static::$parameters = new parameters(static::$id_proc,"procs");
        }
        return static::$parameters;
    }
	public static function set_id_proc($id_proc) {
	    static::$id_proc = intval($id_proc);
	    $hp=static::get_parameters();
	    $hp->get_final_query();
	    static::$SQL_query = $hp->final_query;
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
	    $this->available_filters['main_fields'] = array();
	    $this->available_filters['custom_fields'] = array();
	    
	    $hp = static::get_parameters();
	    for ($i=0; $i<count($hp->query_parameters); $i++) {
	        $name=$hp->query_parameters[$i];
	        $this->available_filters['main_fields'] [$name] = $hp->get_field_alias($hp->parameters_description[$name]);
	    }
	}
	
	/**
	 * Affichage d'un filtre du formulaire de recherche
	 */
	public function get_search_filter_form($property, $label, $delete_is_allow=false) {
	    global $aff_list;
	    
	    $search_filter_form = "
				<div class='colonne3'>
					<div class='row'>";
	    
	    $hp = static::get_parameters();
	    //Affichage des champs
	    $champ_focus="";//nom du champ où l'on va mettre le focus
	    if (!empty($hp->query_parameters)) {
    	    for ($i=0; $i<count($hp->query_parameters); $i++) {
    	        $name=$hp->query_parameters[$i];
    	        if ($name == $property) {
    	            $search_filter_form .= "
    						<label class='etiquette'>".$label."</label>
    					</div>
    					<div class='row'>";
    	            $champ_type=$hp->get_field_type($hp->parameters_description[$name]);
    	            if(!$champ_focus && ($champ_type == "text")) {
    	                $champ_focus=$name;//en priorité le premier champ texte
    	            }
    	            eval("\$aff=".$aff_list[$champ_type]."(\$hp->parameters_description[\$name],\$check_scripts);");
    	            $search_filter_form .= $aff."
                        </div>
    				</div>";
    	        }
    	    }
	    }
	    return $search_filter_form;
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
	    $hp = static::get_parameters();
	    if (!empty($hp->query_parameters)) {
	        for ($i=0; $i<count($hp->query_parameters); $i++) {
	            $name=$hp->query_parameters[$i];
    	        global ${$name};
    	        $val=${$name};
    	        if (isset($val)) {
    	            $this->filters[$name] = $val;
    	        }
    	    }
	    }
	    $this->set_filter_from_form('notice_tpl');
	    parent::set_filters_from_form();
	}
	
	/**
	 * Affichage du formulaire d'options
	 */
	public function get_options_content_form() {
	    global $msg;
	    
	    $options_content_form = parent::get_options_content_form();
	    if(!isset($this->filters['notice_tpl'])) {
	        $this->filters['notice_tpl'] = 0;
	    }
	    $proc = static::get_proc();
	    $sel_notice_tpl=notice_tpl_gen::gen_tpl_select($this->objects_type."_notice_tpl",$this->filters['notice_tpl'],'',0,1);
	    if($proc->proc_notice_tpl_field && $sel_notice_tpl) {
	        $sel_notice_tpl= "
				<div class='".$this->objects_type."_notice_tpl_content'>
					<div class='colonne3'>
						<div class='row'>
							<label>".$msg['etatperso_export_notice']."</label>
						</div>
						<div class='row'>
							".$sel_notice_tpl."
						</div>
					</div>
				</div>";
	    }
	    $options_content_form .= $sel_notice_tpl;
	    return $options_content_form;
	}
	
	protected function init_default_settings() {
	    parent::init_default_settings();
	    $this->set_setting_display('search_form', 'options', true);
	    $this->set_setting_display('search_form', 'add_filters', false);
	}

	protected function _get_query_human_main_field($property, $label) {
	    if(!empty($this->filters[$property])) {
    	    $hp = static::get_parameters();
    	    if (!empty($hp->query_parameters)) {
    	        for ($i=0; $i<count($hp->query_parameters); $i++) {
    	            $name=$hp->query_parameters[$i];
    	            if ($name == $property) {
    	                $field = $hp->parameters_description[$name];
    	                $field_type = $hp->get_field_type($field);
    	                switch ($field_type) {
    	                    case 'query_list':
    	                        $query = $field['OPTIONS'][0]['QUERY'][0]['value'];
    	                        if (!empty($query)) {
    	                            $values = array();
    	                            $result = pmb_mysql_query($query);
    	                            while ($row=pmb_mysql_fetch_row($result)) {
    	                                if((is_array($this->filters[$property]) && in_array($row[0], $this->filters[$property])) || $this->filters[$property] == $row[0]) {
    	                                    $values[] = $row[1];
    	                                }
    	                            }
    	                            return $this->_get_label_query_human($label, $values);
    	                        } else {
    	                            return $this->_get_label_query_human($label, $this->filters[$property]);
    	                        }
    	                        break;
    	                    default:
    	                        return $this->_get_label_query_human($label, $this->filters[$property]);
    	                }
    	            }
	            }
	        }
	    }
	}
	
	public function get_export_icons() {
	    global $msg;
	    
	    if($this->get_setting('display', 'search_form', 'export_icons')) {
	        $export_icons = parent::get_export_icons();
	        $export_icons .= "&nbsp;&nbsp;<img  src='".get_url_icon('texte_ico.gif')."' style='border:0px' class='align_top' onMouseOver ='survol(this);' onclick=\"start_export('EXPORT_NOTI');\" alt='".$msg['etatperso_export_notice']."' title='".$msg['etatperso_export_notice']."'/>";
	        return $export_icons;
	    }
	    return "";
	}
	
	public function get_error_message_empty_list() {
	    global $msg, $charset;
	    return htmlentities($msg["etatperso_aucuneligne"], ENT_QUOTES, $charset);
	    return '';
	}
	
	protected static function get_proc() {
	    if (!isset(static::$proc)) {
    	    $query = "SELECT idproc, name, requete, comment, proc_notice_tpl, proc_notice_tpl_field FROM procs where idproc='".static::$id_proc."' ";
    	    $result = pmb_mysql_query($query);
    	    static::$proc = pmb_mysql_fetch_object($result);
	    }
	    return static::$proc;
	}
	
	protected function get_title() {
	    global $charset;
	    
	    $proc = static::get_proc();
	    return "<h1>".htmlentities($proc->name, ENT_QUOTES, $charset)."</h1><h2>".htmlentities($proc->comment, ENT_QUOTES, $charset)."</h2>";
	}
	
	protected function get_spreadsheet_title() {
	    return "Procedure_".static::$id_proc.".xls";
	}
	
	protected function get_display_spreadsheet_title() {
	    $proc = static::get_proc();
	    $this->spreadsheet->write_string(0,0,$proc->name);
	    $this->spreadsheet->write_string(0,1,$proc->comment);
	}
	
	protected function get_display_spreadsheet_cell($object, $property, $row, $col) {
	    $value = strip_tags($this->get_cell_content($object, $property));
	    if (is_numeric($col)) {
	        $this->spreadsheet->write($row,$col, $value);
	    } else {
	        $this->spreadsheet->write_string($row,$col, $value);
	    }
	}
	
	protected function get_html_title() {
	    $proc = static::get_proc();
	    return "<h1>".$proc->name."</h1><h2>".$proc->comment."</h2>".static::$SQL_query."<br />";;
	}
	
	/**
	 * Header de la liste du tableau
	 */
	protected function get_display_html_header_list() {
	    $display = '<tr>';
	    foreach ($this->columns as $column) {
	        if(!empty($column['exportable']) && !empty($this->get_setting('columns', $column['property'], 'exportable'))) {
	            $display .= "<th class='align_left'>".$this->_get_label_cell_header($column['label'])."</th>";
	        }
	    }
	    $display .= '</tr>';
	    
	    return $display;
	}
	
	protected function get_display_html_cell($object, $property) {
	    $value = strip_tags($this->get_cell_content($object, $property));
	    if (is_numeric($value)){
	        $value = "'".$value ;
	    }
	    if(trim($value)=='') {
	        $value = "&nbsp;";
	    }
	    $display = "<td>".$value."</td>";
	    return $display;
	}
	
	public static function get_controller_url_base() {
	    global $force_exec;
	    
	    return parent::get_controller_url_base()."&action=execute&id_proc=".static::$id_proc."&form_type=gen_form&force_exec=".$force_exec;
	}
	
	public static function get_ajax_controller_url_base() {
	    global $force_exec;
	    
	    return parent::get_ajax_controller_url_base()."&id_proc=".static::$id_proc."&form_type=gen_form&force_exec=".$force_exec;
	}
}