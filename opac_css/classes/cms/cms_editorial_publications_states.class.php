<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_editorial_publications_states.class.php,v 1.9 2021/03/03 07:57:00 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/cms/cms_editorial_publications_states.tpl.php");
require_once($class_path."/cms/cms_cache.class.php");

class cms_editorial_publications_states {
	public $publications_states;	//tableau des statuts de publication
	
	public function __construct(){
		$this->publications_states = array();
	}
	
	protected function fetch_data_cache(){
		if($tmp=cms_cache::get_at_cms_cache($this)){
			$this->restore($tmp);
		}else{
			$this->fetch_data();
			cms_cache::set_at_cms_cache($this);
		}
	}
	
	protected function restore($cms_object){
		foreach(get_object_vars($cms_object) as $propertieName=>$propertieValue){
			$this->{$propertieName}=$propertieValue;
		}
	}

	protected function fetch_data(){
		$rqt = "select * from cms_editorial_publications_states order by editorial_publication_state_label asc";
		$res = pmb_mysql_query($rqt);
		if(pmb_mysql_num_rows($res)){
			while($row = pmb_mysql_fetch_object($res)){
				$this->publications_states[] =array(
					'id' => $row->id_publication_state,
					'label' => $row->editorial_publication_state_label,
					'opac_show' => $row->editorial_publication_state_opac_show,
					'auth_opac_show' => $row->editorial_publication_state_auth_opac_show
				);
			}
		}
	}

	public function get_publications_states(){
		if(!$this->publications_states) {
			$this->fetch_data_cache();
		}
		return $this->publications_states;
	}

	public function get_selector_options($selected=0){
		global $charset;
		$options = "";
		$this->get_publications_states();
		for($i=0 ; $i<count($this->publications_states) ; $i++){
			$options.= "
			<option value='".$this->publications_states[$i]['id']."'".($this->publications_states[$i]['id']==$selected ? "selected='selected'" : "").">".htmlentities($this->publications_states[$i]['label'],ENT_QUOTES,$charset)."</option>";	
		}
		return $options;
	}
}