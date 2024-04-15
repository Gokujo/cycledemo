<?php

require_once __DIR__ . '/data/variables.php';
require_once INCLUDES_DIR . '/vendor/autoload.php';
require_once DATA_DIR . '/functions.php';

spl_autoload_register(function ($class_name) {
	global $mh_loader_paths;

	$found = false;

	foreach ($mh_loader_paths as $path) {
		$dir_data = dirToArray($path);
		foreach ($dir_data as $data) {
			if ($class_name === str_replace('.php', '', $data)) {
				include_once "{$path}/{$data}";
				$found = true;
				break;
			}
		}
		if ($found) break;
	}

});

$mh_logs = new MhDB(MhLog::class);

$log = new MhLog();
$log->setType('test');
$log->setPlugin('test plugin');
$log->setFnName('test fn');
$log->setTime(new DateTimeImmutable());
$log->setMessage('test message');
$mh_logs->create($log);

var_dump($mh_logs->get(1));