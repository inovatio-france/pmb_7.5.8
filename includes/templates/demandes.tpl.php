<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes.tpl.php,v 1.40.6.1 2024/01/05 15:32:49 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $current_module, $form_filtre_demande, $current_module, $pmb_lecteurs_localises, $form_liste_demande;
global $form_modif_demande, $form_consult_dmde, $form_liste_docnum, $form_reponse_final, $reponse_finale, $charset, $form_consult_linked_record;

$form_filtre_demande = "
 <script type='text/javascript'>
	function filtrer_user(){
 		document.forms['search'].submit();
	} 
</script>
<script src='".$base_path."/javascript/ajax.js'></script>
<form class='form-".$current_module."' id='search' name='search' method='post' action=\"./demandes.php?categ=list\">
	<h3>".$msg['demandes_search_filtre_form']."</h3>
	<input type='hidden' name='act' />
	<div class='form-contenu'>
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_titre']."</label>
		</div>
		<div class='row'>
			<input type='text' class='saisie-30em' name='user_input' id='user_input' value='!!user_input!!'/>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_user_filtre']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_etat_filtre']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_periode_filtre']."</label>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<input type='hidden' id='idempr' name='idempr' value='!!idempr!!' />
				<input type='text' id='empr_txt' name='empr_txt' class='saisie-20emr' value='!!empr_txt!!' completion='empr' autfield='idempr' autocomplete='off' tabindex='1'/>
				<input type='button' class='bouton_small' value='...' onclick=\"openPopUp('./select.php?what=origine&caller=search&param1=idempr&param2=empr_txt&deb_rech='+".pmb_escape()."(this.form.empr_txt.value)+'&filtre=ONLY_EMPR&callback=filtrer_user".($pmb_lecteurs_localises ? "&empr_loca='+this.form.dmde_loc.value": "'").", 'selector')\" />
				<input type='button' class='bouton_small' value='X' onclick=\"document.getElementById('idempr').value=0;document.getElementById('empr_txt').value='';\" />
			</div>
			<div class='colonne3'>
				!!state!!
			</div>
			<div class='colonne3'>
				!!periode!!
			</div>
		</div>
		<div class='row'> 
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_affectation_filtre']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_theme_filtre']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type_filtre']."</label>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				!!affectation!!
			</div>
			<div class='colonne3'>
				!!theme!!
			</div>
			<div class='colonne3'>
				!!type!!
			</div>
		</div>
<script>ajax_parse_dom();</script>";
if($pmb_lecteurs_localises)
$form_filtre_demande .="
		<div class='row'> 
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_localisation_filtre']."</label>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				!!localisation!!
			</div>
		</div>";
$form_filtre_demande .="
		<div class='row'></div>
		!!champs_perso!!
		<div class='row'></div>
	</div>
	<div class='row'></div>
	<div class='row'>
		<input type='submit' class='bouton' name='search_dmd' id='search_dmd' value='".$msg['demandes_search']."' onclick='this.form.act.value=\"search\"'/>
		<input type='submit' class='bouton' name='new_dmd' id='new_dmd' value='".$msg['demandes_new']."' onclick='this.form.act.value=\"new\"'/>
	</div>
</form>

";

$form_liste_demande ="
<script src='./javascript/dynamic_element.js' type='text/javascript'></script>
<script src='./javascript/demandes_form.js' type='text/javascript'></script>
<script type='text/javascript'>
	var msg_demandes_note_confirm_demande_end='".addslashes($msg['demandes_note_confirm_demande_end'])."'; 
	var msg_demandes_actions_nocheck='".addslashes($msg['demandes_actions_nocheck'])."'; 
	var msg_demandes_confirm_suppr = '".addslashes($msg['demandes_confirm_suppr'])."';
	var msg_demandes_note_confirm_suppr = '".addslashes($msg['demandes_note_confirm_suppr'])."';
</script>
<script type='text/javascript'>
	function alert_progressiondemande(){
		alert(\"".$msg['demandes_progres_ko']."\");
	}
	function verifChk(txt) {
		var elts = document.forms['liste'].elements['chk[]'];
		var elts_cnt  = (typeof(elts.length) != 'undefined')
	              ? elts.length
	              : 0;
		nb_chk = 0;
		if (elts_cnt) {
			for(var i=0; i < elts.length; i++) {
				if (elts[i].checked) nb_chk++;
			}
		} else {
			if (elts.checked) nb_chk++;
		}
		if (nb_chk == 0) {
			alert(\"".$msg['demandes_nocheck']."\");
			return false;	
		}
		
		if(txt == 'suppr'){
			var sup = confirm(\"".$msg['demandes_confirm_suppr']."\");
			if(!sup) 
				return false;
			return true;
		}
	}
</script>
<form class='form-".$current_module."' id='liste' name='liste' method='post' action=\"./demandes.php?categ=list\">
	<input type='hidden' name='act' id='act' />
	<input type='hidden' name='state' id='state' />
	<h3>".$msg['demandes_liste']." !!count_dmde!!</h3>
	<div class='form-contenu'>
		<table>
			<tbody>
				<tr>
					<th></th>
					<th></th>
					<th>".$msg['demandes_theme']."</th>
					<th>".$msg['demandes_type']."</th>
					<th>".$msg['demandes_titre']."</th>
					<th>".$msg['demandes_etat']."</th>
					<th>".$msg['demandes_date_dmde']."</th>
					<th>".$msg['demandes_date_prevue']."</th>
					<th>".$msg['demandes_date_butoir']."</th>
					<th>".$msg['demandes_demandeur']."</th>
					<th>".$msg['demandes_attribution']."</th>
					<th>".$msg['demandes_progression']."</th>
					!!header_champs_perso!!
					<th>".$msg['demandes_notice']."</th>					
					<th></th>
				</tr>
				!!liste_dmde!!				
			</tbody>
		</table>
	</div>
	<div class='row'>
		<div class='left'>
			!!btn_etat!!
			!!btn_attribue!!
		</div>
		<div class='right'>
			!!btn_suppr!!
		</div>
	</div>
	<div class='row'></div>
</form>	
<script>parse_dynamic_elts();</script>
";

$form_modif_demande = "
<script type='text/javascript'>
	function confirm_delete(){
		
		var sup = confirm(\"".$msg['demandes_confirm_suppr']."\");
		if(!sup)
			return false;
		return true;	
	}
</script>
<script src='".$base_path."/javascript/ajax.js'></script>
<h1>".$msg['demandes_gestion']." : ".$msg['admin_demandes']."</h1>
<form class='form-".$current_module."' id='modif_dmde' name='modif_dmde' method='post' action=\"!!form_action!!\">
	<h3>!!form_title!!</h3>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<div class='form-contenu'>
		!!content_form!!
		<div class='row'></div>
		!!champs_perso!!
		<div class='row'></div>
	</div>
	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='$msg[76]' onClick=\"!!cancel_action!!\" />
			<input type='submit' class='bouton' value='$msg[77]' onClick='this.form.act.value=\"save\" ; return test_form(this.form); ' />
		</div>
		<div class='right'>
			!!btn_suppr!!
		</div>
	</div>
	<div class='row'></div>
</form>

<script type='text/javascript'>
	function test_form(form) {	
		if(isNaN(form.progression.value) || form.progression.value > 100 || form.progression.value < 0 ){
	    	alert(\"$msg[demandes_progres_ko]\");
			return false;
	    }
		if((form.titre.value.length == 0) ||  (form.empr_txt.value.length == 0) || (form.date_debut.value.length == 0)||  (form.date_fin.value.length == 0)){
			alert(\"$msg[demandes_create_ko]\");
			return false;
	    } 
	    
	    var deb =form.date_debut.value;
	    var fin = form.date_fin.value;
	   
	    if(deb>fin){
	    	alert(\"$msg[demandes_date_ko]\");
	    	return false;
	    }
		return check_form();
			
	}
	ajax_parse_dom();
</script>
";

$form_consult_dmde = "
<h1>".$msg['demandes_gestion']." : ".$msg['admin_demandes']."</h1>
<script src='./javascript/demandes.js' type='text/javascript'></script>
<script src='./javascript/tablist.js' type='text/javascript'></script>
<script src='./javascript/select.js' type='text/javascript'></script>
<script type='text/javascript'>
	function confirm_delete(){
		
		var sup = confirm(\"".$msg['demandes_confirm_suppr']."\");
		if(!sup)
			return false;
		return true;	
	}
	
	function alert_progressiondemande(){
		alert(\"".$msg['demandes_progres_ko']."\");
	}
</script>
<form class='form-".$current_module."' id='see_dmde' name='see_dmde' method='post' action=\"./demandes.php?categ=gestion\">
	<h3>!!icone!!!!form_title!!</h3>
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='state' name='state' />
	<div class='form-contenu'>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_theme']." : </label>
				!!theme_dmde!!
			</div>
			<div class='colonne3'>		
				<label class='etiquette'>".$msg['demandes_etat']." : </label>
				!!etat_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_dmde']." : </label>
				!!date_dmde!!
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_sujet']." : </label>
				!!sujet_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_demandeur']." : </label>
				!!demandeur!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_prevue']." : </label>
				!!date_prevue_dmde!!
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type']." : </label>
				!!type_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_attribution']." : </label>
				!!attribution!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_butoir']." : </label>
				!!date_butoir_dmde!!
			</div>
		</div>	
		
		<div class='row'>
			<div class='colonne3'>
				!!form_linked_record!!
			</div>	
			<div class='colonne3'>
				&nbsp;			
			</div>
			<div class='colonne3'>
				<label class='etiquette' >".$msg['demandes_progression']." : </label>
				<span id='progressiondemande_!!iddemande!!' name='progressiondemande_!!iddemande!!' dynamics='demandes,progressiondemande' dynamics_params='text'>!!progression_dmde!!</span>
			</div>
		</div>
		<div class='row'></div>
		<div class='row'>
			!!champs_perso!!
		</div>
		<div class='row'></div>
	</div>
	<div class='row'>
		!!btn_etat!!
	</div>
	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='".$msg['demandes_retour']."' onClick=\"document.location='./demandes.php?categ=list!!params_retour!!'\" />
			<input type='submit' class='bouton' value='$msg[62]' onClick='this.form.act.value=\"modif\" ; ' />			
			!!btns_notice!!
			!!btn_audit!!
			!!btn_repfinal!!
			!!btn_faq!!
		</div>
		<div class='right'>
			!!btn_suppr_notice!!
			<input type='submit' class='bouton' value='".$msg['demandes_delete']."' onClick='this.form.act.value=\"suppr_noti\" ; return confirm_delete();' />
		</div>
	</div>
	<div class='row'></div>
</form>
";

$form_liste_docnum ="
<form class='form-".$current_module."' id='liste_action' name='liste_action' method='post'>
	<h3 id='htitle'>".$msg['demandes_liste_docnum']."</h3>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<div class='form-contenu' >
		<div class='row'>
			!!liste_docnum!!	
		</div>
	</div>
	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='".$msg['demandes_retour']."' onClick=\"history.go(-1)\" />
			!!btn_attach!!	
		</div>
		<div class='right'>
			<input type='button' class='bouton' name='btn_chk' id='btn_chk' value='".$msg['tout_cocher_checkbox']."' onClick=\"check_all('liste_action','chk',true);\" />
			<input type='button' class='bouton' name='btn_chk' id='btn_chk' value='".$msg['tout_decocher_checkbox']."' onClick=\"check_all('liste_action','chk',false);\" />
			<input type='button' class='bouton' name='btn_chk' id='btn_chk' value='".$msg['inverser_checkbox']."' onClick=\"inverser('liste_action','chk');\" />
		</div>
	</div>
	
</form>

<script type='text/javascript'>

function check_all(the_form,the_objet,do_check){

	var elts = document.forms[the_form].elements[the_objet+'[]'] ;
	var elts_cnt  = (typeof(elts.length) != 'undefined')
              ? elts.length
              : 0;

	if (elts_cnt) {
		for (var i = 0; i < elts_cnt; i++) {
			elts[i].checked = do_check;
		} 
	} else {
		elts.checked = do_check;
	}
	return true;
}

function inverser(the_form,the_objet){

	var elts = document.forms[the_form].elements[the_objet+'[]'] ;
	var elts_cnt  = (typeof(elts.length) != 'undefined')
              ? elts.length
              : 0;

	if (elts_cnt) {
		for (var i = 0; i < elts_cnt; i++) {
			if(elts[i].checked == true) elts[i].checked = false;
			else elts[i].checked = true;
		} 
	} 
	return true;
}

 function verifChk() {
		
	var elts = document.forms['liste_action'].elements['chk[]'];
	var elts_cnt  = (typeof(elts.length) != 'undefined')
              ? elts.length
              : 0;
	nb_chk = 0;
	if (elts_cnt) {
		for(var i=0; i < elts.length; i++) {
			if (elts[i].checked) nb_chk++;
		}
	} else {
		if (elts.checked) nb_chk++;
	}
	if (nb_chk == 0) {
		var sup = confirm(\"".$msg['demandes_confirm_attach_docnum']."\");
		if(!sup) 
			return false;
		return true;
	}
	
	return true;
}
</script>
";

$form_reponse_final = "
<h1>".$msg['demandes_gestion']." : ".$msg['admin_demandes']."</h1>
<form class='form-".$current_module."' id='dmde' name='dmde' method='post' action=\"!!form_action!!\">
	<h3>!!titre_dmde!!</h3>
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='act' name='act' />	
	<div class='form-contenu'>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_theme']." : </label>
				!!theme_dmde!!
			</div>			
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_sujet']." : </label>
				!!sujet_dmde!!
			</div>			
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type']." : </label>
				!!type_dmde!!
			</div>			
		</div>		
		<div class='row'></div>
	</div>	
</form>
<form class='form-".$current_module."' id='formrepfinale' name='formrepfinale' method='post' action=\"!!form_action!!\">
	<h3>!!form_title!!</h3>
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='act' name='act' />	
	<div class='form-contenu'>
		
	<div class='row'>
		<textarea id='f_message' name='f_message' wrap='virtual' cols='55' rows='4' >!!reponse!!</textarea>
	</div>
	<div class='row'>
		<div class='left'>			
			<input type='button' class='bouton' value='$msg[76]' onClick=\"!!cancel_action!!\" />
			<input type='submit' class='bouton' value='$msg[77]' onclick='this.form.act.value=\"save_repfinale\"'/>
		</div>
		<div class='right'>
			!!btn_suppr!!
		</div>
	</div>					
	<div class='row'></div>
	</div>
</form>
";

$reponse_finale = "
<form class='form-".$current_module."' id='repfinale' name='formrepfinale' method='post' action=\"!!form_action!!\">
	<h3>".htmlentities($msg['demandes_reponse_finale'],ENT_QUOTES,$charset)."</h3>
		<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
		<input type='hidden' id='act' name='act' />	
		<div class='form-contenu'>		
			<div class='row'>!!repfinale!!</div>
			<div class='row'></div>
		</div>							
		<div class='row'>
			<div class='left'>			
				<input type='submit' class='bouton' value='".$msg['demandes_repfinale_modif']."' onclick='this.form.act.value=\"final_response\" ; ' />&nbsp;
			</div>
			<div class='right'>	
				<input type='submit' class='bouton' value='".$msg['demandes_repfinale_delete']."' onClick='this.form.act.value=\"suppr_repfinale\" ; return confirm_delete();' />	
			</div>
		</div>
		<div class='row'></div>
	</form>	
";

$form_consult_linked_record = "
				<label class='etiquette'>".$msg['demandes_linked_record']." : </label>
				!!linked_record_icon!!
				<a href='!!linked_record_link!!' title='!!linked_record!!'>!!linked_record!!</a>";
