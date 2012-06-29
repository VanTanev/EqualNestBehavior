<?php

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('', __DIR__ . '/../vendor/propel/propel1/generator/lib/util/PropelQuickBuilder.php');
$loader->add('', __DIR__);

set_include_path(__DIR__ . '/../vendor/phing/phing/classes' . PATH_SEPARATOR . get_include_path());
