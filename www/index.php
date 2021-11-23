<?php

require_once __DIR__ . '/../app/config/constant.php';

$container = require __DIR__ . '/../app/bootstrap.php';

$container->getService('application')->run();

