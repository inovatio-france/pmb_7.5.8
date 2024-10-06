<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: skos_concepts_list.class.php,v 1.10 2022/01/10 10:23:05 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/skos/skos_concept.class.php");
require_once($class_path."/vedette/vedette_composee.class.php");

/**
 * class skos_concepts_list
 * Controlleur d'une liste de concepts qui indexent un �l�ment
 */
class skos_concepts_list {
	
	/**
	 * Tableau des concepts associ�s � l'objet
	 * @var skos_concept
	 */
	private $concepts = array();
	
	/**
	 * Tablea des concepts tri�s par sch�mas
	 * @var array
	 */
	protected $concepts_by_schemes;
	
	/**
	 * D�finit les concepts depuis les concepts qui indexent un objet
	 * @param int $object_type Constante repr�sentant le type de l'objet index�
	 * @param int $object_id Identifiant de l'objet index�
	 * @return boolean true si des concepts ont �t� trouv�s, false sinon
	 */
	public function set_concepts_from_object($object_type, $object_id) {
		$query = "select num_concept, order_concept from index_concept where num_object = ".$object_id." and type_object = ".$object_type." order by order_concept";
		$result = pmb_mysql_query($query);
		if ($result && pmb_mysql_num_rows($result)) {
			while ($row = pmb_mysql_fetch_object($result)){
				$this->concepts[$row->order_concept] = new skos_concept($row->num_concept);
			}
			return true;
		}
		return false;
	}
	
	/**
	 * D�finit les concepts depuis un tableau de concepts pass� en param�tre
	 * @param skos_concept $concepts
	 */
	public function set_concepts($concepts) {
		$this->concepts = $concepts;
	}
	
	/**
	 * Ajoute un concept au tableau de concepts
	 * @param skos_concept $concept
	 */
	public function add_concept($concept) {
		$this->concepts[] = $concept;
	}
	
	/**
	 * Retourne le tableau des concepts de la liste
	 * @return skos_concept Tableau des concepts de la liste
	 */
	public function get_concepts() {
		return $this->concepts;
	}
	
	/**
	 * Retourne le tableau des concepts de la liste tri�s par sch�mas
	 * @return skos_concept Tableau des concepts de la liste
	 */
	public function get_concepts_by_schemes() {
		if (isset($this->concepts_by_schemes)) {
			return $this->concepts_by_schemes;
		}
		foreach ($this->concepts as $concept) {
			foreach ($concept->get_schemes() as $scheme_id => $scheme_label) {
				if (!isset($this->concepts_by_schemes[$scheme_id])) {
					$this->concepts_by_schemes[$scheme_id] = array(
							'scheme_label' => $scheme_label,
							'elements' => array()
					);
				}
				$this->concepts_by_schemes[$scheme_id]['elements'][] = $concept;
			}
		}
		return $this->concepts_by_schemes;
	}

	/**
	 * Retourne le tableau des concepts de la liste sans sch�mas
	 * @return skos_concept Tableau des concepts de la liste
	 */
	public function get_concepts_without_sheme() {
		$concepts_no_schemes = array();
		foreach ($this->concepts as $concept) {
			if(!count($concept->get_schemes())) {
				$concepts_no_schemes[] = $concept;			
			}
		}
		return $concepts_no_schemes;
	}
	
	/**
	 * Retourne les concepts compos�s qui utilisent un �l�ment
	 * @param int $element_id Identifiant de l'�l�ment
	 * @param string $element_type Type de l'�l�ment (Disponible dans vedette.xml)
	 * @return skos_concept Tableau de concepts compos�s
	 */
	public function set_composed_concepts_built_with_element($element_id, $element_type) {
		// On va chercher les vedettes construites avec l'�l�ment
		$vedettes_ids = vedette_composee::get_vedettes_built_with_element($element_id, $element_type);
		foreach ($vedettes_ids as $vedette_id) {
			// On va chercher les concepts correspondant � chaque vedette
			if ($concept_id = vedette_composee::get_object_id_from_vedette_id($vedette_id, TYPE_CONCEPT_PREFLABEL)) {
				$this->concepts[] = new skos_concept($concept_id);
			}
		}
		if (!count($this->concepts)) {
			return false;
		}
		return true;
	}
	
	/**
	 * Retourne les concepts utilis�s dans un sch�ma par libell� de sch�ma
	 * @param string $label Label du sch�ma
	 * @return skos_concept Tableau de concepts
	 */
	public function set_concepts_from_scheme_label($label) {
		$query = "select ?concept where {
				?scheme skos:prefLabel '".$label."' .
				?concept skos:inScheme ?scheme
			}";
	
		skos_datastore::query($query);
		if(skos_datastore::num_rows()){
			$results = skos_datastore::get_result();
			foreach($results as $result){
				$this->concepts[] = new skos_concept(0,$result->concept);
			}
		}
	}
	
	/**
	 * Retourne les concepts utilis�s dans un sch�ma par uri de sch�ma
	 * @param string $uri URI du sch�ma
	 * @return skos_concept Tableau de concepts
	 */
	public function set_concepts_from_scheme_uri($uri) {
		$query = "select ?concept where {
				?concept skos:inScheme <".$uri.">
			}";
	
		skos_datastore::query($query);
		if(skos_datastore::num_rows()){
			$results = skos_datastore::get_result();
			foreach($results as $result){
				$this->concepts[] = new skos_concept(0,$result->concept);
			}
		}
	}
} // fin de d�finition de la classe index_concept
