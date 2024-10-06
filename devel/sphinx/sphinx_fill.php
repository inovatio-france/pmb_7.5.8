<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sphinx_fill.php,v 1.12.6.3 2024/04/26 15:44:40 dbellamy Exp $

$base_path = __DIR__ . '/../..';
$base_noheader = 1;
$base_nocheck = 1;
$base_nobody = 1;
$base_nosession = 1;

ini_set('display_errors', 0);
error_reporting(0);

$_SERVER['REQUEST_URI'] = '';

// R�cup�rer les arguments en ligne de commande
$argv = $_SERVER['argv'];

// Supprimer le nom du script lui-m�me
array_shift($argv);

global $database;

// Recherche de l'argument --db dans les arguments fournis pour r�cup�rer le nom de la base de donn�es
foreach ($argv as $key => $arg) {
    if (strpos($arg, '--db=') === 0) {
        $database = substr($arg, 5);
        unset($argv[$key]);
        break;
    }
}

require_once $base_path.'/includes/init.inc.php';
require_once $class_path.'/parametres_perso.class.php';

require_once $class_path.'/sphinx/sphinx_records_indexer.class.php';
require_once $class_path.'/sphinx/sphinx_titres_uniformes_indexer.class.php';

require_once 'progress_bar.php';

// Si aucun argument n'est fourni, utiliser toutes les entites r�pertoriees
$entities = array(
    'records',
    'titres_uniformes',
    'series',
    'categories',
    'collections',
    'subcollections',
    'authperso',
    'indexint',
    'authors',
    'concepts',
    'explnums',
    'publishers'
);

// Si des arguments sont fournis, utiliser ceux-ci comme entites a traiter
if (!empty($argv)) {
    $entities = $argv;
}

// file_put_contents("/tmp/index.log", '');
// $m0 = memory_get_peak_usage(true);
// file_put_contents("/tmp/index.log", 'memory_peak_usage before index= '.$m0.PHP_EOL, FILE_APPEND);

$flag = false;
foreach($entities as $entity){
	$index_class = 'sphinx_' . $entity . '_indexer';

	if(class_exists($index_class)){
		$flag = true;

		$sconf = new $index_class();
		$sconf->checkExistingIndexes();
		print $sconf->fillIndexes([], true);
	}
}

if (!$flag) {
	print 'Aucune entite connue. ("records", "titres_uniformes", "series", "categories", "collections", "subcollections", "authperso", "indexint", "authors", "concepts", "explnums", "publishers")';
}

// $m1 = memory_get_peak_usage(true);
// file_put_contents("/tmp/index.log", 'memory_peak_usage after index = '.$m1.PHP_EOL, FILE_APPEND);
// file_put_contents("/tmp/index.log", 'memory_peak_usage differentiel = '.($m1-$m0).PHP_EOL, FILE_APPEND);


