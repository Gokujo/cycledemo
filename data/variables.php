<?php

define('ROOT_DIR', dirname(__FILE__, 2));
const DATA_DIR = ROOT_DIR . '/data';
const INCLUDES_DIR = ROOT_DIR . '/includes';

include_once DATA_DIR . '/db.php';

$mh_loader_paths = [
	INCLUDES_DIR . '/abstract',
	INCLUDES_DIR . '/class',
	INCLUDES_DIR . '/repository',
	INCLUDES_DIR . '/static',
];

$mh_models_paths = [
	INCLUDES_DIR . '/model',
];

$mh_loader_paths = array_merge($mh_models_paths, $mh_loader_paths);
