<?php

require_once __DIR__ . '/data/variables.php';
require_once INCLUDES_DIR . '/vendor/autoload.php';
require_once DATA_DIR . '/functions.php';

$mh_logs = new MhDB(MhLogs::class);
