<?php

$rawRequest = array_map('stripslashes_deep', $_REQUEST);

$toReturn = array();
$toReturn['draw'] = (int) $rawRequest['draw'];
$toReturn['success'] = false;
$toReturn['data'] = array();

$extraPath = '';
if (!empty($rawRequest['iDisplayStart'])) {
    $extraPath .= 'start/' . (int) $rawRequest['iDisplayStart'] . '/';
} else {
    $extraPath .= 'start/0/';
}
if (!empty($rawRequest['iDisplayLength'])) {
    $extraPath .= 'length/' . (int) $rawRequest['iDisplayLength'] . '/';
}
if (!empty($rawRequest['sSearch'])) {
    $extraPath .= 'keywords/' . $rawRequest['sSearch'] . '/';
}
if (!empty($rawRequest['sSearch_1'])) {
    $extraPath .= 'category/' . $rawRequest['sSearch_1'] . '/';
}

if (!empty($rawRequest['data'])) {
    $toReturn = array('data' => array());

    if ($rawRequest['data'] == 'catalog') {
        $catalog = ConstantContent::accessAPI('catalog', 'get', array(), $extraPath);

        $doc = new DOMDocument();
        $doc->loadXML($catalog);
        $documents = $doc->getElementsByTagName('document');

        $toReturn = ConstantContentParser::catalog($documents);
        $toReturn['recordsTotal'] = ConstantContent::getTextNode($doc, 'maxFound');
        $toReturn['recordsFiltered'] = ConstantContent::getTextNode($doc, 'maxFound');
    }

    if ($rawRequest['data'] == 'notifications') {
        $notificationXML = ConstantContent::accessAPI('notification');
        $noticationDoc = new DOMDocument();
        $noticationDoc->loadXML($notificationXML);
        $notifications = $noticationDoc->getElementsByTagName('event');

        $toReturn = ConstantContentParser::notifications($notifications);
        $toReturn['recordsTotal'] = count($toReturn['data']);
    }

    if ($rawRequest['data'] == 'requests') {
        $source = 'requests';
        if (!empty($rawRequest['source'])) {
            if ($rawRequest['source'] == 'dashboard') {
                $source = 'dashboard';
            }
        }
        $archived = false;
        $extraPath = '';
        if (!empty($rawRequest['archived']) && $rawRequest['archived'] == 'true') {
            $extraPath = 'archived/true/';
            $archived = true;
        }
        $requestML = ConstantContent::accessAPI('request', 'get', null, $extraPath);
        $requestDoc = new DOMDocument();
        $requestDoc->loadXML($requestML);
        $requests = $requestDoc->getElementsByTagName('request');
        $toReturn = ConstantContentParser::requests($requests, $archived, $source);
        $toReturn['recordsTotal'] = count($toReturn['data']);
    }

    if ($rawRequest['data'] == 'content') {
        $contentXML = ConstantContent::accessAPI('document');
        $doc = new DOMDocument();
        $doc->loadXML($contentXML);
        $documents = $doc->getElementsByTagName('document');
        $toReturn = ConstantContentParser::documents($documents);
        $toReturn['recordsTotal'] = count($toReturn['data']);
    }

    if ($rawRequest['data'] == 'orders') {
        $orderXML = ConstantContent::accessAPI('order');
        $doc = new DOMDocument();
        $doc->loadXML($orderXML);
        $orders = $doc->getElementsByTagName('order');
        $balance = ConstantContent::getTextNode($doc, 'credit_balance');
        $toReturn = ConstantContentParser::orders($orders, $balance);
        $toReturn['recordsTotal'] = count($toReturn['data']);
    }

    if ($rawRequest['data'] == 'revisions') {
        $contentXML = ConstantContent::accessAPI('revision');
        $doc = new DOMDocument();
        $doc->loadXML($contentXML);
        $revisions = $doc->getElementsByTagName('revisions');
        $toReturn = ConstantContentParser::revisions($revisions);
        $toReturn['recordsTotal'] = count($toReturn['data']);
    }

    if ($rawRequest['data'] == 'buyCredit') {
        $price = $_REQUEST['price'];
        $result = ConstantContent::accessAPI('credit', 'get', array(), 'buy/' . (int) $price . '/');
        $prices = ConstantContent::get('price_list');
        if (!isset($prices[$price])) {
            header("Location: " .admin_url('admin.php?page=constant-content-error&code=1'));
            exit;
        }
        $doc = new DOMDocument();
        $doc->loadXML($result);
        $credit_url = ConstantContent::getTextNode($doc, 'credit_url');
        $value = $_REQUEST['value'];
        header("Location: " . $credit_url);
    }
}

if (!empty($rawRequest['type']) && !empty($rawRequest['document_id'])) {
    $orderXML = ConstantContent::addToOrder($rawRequest['document_id'], $rawRequest['type']);
    $doc = new DOMDocument();
    $doc->loadXML($orderXML);
    $result = ConstantContent::checkElementValue($doc, 'success', 'true');
    $order = ConstantContent::findFirstNode($doc, 'order');
    $toReturn = array(
        'success' => ConstantContent::getTextNode($doc, 'success'),
        'message' => ConstantContent::getTextNode($doc, 'message'),
        'order_id' => ConstantContent::getTextNode($order, 'order_id'),
    );
}

if (!empty($rawRequest['hide']) && !empty($rawRequest['hidestate'])) {
    $toHide = ConstantContent::get('dashboard_hide');
    $hideItem = $rawRequest['hide'];
    $hideState = $rawRequest['hidestate'];
    if ($hideState == 'true') {
        $toHide[$hideItem] = true;
    }
    if ($hideState == 'false') {
        $toHide[$hideItem] = false;
    }
    ConstantContent::save('dashboard_hide', $toHide);
    $toReturn = array('success' => true);
}

if (!empty($rawRequest['download'])) {
    if (strtolower($rawRequest['download']) == 'download revision') {
        $id = (int) $rawRequest['download_id'];
        $result = ConstantContent::accessAPI('file', 'get', array(), 'id/' . $id . '/type/revision/xml/true/');

        $doc = new DOMDocument();
        $doc->loadXML($result);

        $header1 = ConstantContent::getTextNode($doc, 'header1');
        $header2 = ConstantContent::getTextNode($doc, 'header2');
        $content = ConstantContent::getTextNode($doc, 'content');

        header($header1);
        header($header2);
        print base64_decode($content);
        ob_flush();
        exit;
    }
    if (strtolower($rawRequest['download']) == 'download document') {
        $id = (int) $rawRequest['download_id'];
        $result = ConstantContent::accessAPI('file', 'get', array(), 'id/' . $id . '/xml/true/');

        $doc = new DOMDocument();
        $doc->loadXML($result);

        $header1 = ConstantContent::getTextNode($doc, 'header1');
        $header2 = ConstantContent::getTextNode($doc, 'header2');
        $content = ConstantContent::getTextNode($doc, 'content');

        header($header1);
        header($header2);
        print base64_decode($content);
        ob_flush();
        exit;
    }
}

if (!empty($rawRequest['request_action'])) {
    if (!empty($rawRequest['title']) || !empty($rawRequest['type']) || !empty($rawRequest['deadline']) ||
            !empty($rawRequest['authors']) || !empty($rawRequest['description']) || !empty($rawRequest['subjects']) ||
            !empty($rawRequest['price']) || !empty($rawRequest['wordcount']) || !empty($rawRequest['item_count'])) {
        $postValues = array(
            'title' => $rawRequest['title'],
            'type' => $rawRequest['type'],
            'deadline' => $rawRequest['deadline'],
            'authors' => $rawRequest['authors'],
            'description' => $rawRequest['description'],
            'subjects' => $rawRequest['subjects'],
            'price' => $rawRequest['price'],
            'wordcount' => $rawRequest['wordcount'],
            'item_count' => $rawRequest['item_count']
        );
        if (!empty($rawRequest['authors'])) {
            $postValues['authors'] = 'c_5_' . $postValues['type'];
            switch (strtolower($rawRequest['authors'])) {
                case 'targeted_request':
                    $postValues['authors'] = 'c_10_' . $postValues['type'];
                    break;
                case 'targeted_request_country':
                    if (!empty($rawRequest['country'])) {
                        $postValues['authors'] = $rawRequest['country'];
                    }
                    break;
                case 'targeted_request_study':
                    if (!empty($rawRequest['study'])) {
                        $postValues['authors'] = $rawRequest['study'];
                    }
                    break;
                case 'targeted_request_certification':
                    if (!empty($rawRequest['certfication'])) {
                        $postValues['authors'] = $rawRequest['certfication'];
                    }
                    break;
                case 'targeted_request_category':
                    if (!empty($rawRequest['categories'])) {
                        $postValues['authors'] = $rawRequest['categories'];
                    }
                    break;
                case 'expert_request':
                    if (!empty($rawRequest['expert'])) {
                        $postValues['authors'] = $rawRequest['expert'];
                    }
                    break;
                case 'private_team':
                    if (!empty($rawRequest['team'])) {
                        $postValues['authors'] = $rawRequest['team'];
                    }
                    break;
                case 'private_writer':
                    if (!empty($rawRequest['writers'])) {
                        if (is_array($rawRequest['writers'])) {
                            $postValues['authors'] = implode(' ', $rawRequest['writers']);
                        } else {
                            $postValues['authors'] = $rawRequest['writers'];
                        }
                    }
                    break;
                case 'casting_call':
                    $postValues['authors'] = 'casting_call';
                    break;
                case 'call_for_articles':
                default;
                    break;
            }
        }
        if ($rawRequest['request_action'] == 'update') {
            $resultXML = ConstantContent::accessAPI('request', 'put', $postValues, 'id/' . $rawRequest['request'] . '/');
        }
        if ($rawRequest['request_action'] == 'create') {
            $resultXML = ConstantContent::accessAPI('request', 'post', $postValues);
        }
    }
}

if (!empty($rawRequest['massRequestAction'])) {
    if (!empty($rawRequest['massRequestAction']['archive']) && is_array($rawRequest['massRequestAction']['archive'])) {
        foreach ($rawRequest['massRequestAction']['archive'] as $id) {
            $postValues = array('archive' => 'true');
            $resultXML = ConstantContent::accessAPI('request', 'put', $postValues, 'id/' . $id . '/');
        }
    }
    if (!empty($rawRequest['massRequestAction']['unarchive']) && is_array($rawRequest['massRequestAction']['unarchive'])) {
        foreach ($rawRequest['massRequestAction']['unarchive'] as $id) {
            $postValues = array('unarchive' => 'true');
            $resultXML = ConstantContent::accessAPI('request', 'put', $postValues, 'id/' . $id . '/');
        }
    }
    if (!empty($rawRequest['massRequestAction']['close']) && is_array($rawRequest['massRequestAction']['close'])) {
        foreach ($rawRequest['massRequestAction']['close'] as $id) {
            $postValues = array('close' => 'true');
            $resultXML = ConstantContent::accessAPI('request', 'put', $postValues, 'id/' . $id . '/');
        }
    }
    if (!empty($rawRequest['massRequestAction']['reopen']) && is_array($rawRequest['massRequestAction']['reopen'])) {
        foreach ($rawRequest['massRequestAction']['reopen'] as $id) {
            $postValues = array('reopen' => 'true');
            $resultXML = ConstantContent::accessAPI('request', 'put', $postValues, 'id/' . $id . '/');
        }
    }
}

if (!empty($rawRequest['massaction'])) {
    $message = '';
    if (!empty($rawRequest['massaction']) && is_array($rawRequest['massaction'])) {
        foreach ($rawRequest['massaction'] as $action => $items) {
            foreach ($items as $itemType => $itemList) {
                foreach ($itemList as $item => $title) {
                    $extraUrl = 'xml/true/id/' . (int) $item . '/output/simple/';
                    if ($itemType == 'document') {
                        $extraUrl .= 'type/document/';
                    } elseif ($itemType == 'revision') {
                        $extraUrl .= 'type/revision/';
                    } else {
                        continue;
                    }
                    $contentXML = ConstantContent::accessAPI('file', 'get', array(), $extraUrl);
                    $doc = new DOMDocument();
                    $doc->loadXML($contentXML);
                    $content = base64_decode(ConstantContent::getTextNode($doc, 'content'));
                    switch (strtolower(($action))) {
                        case 'create_post':
                            $result = ConstantContent::create($item, $title, $content, 'post', 'publish', false);
                            if ($result) {
                                $message .= '<p>Post Created for : ' . $title . '</p>';
                            }
                            break;
                        case 'create_draft_post':
                            $result = ConstantContent::create($item, $title, $content, 'post', 'draft', false);
                            if ($result) {
                                $message .= '<p>Draft Post Created for : ' . $title . '</p>';
                            }
                            break;
                        case 'create_page':
                            $result = ConstantContent::create($item, $title, $content, 'page', 'publish', false);
                            if ($result) {
                                $message .= '<p>Page Created for : ' . $title . '</p>';
                            }
                            break;
                        case 'create_draft_page':
                            $result = ConstantContent::create($item, $title, $content, 'page', 'draft', false);
                            if ($result) {
                                $message .= '<p>Draft Page Created for : ' . $title . '</p>';
                            }
                            break;
                    }
                }
            }
        }
    }
    $toReturn = array(
        'success' => true,
        'message' => $message
    );
}

// Order Actions
if (!empty($rawRequest['remove'])) {
    $toRemove = array('remove' => $rawRequest['document']);
    $order_id = $rawRequest['order_id'];
    $resultXML = ConstantContent::accessAPI('order', 'put', $toRemove, $order_id);
}
if (!empty($rawRequest['purchase'])) {
    $purchase = array('purchase' => 'true');
    $order_id = $rawRequest['order_id'];
    $resultXML = ConstantContent::accessAPI('order', 'put', $purchase, $order_id);
}

if (!empty($rawRequest['delete'])) {
    $purchase = array('delete' => 'true');
    $order_id = $rawRequest['order_id'];
    $resultXML = ConstantContent::accessAPI('order', 'put', $purchase, $order_id);
}

if (!empty($rawRequest['revisionAction']) && !empty($rawRequest['revision'])) {
    $result = null;
    $instructions = $rawRequest['instructions'];
    $price = $rawRequest['price'];
    $document = $rawRequest['document'];
    $revision = (int) $rawRequest['revision'];
    switch (strtolower($rawRequest['revisionAction'])) {
        case 'view order':
            wp_redirect(admin_url('admin.php?page=constant-content-orders&order_id=' . (int) $rawRequest['order']));
            exit;
            break;
        case 'submit revision request':
            $post = array('method' => 'submit', 'id' => $revision, 'instructions' => $instructions, 'price' => $price);
            $resultXML = ConstantContent::accessAPI('revision', 'post', $post);
            break;
        case 'counter revision price':
            $post = array('method' => 'counter', 'id' => $revision, 'instructions' => $instructions, 'price' => $price);
            $resultXML = ConstantContent::accessAPI('revision', 'post', $post);
            break;
        case 'accept revision price':
            $post = array('method' => 'accept', 'id' => $revision);
            break;
        case 'reject revision price':
            $post = array('method' => 'reject', 'id' => $revision);
            $resultXML = ConstantContent::accessAPI('revision', 'post', $post);
            break;
        case "request new revision":
            $post = array('method' => 'restart', 'id' => $revision);
            $resultXML = ConstantContent::accessAPI('revision', 'post', $post);
            break;
        case "accept revision":
            $post = array('method' => 'accept', 'id' => $revision);
            $resultXML = ConstantContent::accessAPI('revision', 'post', $post);
            break;
        case "reject revision":
            $post = array('method' => 'reject', 'id' => $revision);
            $resultXML = ConstantContent::accessAPI('revision', 'post', $post);
            break;
        case "buy revision":
            $resultXML = ConstantContent::addToOrder($document, 'revision');
            break;
    }
}


if (!empty($rawRequest['fileID'])) {
    $extraUrl = 'id/' . (int) $rawRequest['fileID'] . '/output/simple/';
    if (!empty($rawRequest['license']) && $rawRequest['license'] == 'revision') {
        $extraUrl .= 'type/revision/';
    }
    $content = ConstantContent::accessAPI('file', 'get', array(), $extraUrl);
    if (!empty($rawRequest['publishAction'])) {
        switch (strtolower($rawRequest['publishAction'])) {
            case 'create_post':
                $toReturn['url'] = ConstantContent::create($rawRequest['fileID'], $rawRequest['title'], $content, 'post', 'publish');
                break;
            case 'create_draft_post':
                $toReturn['url'] = ConstantContent::create($rawRequest['fileID'], $rawRequest['title'], $content, 'post', 'draft');
                break;
            case 'create_page':
                $toReturn['url'] = ConstantContent::create($rawRequest['fileID'], $rawRequest['title'], $content, 'page', 'publish');
                break;
            case 'create_draft_page':
                $toReturn['url'] = ConstantContent::create($rawRequest['fileID'], $rawRequest['title'], $content, 'page', 'draft');
                break;
        }
        if (!empty($toReturn['url'])) {
            $toReturn['success'] = true;
        }
    }
}

if (empty($toReturn['success']) && !empty($resultXML)) {
    $doc = new DOMDocument();
    $doc->loadXML($resultXML);
    $toReturn = array(
        'success' => ConstantContent::getTextNode($doc, 'success'),
        'message' => ConstantContent::getTextNode($doc, 'message'),
        'xml' => $resultXML
    );
    $toReturn['credit_balance'] =  ConstantContent::getTextNode($doc, 'credit_balance');
}

wp_send_json($toReturn);
