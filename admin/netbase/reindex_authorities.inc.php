<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: reindex_authorities.inc.php,v 1.12.4.4 2024/06/19 07:25:04 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $include_path, $msg;
global $start, $v_state, $spec, $count, $current_module, $index_quoi, $step_position;

require_once($class_path."/indexation_authority.class.php");
require_once($class_path."/indexation_authperso.class.php");

// la taille d'un paquet de notices
$lot = REINDEX_PAQUET_SIZE; // defini dans ./params.inc.php

// initialisation de la borne de départ
if (!isset($start) && empty($step_position)) {
    $start=0;
    //remise a zero de la table au début
    netbase_authorities::raz_index();
}
if(!empty($step_position)) {
    netbase_authorities::set_step_position($step_position);
}
$v_state=urldecode($v_state);

// on commence par :
if (empty($index_quoi)) $index_quoi='AUTHORS';

switch ($index_quoi) {
    case 'AUTHORS':
        netbase_authorities::set_object_type(AUT_TABLE_AUTHORS);
        netbase_authorities::proceed();
        break ;
        
    case 'PUBLISHERS':
        netbase_authorities::set_object_type(AUT_TABLE_PUBLISHERS);
        netbase_authorities::proceed();
        break ;
        
    case 'CATEGORIES':
        netbase_authorities::set_object_type(AUT_TABLE_CATEG);
        netbase_authorities::proceed();
        break ;
        
    case 'COLLECTIONS':
        netbase_authorities::set_object_type(AUT_TABLE_COLLECTIONS);
        netbase_authorities::proceed();
        break ;
        
    case 'SUBCOLLECTIONS':
        netbase_authorities::set_object_type(AUT_TABLE_SUB_COLLECTIONS);
        netbase_authorities::proceed();
        break ;
        
    case 'SERIES':
        netbase_authorities::set_object_type(AUT_TABLE_SERIES);
        netbase_authorities::proceed();
        break ;
        
    case 'DEWEY':
        netbase_authorities::set_object_type(AUT_TABLE_INDEXINT);
        netbase_authorities::proceed();
        break ;
        
    case 'TITRES_UNIFORMES':
        netbase_authorities::set_object_type(AUT_TABLE_TITRES_UNIFORMES);
        netbase_authorities::proceed();
        break ;
        
    case 'AUTHPERSO':
        netbase_authorities::set_object_type(AUT_TABLE_AUTHPERSO);
        if (!isset($count) || !$count) {
            $elts = pmb_mysql_query("SELECT count(1) FROM authperso_authorities");
            $count = pmb_mysql_result($elts, 0, 0);
            //On controle qu'il n'y a pas d'autorité à enlever
            netbase_authorities::delete_unrelated_authorities();
        }
        
        print netbase::get_display_progress_title($msg["nettoyage_reindex_authperso"]);
        
        $query = pmb_mysql_query("SELECT id_authperso_authority as id, authperso_authority_authperso_num from authperso_authorities ORDER BY authperso_authority_authperso_num LIMIT $start, $lot");
        if (pmb_mysql_num_rows($query)) {
            print netbase::get_display_progress($start, $count);
            
            $id_authperso = 0;
            while($row = pmb_mysql_fetch_object($query)) {
                if(!$id_authperso || ($id_authperso != $row->authperso_authority_authperso_num)) {
                    $indexation_authperso = new indexation_authperso($include_path."/indexation/authorities/authperso/champs_base.xml", "authorities", (1000+$row->authperso_authority_authperso_num), $row->authperso_authority_authperso_num);
                    $indexation_authperso->set_deleted_index(true);
                    $id_authperso = $row->authperso_authority_authperso_num;
                }
                $indexation_authperso->maj($row->id);
            }
            pmb_mysql_free_result($query);
            $next = $start + $lot;
            print netbase::get_current_state_form($v_state, $spec, 'AUTHPERSO', $next, $count);
        } else {
            // mise à jour de l'affichage de la jauge
            print netbase::get_display_final_progress();
            $v_state .= netbase::get_display_progress_v_state($msg["nettoyage_reindex_authperso"], $count." ".$msg["nettoyage_res_reindex_authperso"]);
            print netbase::get_current_state_form($v_state, $spec, 'FINI');
        }
        break ;
        
    case 'FINI':
        $spec = $spec - INDEX_AUTHORITIES;
        netbase_authorities::enable_index();
        $v_state .= netbase::get_display_progress_v_state($msg["nettoyage_reindex_fini"]);
        print "
			<form class='form-$current_module' name='process_state' action='./clean.php?spec=$spec&start=0' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
			</form>
			<script type=\"text/javascript\"><!--
				setTimeout(\"document.forms['process_state'].submit()\",1000);
				-->
			</script>";
        break ;
}
