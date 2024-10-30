<?php

$id = $_REQUEST['id'];

$requestXML = ConstantContent::accessAPI('request','get',array(), 'id/'.$id.'/');

$doc = new DOMDocument();
$doc->loadXML($requestXML);

$request = ConstantContent::findFirstNode($doc, 'request');

$action = 'update';
$request_id = ConstantContent::getTextNode($request, 'request_id');
$title = ConstantContent::getTextNode($request, 'title');
$type = ConstantContent::getTextNode($request, 'type');
$authors = ConstantContent::getTextNode($request, 'authors');
$deadline = ConstantContent::getTextNode($request, 'deadline');
$description = ConstantContent::getTextNode($request, 'comment');
$subjects = ConstantContent::getTextNode($request, 'subjects');
$price = ConstantContent::getTextNode($request, 'price');
$wordcount = ConstantContent::getTextNode($request, 'word_count');
$item_count = ConstantContent::getTextNode($request, 'number_of_items');
$raw_type = ConstantContent::getTextNode($request, 'raw_type');
preg_match('/c_([0-9]+)_(.*)/', $raw_type, $matches);
$reqType = 'call_for_articles';
if (!empty($matches) && isset($matches[1])) {
    switch ($matches[1]) {
        case 2:
            $reqType = 'targeted_request_study';
            break;
        case 3:
            $reqType = 'targeted_request_certification';
            break;
        case 4:
            $reqType = 'targeted_request_category';
            break;
        case 5:
            $reqType = 'call_for_articles';
            $type = $matches[2];
            break;
        case 7:
            $reqType = 'expert_request';
            break;
        case 8:
            $reqType = 'targeted_request_country';
            break;
        case 9:
            $reqType = 'private_team';
            break;
        case 10:
            $reqType = 'targeted_request';
            break;
    }
}
$authors = explode(' ',trim($raw_type));
if (!empty($authors) && is_numeric($authors[0])) {
    $reqType = 'private_writer';
}
if (strpos($request_id,'PR') !== false) {
    $requestClass = 'legacy';
} else {
    $requestClass = 'pool';
}
require_once CONSTANTCONTENT_INCLUDE_DIR . 'requestcommon.php';

exit;
