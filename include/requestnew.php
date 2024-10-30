<?php

$action = 'create';
$title = '';
$type = '';
$authors = '';
$description = '';
$subjects = '';
$price = '';
$wordcount = '';
$item_count = '';

$date = new DateTime();
$date->setTime(17, 0, 0);
$date->modify('+7 day');
$deadline = $date->format("Y-m-d h:i a");
$requestClass = 'both';
require_once CONSTANTCONTENT_INCLUDE_DIR . 'requestcommon.php';

exit;
