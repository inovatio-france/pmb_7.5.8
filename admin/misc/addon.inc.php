<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: addon.inc.php,v 1.6.6.56 2024/08/30 09:49:14 rtigero Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if( !function_exists('traite_rqt') ) {
	function traite_rqt($requete="", $message="") {

		global $charset;
		$retour="";
		if($charset == "utf-8"){
			$requete=utf8_encode($requete);
		}
		pmb_mysql_query($requete) ;
		$erreur_no = pmb_mysql_errno();
		if (!$erreur_no) {
			$retour = "Successful";
		} else {
			switch ($erreur_no) {
				case "1060":
					$retour = "Field already exists, no problem.";
					break;
				case "1061":
					$retour = "Key already exists, no problem.";
					break;
				case "1091":
					$retour = "Object already deleted, no problem.";
					break;
				default:
					$retour = "<font color=\"#FF0000\">Error may be fatal : <i>".pmb_mysql_error()."<i></font>";
					break;
			}
		}
		return "<tr><td><font size='1'>".($charset == "utf-8" ? utf8_encode($message) : $message)."</font></td><td><font size='1'>".$retour."</font></td></tr>";
	}
}
echo "<table>";

/******************** AJOUTER ICI LES MODIFICATIONS *******************************/

switch ($pmb_bdd_subversion) {
	case 0:
		// DG - Ajout d'une classification sur les listes
		$rqt = "ALTER TABLE lists ADD list_num_ranking int not null default 0 AFTER list_default_selected" ;
		echo traite_rqt($rqt,"ALTER TABLE lists ADD list_num_ranking");
	case 1:
		// DG - Ajout dans les bannettes la possibilit� d'historiser les diffusions
		$rqt = "ALTER TABLE bannettes ADD bannette_diffusions_history INT(1) UNSIGNED NOT NULL default 0";
		echo traite_rqt($rqt,"ALTER TABLE bannettes ADD bannette_diffusions_history");

		// DG - Log des diffusions de bannettes
		$rqt = "CREATE TABLE IF NOT EXISTS bannettes_diffusions (
					id_diffusion int unsigned not null auto_increment primary key,
        			diffusion_num_bannette int(9) unsigned not null default 0,
        			diffusion_mail_object text,
					diffusion_mail_content mediumtext,
					diffusion_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					diffusion_records text,
					diffusion_deleted_records text,
					diffusion_recipients text,
					diffusion_failed_recipients text
        		)";
		echo traite_rqt($rqt,"create table bannettes_diffusions");
	case 2:
		// TS-RT-JP - Ajout de la table dsi_content_buffer
		$rqt = "CREATE TABLE IF NOT EXISTS dsi_content_buffer (
		  id_content_buffer int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  type int(11) NOT NULL DEFAULT 0,
		  content longblob NOT NULL,
		  num_diffusion_history int(10) UNSIGNED NOT NULL DEFAULT 0
		)";
		echo traite_rqt($rqt,"CREATE TABLE dsi_content_buffer");

		// TS-RT-JP - Ajout du champ automatic sur une diffusion
		$rqt = "ALTER TABLE dsi_diffusion ADD automatic tinyint(1) NOT NULL DEFAULT 0 AFTER settings" ;
		echo traite_rqt($rqt,"ALTER dsi_diffusion ADD automatic");

		// TS-RT-JP - Ajout d'un �tat sur l'historique de diffusion
		$rqt = "ALTER TABLE dsi_diffusion_history ADD state tinyint(1) NOT NULL DEFAULT 0 AFTER total_recipients" ;
		echo traite_rqt($rqt,"ALTER dsi_diffusion_history ADD state");
	case 3:
		//DG - T�ches : changement du champ msg_statut en texte
		$rqt = "ALTER TABLE taches MODIFY msg_statut TEXT";
		echo traite_rqt($rqt,"ALTER TABLE taches MODIFY msg_statut IN TEXT");

		//DG - Ajout d'un param�tre cach� permettant de d�finir si une indexation via le gestionnaire de t�ches est en cours
		if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='scheduler_indexation_in_progress' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
				VALUES (NULL, 'pmb', 'scheduler_indexation_in_progress', '0', 'Param�tre cach� permettant de d�finir si une indexation via le gestionnaire de t�ches est en cours', '', '1')" ;
			echo traite_rqt($rqt,"insert hidden pmb_scheduler_indexation_in_progress=0 into parametres") ;
		}
	case 4:
		//DG - T�ches : (correction du float) changement du champ indicat_progress en nombre flotant
		$rqt = "ALTER TABLE taches MODIFY indicat_progress FLOAT(5,2) NOT NULL DEFAULT 0";
		echo traite_rqt($rqt,"ALTER TABLE taches MODIFY indicat_progress IN FLOAT");

	case 5:
	    //NG - Ajout d'un parametre pour la prise en compte de la gestion des animations des lecteurs
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='gestion_animation' "))==0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param) VALUES (0, 'pmb', 'gestion_animation', '0', 'Utiliser la gestion des animations des lecteurs ? \n 0 : Non\n 1 : Oui, gestion simple, \n 2 : Oui, gestion avanc�e') " ;
	        echo traite_rqt($rqt,"insert pmb_gestion_animation = 0 into parametres");
	    }

	case 6:
	    // GN - Ajout d'un param�tre utilisateur (import Z3950 en catalogue automatique/manuel)
	    $rqt = "ALTER TABLE users ADD deflt_notice_catalog_categories_auto INT(1) UNSIGNED DEFAULT 1 NOT NULL ";
	    echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_notice_catalog_categories_auto");

	case 7:
		// Equipe DEV Plugins
		$rqt = "CREATE TABLE IF NOT EXISTS plugins (
        			id_plugin int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        			plugin_name varchar(255) NOT NULL DEFAULT '',
        			plugin_settings text NOT NULL
			)";
		echo traite_rqt($rqt,"CREATE TABLE plugins");

	case 8 :
	    // DB - Info de modification des fichiers db_param
	    $rqt = " select 1 " ;
        echo traite_rqt($rqt, encoding_normalize::charset_normalize("<b class='erreur'>
            LES FICHIERS DE CONNEXION A LA BASE DE DONNEES ( pmb/includes/db_param.inc.php et pmb/opac_css/includes/opac_db_param.inc.php ONT ETE MODIFIES.<br />
            Un mod&egrave;le de r&eacute;f&eacute;rence est d&eacute;fini dans le r&eacute;pertoire pmb/tables pour chacun de ces fichiers.<br />
            VERIFIEZ CES FICHIERS SI VOUS VENEZ DE FAIRE UNE MISE A JOUR DE VOTRE INSTALLATION.
            </b>", 'iso-8859-1'));

	case 9 :
	    // TS - mise � jour du param�tre pmb_book_pics_url
	    $rqt = "UPDATE parametres SET comment_param = CONCAT(comment_param,'\n Ce param�tre n\'est plus utilis�. Merci de reporter les valeurs personnalis�es dans le param�trage des vignettes (admin/vignettes/sources/liens externes).') WHERE sstype_param = 'book_pics_url'" ;
            echo traite_rqt($rqt, encoding_normalize::charset_normalize("<b class='erreur'>
            Les param&egrave;tres book_pics_url ne sont plus utilis&eacute;s. Merci de reporter les valeurs personnalis&eacute;es dans le param&eacute;trage de vignettes (admin/vignettes/sources/liens externes.
            </b> ", 'iso-8859-1'));

	    // TS - modification du nom de la source de vinettes
	    $rqt = "UPDATE thumbnail_sources_entities SET source_class = 'Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Externallinks\\\\RecordExternallinksThumbnailSource' WHERE source_class = 'Pmb\\\\Thumbnail\\\\Models\\\\Sources\\\\Entities\\\\Record\\\\Amazon\\\\RecordAmazonThumbnailSource'";
	    echo traite_rqt($rqt, "UPDATE thumbnail_sources_entitie WHERE source_class = 'Pmb\\Thumbnail\\Models\\Sources\\Entities\\Record\\Amazon\\RecordAmazonThumbnailSource'");

	    //TS - changement du champ search_universe_description en text
	    $rqt = "ALTER TABLE search_universes MODIFY search_universe_description TEXT";
	    echo traite_rqt($rqt,"ALTER TABLE search_universes MODIFY search_universe_description IN TEXT");

	    //TS - changement du champ search_segment_description en text
	    $rqt = "ALTER TABLE search_segments MODIFY search_segment_description TEXT";
	    echo traite_rqt($rqt,"ALTER TABLE search_segments MODIFY search_segment_description IN TEXT");
	case 10 :
	    //GN - Ajout d'un champ search_segment_data pour stocker des donn�es
	    $rqt = "ALTER TABLE search_segments ADD search_segment_data varchar(255)";
	    echo traite_rqt($rqt,"ALTER TABLE search_segments ADD search_segment_data");
	case 11 :
		//DG - Modification de la taille du champ watch_boolean_expression en text
		$rqt = "ALTER TABLE docwatch_watches MODIFY watch_boolean_expression TEXT";
		echo traite_rqt($rqt,"ALTER TABLE docwatch_watches MODIFY watch_boolean_expression IN TEXT");

		//DG - Modification de la taille du champ datasource_boolean_expression en text
		$rqt = "ALTER TABLE docwatch_datasources MODIFY datasource_boolean_expression TEXT";
		echo traite_rqt($rqt,"ALTER TABLE docwatch_datasources MODIFY datasource_boolean_expression IN TEXT");
	case 12 :
	    //QV - Refonte DSI ajout des descripteurs
	    $rqt = "CREATE TABLE IF NOT EXISTS dsi_diffusion_descriptors (
                num_diffusion int(11) NOT NULL DEFAULT 0,
                num_noeud int(11) NOT NULL DEFAULT 0,
                diffusion_descriptor_order int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (num_diffusion, num_noeud)
            )";
	    echo traite_rqt($rqt, "CREATE TABLE dsi_diffusion_descriptors");

	    //QV - Refonte DSI correction du commentaire dsi_active
	    $rqt = "UPDATE parametres SET comment_param = 'D.S.I activ�e ? \r\n 0: Non \r\n 1: Oui \r\n 2: Oui (refonte)' WHERE type_param = 'dsi' AND sstype_param = 'active';";
	    echo traite_rqt($rqt, "UPDATE parametres SET comment_param for dsi_active");

	    //QV - Refonte Portail correction du commentaire cms_active
	    $rqt = "UPDATE parametres SET comment_param = 'Module \'Portail\' activ�.\r\n 0 : Non.\r\n 1 : Oui.\r\n 2 : Oui (refonte).' WHERE type_param = 'cms' AND sstype_param = 'active';";
	    echo traite_rqt($rqt, "UPDATE parametres SET comment_param for cms_active");
	case 13 :
		// DG - Table de cache des ISBD d'entit�s
		$rqt = "CREATE TABLE IF NOT EXISTS entities (
				num_entity int(10) UNSIGNED NOT NULL DEFAULT 0,
				type_entity int(3) UNSIGNED NOT NULL DEFAULT 0,
				entity_isbd text NOT NULL,
				PRIMARY KEY(num_entity, type_entity)
			)";
		echo traite_rqt($rqt,"CREATE TABLE entities");
	case 14 :
		//RT - Modification commentaire accessibility
		$rqt = "UPDATE parametres SET comment_param = 'Accessibilit� activ�e.\n0 : Non.\n1 : Oui.\n2 : Oui + compatibilit� REM (unit� CSS)' WHERE type_param = 'opac' AND sstype_param = 'accessibility'";
		echo traite_rqt($rqt,"UPDATE parametres SET comment_param for accessibility");
	case 15 :
	    //RT - TS Ajout param�tre d'activation de l'autocompl�tion en recherche simple
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'opac' and sstype_param='search_autocomplete'")) == 0) {
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param, section_param)
        			VALUES (0, 'opac', 'search_autocomplete', '0', '0', 'Autocompl�tion en recherche simple activ�e.\r\n 0 : Non.\r\n 1 : Oui.', 'c_recherche')";
	        echo traite_rqt($rqt, "INSERT opac_search_autocomplete INTO parametres") ;
	    }
	case 16 :
		// DG - Log des diffusions de bannettes - d�tails des �quations ex�cut�es au remplissage
		$rqt = "ALTER TABLE bannettes_diffusions ADD diffusion_equations text";
		echo traite_rqt($rqt,"ALTER TABLE bannettes_diffusions ADD diffusion_equations");
	case 17 :
	    // JP - Ajout du champ modified sur un content_buffer
	    $rqt = "ALTER TABLE dsi_content_buffer ADD modified tinyint(1) NOT NULL DEFAULT 0 AFTER content" ;
	    echo traite_rqt($rqt,"ALTER dsi_content_buffer ADD modified");

	case 18 :
	    // DB / QV : Compatibilit� MySQL 8
	    // Utilisation des back quotes (`) pour Mysql 8. NE PAS LES SUPPRIMER
	    $rqt = "ALTER TABLE thumbnail_sources_entities CHANGE `rank` ranking int(10) NOT NULL DEFAULT 0";
	    echo traite_rqt($rqt,"ALTER TABLE thumbnail_sources_entities CHANGE rank ranking");

	    $rqt = "ALTER TABLE notices_relations CHANGE `rank` ranking int(11)  NOT NULL DEFAULT 0";
	    echo traite_rqt($rqt,"ALTER TABLE notices_relations CHANGE rank ranking");
	case 19 :
	    // DG - Modification de la date de cr�ation d'un document du portfolio en datetime
	    $rqt = "ALTER TABLE cms_documents MODIFY document_create_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	    echo traite_rqt($rqt,"ALTER TABLE cms_documents MODIFY document_create_date DATETIME");
	case 20 :
	    // DG - TS - Parametre pour d�finir la taille maximale du cache des images en gestion
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='img_cache_size' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion)
					VALUES (NULL, 'pmb', 'img_cache_size', '100', 'Taille maximale du cache des images en Mo. Param�tre modifiable uniquement via l\'application.', 1)";
	        echo traite_rqt($rqt,"insert pmb_img_cache_size = '100' into parametres ");
	    }
	    // DG - TS - Parametre pour d�finir la taille maximale du cache des images en opac
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='img_cache_size' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion, section_param)
					VALUES (NULL, 'opac', 'img_cache_size', '100', 'Taille maximale du cache des images en Mo. Param�tre modifiable uniquement via l\'application.', 1, 'a_general')";
	        echo traite_rqt($rqt,"insert opac_img_cache_size = '100' into parametres ");
	    }
	    // DG - TS - Parametre pour d�finir la volum�trie d'images � supprimer lors de la saturation du cache en gestion
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'pmb' and sstype_param='img_cache_clean_size' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion)
					VALUES (NULL, 'pmb', 'img_cache_clean_size', '20', 'Pourcentage du nombre d\'images � supprimer lors de la saturation du cache.  Param�tre modifiable uniquement via l\'application.', 1)";
	        echo traite_rqt($rqt,"insert pmb_img_cache_clean_size = '20' into parametres ");
	    }
	    // DG - TS - Parametre pour d�finir la volum�trie d'images � supprimer lors de la saturation du cache en opac
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='img_cache_clean_size' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion, section_param)
					VALUES (NULL, 'opac', 'img_cache_clean_size', '20', 'Pourcentage du nombre d\'images � supprimer lors de la saturation du cache.  Param�tre modifiable uniquement via l\'application.', 1, 'a_general')";
	        echo traite_rqt($rqt,"insert opac_img_cache_clean_size = '20' into parametres ");
	    }
	    // DG - TS - Parametre pour d�finir le type des images stockees dans le cache opac
	    if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='img_cache_type' "))==0){
	        $rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion, section_param)
					VALUES (NULL, 'opac', 'img_cache_type', 'webp', 'Type d\'image � stocker dans le cache. Param�tre modifiable uniquement via l\'application.', 1, 'a_general')";
	        echo traite_rqt($rqt,"insert opac_img_cache_type = 'webp' into parametres ");
	    }
	case 21 :
	    // DG - Ajout du champ cache_cadre_header sur la table cms_cache_cadres
	    $rqt = "ALTER TABLE cms_cache_cadres ADD cache_cadre_header MEDIUMTEXT NOT NULL" ;
	    echo traite_rqt($rqt,"ALTER cms_cache_cadres ADD cache_cadre_header");
	case 22 :
	    //GN - Alerter l'utilisateur par mail des nouvelles inscriptions aux animations proposees ?
	    $rqt = "ALTER TABLE users ADD user_alert_animation_mail INT(1) UNSIGNED NOT NULL DEFAULT 0 after deflt_animation_unique_registration";
	    echo traite_rqt($rqt,"ALTER TABLE users add user_alert_animation_mail default 0");

	    //GN - Ajout d'un mail pour reception. L'autre mail sert a l'envoi et n'est pas toujours consultable
	    $rqt = "ALTER TABLE users ADD user_email_recipient VARCHAR(255) default '' after user_alert_animation_mail";
	    echo traite_rqt($rqt,"ALTER TABLE users add user_email_recipient default ''");
	case 23 :
	    //GN - Ajout d'une table pour enregistrer les transactions de paiement
	    $rqt = "CREATE TABLE transaction_payments (
                id INT(11) unsigned auto_increment,
                order_number INT NOT NULL,
                payment_date DATETIME NOT NULL,
                payment_status INT(1) NOT NULL,
                payment_organization_status VARCHAR(10) NULL,
                num_user INT NOT NULL,
                num_organization INT(1)NOT NULL,
                PRIMARY KEY (id),
                UNIQUE order_number (order_number)
                ) ";
	    echo traite_rqt($rqt,"create table transaction_payments");

	    //GN - Ajout d'une table pour enregistrer les organismes de paiement
	    $rqt = "CREATE TABLE payment_organization (
                id INT(11) unsigned auto_increment,
                name VARCHAR(255) NOT NULL,
                data mediumblob NULL,
                PRIMARY KEY (id)
                ) ";
	    echo traite_rqt($rqt,"create table payment_organization");

	    //GN - Ajout d'une table d'une table de liaison entre les payments et les comptes
	    $rqt = "CREATE TABLE transaction_compte_payments (
                id INT(11) unsigned auto_increment,
                transaction_num INT NOT NULL,
                compte_num INT NOT NULL,
                amount INT NOT NULL,
                PRIMARY KEY (id)
                )";
	    echo traite_rqt($rqt,"create table transaction_compte_payments");
	case 24 :
		// DB - Modification des tables r�colteur
		$rqt = "ALTER TABLE harvest_field ADD harvest_field_ufield varchar(100) DEFAULT NULL AFTER harvest_field_xml_id";
		echo traite_rqt($rqt,"ALTER TABLE harvest_field ADD harvest_field_ufields");

		$rqt = "ALTER TABLE harvest_search_field CHANGE num_field num_field VARCHAR(25) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"ALTER TABLE harvest_search_field CHANGE num_field VARCHAR(25)");

		$rqt = "ALTER TABLE harvest_src DROP harvest_src_pmb_unimacfield, DROP harvest_src_pmb_unimacsubfield, DROP harvest_src_unimacsubfield";
		echo traite_rqt($rqt,"ALTER TABLE harvest_src DROP harvest_src_pmb_unimacfield, harvest_src_pmb_unimacsubfield, harvest_src_unimacsubfield");

		$rqt = "ALTER TABLE harvest_src CHANGE harvest_src_unimacfield harvest_src_ufield VARCHAR(255) NOT NULL DEFAULT '' ";
		echo traite_rqt($rqt,"ALTER TABLE harvest_src CHANGE harvest_src_unimacfield harvest_src_ufield ");
	case 25:
		//RT - Ajout param�tres utilisateur pour les statuts dans la D.S.I.
		$rqt = "ALTER TABLE users ADD deflt_dsi_diffusion_default_status TINYINT UNSIGNED DEFAULT 1 NOT NULL";
		echo traite_rqt($rqt,"ALTER TABLE users ADD deflt_dsi_diffusion_default_status TINYINT UNSIGNED DEFAULT 1 NOT NULL");
		$rqt = "ALTER TABLE users ADD deflt_dsi_product_default_status TINYINT UNSIGNED DEFAULT 1 NOT NULL";
		echo traite_rqt($rqt,"ALTER TABLE users ADD deflt_dsi_product_default_status TINYINT UNSIGNED DEFAULT 1 NOT NULL");
	case 26:
		// QV - Parametre Content Security Policy (CSP)
		if (pmb_mysql_num_rows(pmb_mysql_query("select 1 from parametres where type_param= 'opac' and sstype_param='content_security_policy' "))==0){
			$rqt="INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, gestion, section_param)
					VALUES (NULL, 'opac', 'content_security_policy', '', 'Permet de d�finir la valeur pour le content security policy (CSP) ou strat�gie de s�curit� du contenu afin de renforcer la s�curit� de votre OPAC.\n\nLaisser la valeur � vide pour ne sp�cifier aucune directive de s�curit� de contenu.', 0, 'a_general')";
			echo traite_rqt($rqt,"insert opac_content_security_policy = '' into parametres");
		}
	case 27:
	    //DG - Ajout d'une cl� primaire aux listes associ�es aux champs personalis�s
	    $rqt = "ALTER TABLE anim_animation_custom_lists ADD id_anim_animation_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE anim_animation_custom_lists ADD id_anim_animation_custom_list ");

	    $rqt = "ALTER TABLE anim_price_type_custom_lists ADD id_anim_price_type_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE anim_price_type_custom_lists ADD id_anim_price_type_custom_list ");

	    $rqt = "ALTER TABLE author_custom_lists ADD id_author_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE author_custom_lists ADD id_author_custom_list ");

	    $rqt = "ALTER TABLE authperso_custom_lists ADD id_authperso_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE authperso_custom_lists ADD id_authperso_custom_list ");

	    $rqt = "ALTER TABLE categ_custom_lists ADD id_categ_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE categ_custom_lists ADD id_categ_custom_list ");

	    $rqt = "ALTER TABLE cms_editorial_custom_lists ADD id_cms_editorial_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE cms_editorial_custom_lists ADD id_cms_editorial_custom_list ");

	    $rqt = "ALTER TABLE collection_custom_lists ADD id_collection_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE collection_custom_lists ADD id_collection_custom_list ");

	    $rqt = "ALTER TABLE collstate_custom_lists ADD id_collstate_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE collstate_custom_lists ADD id_collstate_custom_list ");

	    $rqt = "ALTER TABLE demandes_custom_lists ADD id_demandes_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE demandes_custom_lists ADD id_demandes_custom_list ");

	    $rqt = "ALTER TABLE empr_custom_lists ADD id_empr_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE empr_custom_lists ADD id_empr_custom_list ");

	    $rqt = "ALTER TABLE explnum_custom_lists ADD id_explnum_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE explnum_custom_lists ADD id_explnum_custom_list ");

	    $rqt = "ALTER TABLE expl_custom_lists ADD id_expl_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE expl_custom_lists ADD id_expl_custom_list ");

	    $rqt = "ALTER TABLE notices_custom_lists ADD id_notices_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE notices_custom_lists ADD id_notices_custom_list ");

	    $rqt = "ALTER TABLE gestfic0_custom_lists ADD id_gestfic0_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE gestfic0_custom_lists ADD id_gestfic0_custom_list ");

	    $rqt = "ALTER TABLE indexint_custom_lists ADD id_indexint_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE indexint_custom_lists ADD id_indexint_custom_list ");

	    $rqt = "ALTER TABLE pret_custom_lists ADD id_pret_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE pret_custom_lists ADD id_pret_custom_list ");

	    $rqt = "ALTER TABLE publisher_custom_lists ADD id_publisher_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE publisher_custom_lists ADD id_publisher_custom_list ");

	    $rqt = "ALTER TABLE serie_custom_lists ADD id_serie_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE serie_custom_lists ADD id_serie_custom_list ");

	    $rqt = "ALTER TABLE skos_custom_lists ADD id_skos_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE skos_custom_lists ADD id_skos_custom_list ");

	    $rqt = "ALTER TABLE subcollection_custom_lists ADD id_subcollection_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE subcollection_custom_lists ADD id_subcollection_custom_list ");

	    $rqt = "ALTER TABLE tu_custom_lists ADD id_tu_custom_list INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	    echo traite_rqt($rqt,"ALTER TABLE tu_custom_lists ADD id_tu_custom_list ");

	case 28:
	    //DB - Nettoyage table translation
	    $rqt = "delete from translation where trans_table='parametres' and trans_num not in (select id_param from parametres where concat(type_param,'_',sstype_param) in ('".implode("','", parameter::TRANSLATED_PARAMETERS)."'))";
	    echo traite_rqt($rqt,"CLEAN TABLE translation");

	case 29 :
	    // DB - Ajout index sur explnum_mimetype
	    $add_index = true;
	    $req = "SHOW INDEX FROM explnum WHERE Key_name='i_explnum_mimetype' ";
	    $res = pmb_mysql_query($req);
	    if($res && pmb_mysql_num_rows($res)){
	        $add_index=false;
	    }
        if($add_index){
            @set_time_limit(0);
            pmb_mysql_query("set wait_timeout=28800");
            $rqt = "ALTER TABLE explnum ADD INDEX i_explnum_mimetype(explnum_mimetype)";
            echo traite_rqt($rqt,"alter table explnum add index i_explnum_mimetype");
        }

	case 30 :
	    //DG - Date de diffusion sur les d�comptes
	    $rqt = "ALTER TABLE rent_accounts ADD account_diffusion_date datetime";
	    echo traite_rqt($rqt,"ALTER TABLE rent_accounts ADD account_diffusion_date");

	    //DG - Date de fin de droits sur les d�comptes
	    $rqt = "ALTER TABLE rent_accounts ADD account_rights_date datetime";
	    echo traite_rqt($rqt,"ALTER TABLE rent_accounts ADD account_rights_date");

	    //DG - Droits illimit�s sur les d�comptes
	    $rqt = "ALTER TABLE rent_accounts ADD account_unlimited_rights int(1) unsigned not null default 0";
	    echo traite_rqt($rqt,"ALTER TABLE rent_accounts ADD account_unlimited_rights");

	case 31:
		// QV - Ajout d'un param�tre pour la recherche des synonymes (Gestion et OPAC)
		if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'opac' AND sstype_param='synonym_search' "))==0){
			$rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                VALUES('opac', 'synonym_search', '1', 'Activer la recherche des synonymes d\'un mot\n0 : non\n1 : oui', 'c_recherche', 0)" ;
			echo traite_rqt($rqt,"INSERT opac_synonym_search INTO parametres") ;
		}
		if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'pmb' AND sstype_param='synonym_search' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                VALUES(0, 'pmb', 'synonym_search', '1', 'Activer la recherche des synonymes d\'un mot\n0 : non\n1 : oui', 'c_recherche', 0)" ;
			echo traite_rqt($rqt,"INSERT pmb_synonym_search INTO parametres") ;
		}

	case 32:
	    // JP - Ajout d'un param�tre pour la connexion � l'API sphinx pour le multi-bases
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'sphinx' AND sstype_param='api_connect' "))==0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param)
                VALUES (NULL, 'sphinx', 'api_connect', '127.0.0.1:9312', 'Param�tre de connexion � l\'API du serveur sphinx :\n hote:port', '')";
	        echo traite_rqt($rqt,"INSERT sphinx_api_connect = '127.0.0.1:9312' INTO parametres ");
	    }

	case 33:
	    // DB - Ajout d'un champ host_name et d'un champ alive_at dans la table taches
	    $rqt = "ALTER TABLE taches ADD host_name varchar(255) NOT NULL DEFAULT '' AFTER id_process,  ADD alive_at TIMESTAMP NULL DEFAULT NULL AFTER host_name ";
	    echo traite_rqt($rqt,"ALTER TABLE taches ADD host_name, alive_at ");
	case 34:
        // GN - Ajout d'un message qui indique si l'emprunteur poss�de l'exemplaire
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'selfservice' and sstype_param='already_loaned' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
				VALUES (0, 'selfservice', 'already_loaned', 'l\'emprunteur poss�de l\'exemplaire', '1', 'Ajout d\'un message qui indique si l\'emprunteur poss�de l\'exemplaire') ";
			echo traite_rqt($rqt,"INSERT selfservice_already_loaned INTO parametres") ;
		}

		// GN - Ajout d'un message pour la gestion du statut de la r�servations de l'exemplaire
		if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'selfservice' and sstype_param='expl_status' "))==0){
			$rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
				VALUES (0, 'selfservice', 'expl_status', '', '1', 'Ajout d\'un message pour la gestion des statuts de l\'exemplaire') ";
			echo traite_rqt($rqt,"INSERT selfservice_expl_status INTO parametres") ;
		}
	case 35:
	    // DG - Ajout index sur num_object et type_object de la table vedette_link
	    $add_index = true;
	    $req = "SHOW INDEX FROM vedette_link WHERE Key_name='i_object' ";
	    $res = pmb_mysql_query($req);
	    if($res && pmb_mysql_num_rows($res)){
	        $add_index=false;
	    }
	    if($add_index){
	        $rqt = "ALTER TABLE vedette_link ADD INDEX i_object(num_object, type_object)";
	        echo traite_rqt($rqt,"alter table vedette_link add index i_object");
	    }
	    
	case 36:
	    // JP - Ajout d'un param�tre pour la taille maximum d'un logo dans le contenu �ditorial
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'cms' AND sstype_param='img_pics_max_size'"))==0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'cms', 'img_pics_max_size', '640', 'Taille maximale des logos du contenu �ditorial, en largeur ou en hauteur', '', 0)";
	        echo traite_rqt($rqt, "INSERT cms_img_pics_max_size = 640 INTO parameters");
	    }
	    
	case 37:
	    //JP - Ajout d'un param�tre pour le remplacement du champ identifiant par le champ mail dans le formulaire du lecteur � l'OPAC
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param='empr' AND sstype_param='username_with_mail'")) == 0) {
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
                    VALUES (0, 'empr', 'username_with_mail', '0', '1', 'Activer le remplacement du champ identifiant par le champ mail dans le formulaire changement de profil � l\'OPAC\n 0: Non \n 1: Oui') ";
	        echo traite_rqt($rqt, 'INSERT username_with_mail INTO parametres');
	    }
	    
	case 38:
	    //JP - Nombre de notices max diffus�es dans une bannette par mail
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'dsi' AND sstype_param = 'bannette_max_nb_notices_per_mail'")) == 0){
	        $rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                    VALUES ('dsi', 'bannette_max_nb_notices_per_mail', '100', 'Nombre maximum de notices diffus�es dans une bannette par mail.', '', 0)";
	        echo traite_rqt($rqt, "INSERT dsi_bannette_max_nb_notices_per_mail INTO parametres");
	    }
    
	case 39:
	    // JP - Ajout d'une table pour g�rer la diffusion manuelle
	    $rqt = "CREATE TABLE IF NOT EXISTS dsi_send_queue (
            id_send_queue INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            channel_type VARCHAR(255) NOT NULL DEFAULT '',
            settings mediumblob NOT NULL,
            num_subscriber_diffusion INT(11) UNSIGNED NOT NULL,
            num_diffusion_history INT(11) UNSIGNED NOT NULL,
            flag INT(1) UNSIGNED NOT NULL DEFAULT 0
        )";
	    echo traite_rqt($rqt, "CREATE TABLE dsi_send_queue");
	    
	case 40:
	    //JP - QV - Activer la mise en cache des images dans les animations
	    if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'animations' AND sstype_param ='active_image_cache' ")) == 0){
	        $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                    VALUES (0, 'animations', 'active_image_cache', '0', 'Activer la mise en cache des images dans les animations\n 0: Non \n 1: Oui', '', 0)";
	        echo traite_rqt($rqt,"INSERT animations_active_image_cache INTO parametres");
	    }
	case 41:
	    // TS - Ajout d'une nouvelle option du parametre resa_alert_localized pour les notifications aux utilisateurs du site de retrait
	    $rqt = "update parametres set comment_param='Mode de notification par email des nouvelles r�servations aux utilisateurs ? \n0 : Recevoir toutes les notifications \n1 : Notification des utilisateurs du site de gestion du lecteur \n2 : Notification des utilisateurs associ�s � la localisation par d�faut en cr�ation d\'exemplaire \n3 : Notification des utilisateurs du site de gestion et de la localisation d\'exemplaire \n4 : Notification des utilisateurs du site de retrait' where type_param= 'pmb' and sstype_param='resa_alert_localized' ";
	    echo traite_rqt($rqt,"update pmb_resa_alert_localized into parametres");
	case 42:
		// RT - Ajout d'un param�tre utilisateur permettant de d�finir un propri�taire par d�faut en import d'exemplaires UNIMARC
		$rqt = "ALTER TABLE users ADD deflt_import_lenders TINYINT UNSIGNED DEFAULT 1 NOT NULL ";
		echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_import_lenders");
}



/******************** JUSQU'ICI **************************************************/
/* PENSER � faire +1 au param�tre $pmb_subversion_database_as_it_shouldbe dans includes/config.inc.php */
/* COMMITER les deux fichiers addon.inc.php ET config.inc.php en m�me temps */

echo traite_rqt("update parametres set valeur_param='".$pmb_subversion_database_as_it_shouldbe."' where type_param='pmb' and sstype_param='bdd_subversion'","Update to $pmb_subversion_database_as_it_shouldbe database subversion.");
echo "<table>";