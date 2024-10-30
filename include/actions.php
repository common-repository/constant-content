<?php

$message = '';
$resultXML = null;

//Credit Purchase
if (!empty($_REQUEST['purchase_credits'])) {
    $price = $_REQUEST['price'];
    $result = ConstantContent::accessAPI('credit', 'get', array(), 'buy/' . (int) $price . '/');
    $doc = new DOMDocument();
    $doc->loadXML($result);
    $credit_url = ConstantContent::getTextNode($doc, 'credit_url');
}

// For Requests
if (!empty($_REQUEST['revision'])) {
    $extraUrl = 'document/' . (int) $_REQUEST['document'] . '/';
    $revisionXML = ConstantContent::accessAPI('revision', 'get', array(), $extraUrl);
    $doc = new DOMDocument();
    $doc->loadXML($revisionXML);
    $revision = ConstantContent::findFirstNode($doc, 'revision');
    $revisionID = ConstantContent::getTextNode($revision, 'id');
    wp_redirect(admin_url('admin.php?page=constant-content-revision-edit&id=' . $revisionID));
    exit;
}

if (!empty($_REQUEST['license']) && !empty($_REQUEST['document'])) {
    $resultXML = ConstantContent::addToOrder($_REQUEST['document'], $_REQUEST['license']);
    $doc = new DOMDocument();
    $doc->loadXML($resultXML);
    $result = ConstantContent::checkElementValue($doc, 'success', 'true');
    if ($result) {
        $order_id = ConstantContent::getTextNode($doc, 'order_id');
        wp_redirect(admin_url('admin.php?page=constant-content-orders&order_id=' . (int) $order_id));
        exit;
    }
}

if (!empty($resultXML)) {
    $doc = new DOMDocument();
    $doc->loadXML($resultXML);
    $result = ConstantContent::checkElementValue($doc, 'success', 'true');
    $message .= ConstantContent::getTextNode($doc, 'message');
}
print_r($resultXML);