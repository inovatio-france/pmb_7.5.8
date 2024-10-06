<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: params.inc.php,v 1.10.4.2 2024/04/30 14:14:47 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $pmb_clean_nb_elements;
define('NOEXPL_PAQUET_SIZE', $pmb_clean_nb_elements);
define('REINDEX_PAQUET_SIZE', $pmb_clean_nb_elements);
define('REINDEX_GLOBAL_PAQUET_SIZE', 2000);
define('REINDEX_BY_FIELDS_PAQUET_SIZE', 50000);
define('REINDEX_BY_CALLABLES_FIELDS_PAQUET_SIZE', 5000);
define('ACQUISITION_PAQUET_SIZE', $pmb_clean_nb_elements);
define('EMPR_PAQUET_SIZE', $pmb_clean_nb_elements);
define('GAUGE_SIZE', 560);