<?php
require('../vendor/autoload.php');
date_default_timezone_set('Asia/Tokyo');

$app = require('../app/bootstrap/app.php');

require('../app/bootstrap/bootstrap.php');

$app->run();
