<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: frbr_entity_authperso_view_authpersolist.class.php,v 1.3 2021/03/01 11:04:07 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/authperso_authority.class.php");

class frbr_entity_authperso_view_authpersolist extends frbr_entity_common_view_django{
	
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->default_template = "<div>
{% for auth in authperso %}
	{% if auth.info.view %}
		{{ auth.info.view }}
	{% else %}
		{{ auth.name }} : {{ auth.info.isbd }}
	{% endif%}
{% endfor %}
</div>";
	}
		
	public function render($datas, $grouped_datas = []){	
		//on rajoute nos �l�ments...
		//le titre
		$render_datas = array();
		$render_datas['title'] = $this->msg["frbr_entity_authperso_view_authpersolist_title"];
		$render_datas['authperso'] = array();
		if(is_array($datas)){
			foreach($datas as $authperso_authority_id){
				$render_datas['authperso'][] = authorities_collection::get_authority('authority', 0, ['num_object' => $authperso_authority_id, 'type_object' => AUT_TABLE_AUTHPERSO]);
			}
		}
		if(!empty($grouped_datas)){
		    $render_datas['grouped_authperso'] = [];
		    foreach($grouped_datas as $key => $group){
		        if (!isset($render_datas['grouped_authperso'][$key])) {
		            $render_datas['grouped_authperso'][$key] = [];
		        }
		        $render_datas['grouped_authperso'][$key]['label'] = $group["label"];
		        $render_datas['grouped_authperso'][$key]["values"] = [];
		        foreach ($group["values"] as $authperso_authority_id) {
		            $render_datas['grouped_authperso'][$key]["values"][] = authorities_collection::get_authority('authority', 0, ['num_object' => $authperso_authority_id, 'type_object' => AUT_TABLE_AUTHPERSO]);
		        }
		    }
		    usort($render_datas['grouped_authperso'], function ($item1, $item2) {
		        return $item1['label'] <=> $item2['label'];
		    });
		}
		//on rappelle le tout...
		return parent::render($render_datas);
	}
	
	public function get_format_data_structure(){		
		$format = array();
		$format[] = array(
			'var' => "title",
			'desc' => $this->msg['frbr_entity_authperso_view_title']
		);
		$authperso = array(
			'var' => "authperso",
			'desc' => $this->msg['frbr_entity_authperso_view_authperso_desc'],
			'children' => authority::get_properties(AUT_TABLE_AUTHPERSO,"authperso[i]")
		);
		$format[] = $authperso;
		$format[] = array(
		    'var' => "grouped_authperso",
		    'desc' => $this->msg['frbr_entity_authperso_view_grouped_authperso'],
		    'children' => [
		        [
		            'var' => "grouped_authperso.key.label",
		            'desc' => $this->msg['frbr_entity_authperso_view_grouped_authperso_label']
		            
		        ],
		        [
		            'var' => "grouped_authperso.key.values",
		            'desc' => $this->msg['frbr_entity_authperso_view_grouped_authperso_values']
		            
		        ]
		    ]
		);
		$format = array_merge($format,parent::get_format_data_structure());
		return $format;
	}
}