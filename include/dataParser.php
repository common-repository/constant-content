<?php

class ConstantContentParser {

    public static function notifications($notifications) {
        $toReturn = array('data' => array());
        foreach ($notifications as $notification) {
            $notification_id = ConstantContent::getTextNode($notification, 'id');
            $title = ConstantContent::getTextNode($notification, 'title');
            $details = ConstantContent::getTextNode($notification, 'details');
            $date = ConstantContent::getTextNode($notification, 'date');
            $toReturn['data'][] = array($title, $date);
        }
        return $toReturn;
    }

    public static function requests($requests, $archived = false, $source = 'requests') {
        $toReturn = array('data' => array());
        foreach ($requests as $request) {
            $request_id = ConstantContent::getTextNode($request, 'request_id');
            $title = ConstantContent::getTextNode($request, 'title');
            $expires_on = ConstantContent::getTextNode($request, 'deadline');
            $number_of_items = ConstantContent::getTextNode($request, 'number_of_items');
            $status = ConstantContent::getTextNode($request, 'status');
            $authors = ConstantContent::getTextNode($request, 'authors');
            $raw_type = ConstantContent::getTextNode($request, 'raw_type');
            if (is_numeric($raw_type)) {
                $authors = 'Private Request: ' . $authors;
            }
            $approved = ConstantContent::getTextNode($request, 'approved');
            $claimed = ConstantContent::findFirstNode($request, 'claimed_by');
            $claimed_by = 'Unclaimed';
            if (!empty($claimed)) {
                $claimed_by = ConstantContent::getTextNode($claimed, 'penname');
                $claimed_by_id = ConstantContent::getTextNode($claimed, 'author_id');
                $claimed_by_part = ConstantContent::getTextNode($claimed, 'part');
            }
            $documentList = ConstantContent::findFirstNode($request, 'documents');
            $docCount = $documentList->getAttribute('count');
            $documents = $request->getElementsByTagName('document');

            $column_0 = '<input type="checkbox" name="requestMassAction[]" class="massRequestAction" value="' . $request_id . '" id="req_' . $request_id . '">';

            $column_1 = $title;
            $column_1 .= '<br style="clear: both;"><div class="left" style="clear: both;">';
            $column_1 .= '<a class="fancybox-request" onclick="requestFancybox(this)" link="' . admin_url('admin-ajax.php?action=constant-content-requestedit') . '&id=' . $request_id . '">Edit</a>';
            if ($status == 'Open') {
                $column_1 .= ' - <a class="request_link" message="Close Request" onclick="requestAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-ajax-data') . '&massRequestAction[close][' . $request_id . ']=' . $request_id . '">Close</a>';
            } else {
                $column_1 .= ' - <a class="request_link" message="Open Request" onclick="requestAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-ajax-data') . '&massRequestAction[reopen][' . $request_id . ']=' . $request_id . '">Reopen</a>';
            }

            if ($archived) {
                $column_1 .= ' - <a class="request_link" message="Unarchive Request" onclick="requestAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-ajax-data') . '&massRequestAction[unarchive][' . $request_id . ']=' . $request_id . '">Unarchive</a>';
            } else {
                $column_1 .= ' - <a class="request_link" message="Archive Request" onclick="requestAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-ajax-data') . '&massRequestAction[archive][' . $request_id . ']=' . $request_id . '">Archive</a>';
            }
            $column_1 .= '</div>';

            $column_2 = $expires_on;
            $column_3 = $authors;
            $column_4 = $approved . '<br/>' . $status;
            $column_5 = $claimed_by;

            $hasDocs = false;
            $column_6 = $docCount . ' of ' . $number_of_items . ' Documents.';
            $column_6 .= '<div class="hidden request_details" id="req_docs_' . $request_id . '">';
            foreach ($documents as $document) {
                $hasDocs = true;
                $document_id = ConstantContent::getTextNode($document, 'id');
                $docTitle = ConstantContent::getTextNode($document, 'title');
                $revisable = ConstantContent::getTextNode($document, 'revisable');
                $use = ConstantContent::getTextNode($document, 'usage_price');
                $unique = ConstantContent::getTextNode($document, 'unique_price');
                $fullrights = ConstantContent::getTextNode($document, 'fullrights_price');
                $usagelicense = ConstantContent::getTextNode($document, 'usagelicense');
                $license = ConstantContent::findFirstNode($document, 'license');

                $column_6 .= '<div class="doctitle">' . $docTitle . '</div>';
                if ($revisable == 'TRUE') {
                    $column_6 .= '<div class="right">';
                    $column_6 .= '<a class = "fancybox-new clickable link" onclick="fancyboxAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-revision-edit&document=') . $document_id . '">Revision</a>';
                    $column_6 .= '</div>';
                    $column_6 .= '<div class="right">|</div>';
                }
                $column_6 .= '<div class="right"><a class="fancybox-preview clickable link"  onclick="fancyboxPreview(this)" link="' . admin_url('admin-ajax.php?action=constant-content-docdetails') . '&id=' . $document_id . '">View</a></div>';
                $column_6 .= '<div class="right">|</div>';
                if (empty($license)) {
                    $column_6 .= '<div class="right">';
                    $column_6 .= '<form method="post" class="requestDocForm">';
                    $column_6 .= '<input type="hidden" name="document_id" value="' . $document_id . '">';
                    if ($usagelicense == 0 && !empty($fullrights)) {
                        $column_6 .= '<input type="hidden" name="type" value="fullrights">';
                        $column_6 .= '<div class="link clickable" onclick="jQuery(this).parent().submit();">';
                        $column_6 .= 'Purchase $' . number_format($fullrights, 2);
                        $column_6 .= '</div>';
                    } elseif ($usagelicense == 0 && !empty($unique)) {
                        $column_6 .= '<input type="hidden" name="type" value="unique">';
                        $column_6 .= '<div class="link clickable" onclick="jQuery(this).parent().submit();">';
                        $column_6 .= 'Purchase $' . number_format($unique, 2);
                        $column_6 .= '</div>';
                    } else {
                        $column_6 .= '<input type="hidden" name="type" value="use">';
                        $column_6 .= '<div class="link clickable" onclick="jQuery(this).parent().submit();">';
                        $column_6 .= 'Purchase $' . number_format($use, 2);
                        $column_6 .= '</div>';
                    }
                    $column_6 .= '</form>';
                    $column_6 .= '</div>';
                }
            }

            $column_6 .= '</div>';
            if ($hasDocs) {
                $column_6 .= '<div class="link clickable request_docs">View Documents</div>';
            }

            $toReturn['data'][] = array(
                $column_0,
                $column_1,
                $column_2,
                $column_3,
                $column_4,
                $column_5,
                $column_6
            );
        }
        return $toReturn;
    }

    public static function documents($documents) {
        $toReturn = array('data' => array());
        $publicationDates = ConstantContent::get('publishedDocs');

        foreach ($documents as $document) {
            $id = ConstantContent::getTextNode($document, 'id');
            $title = ConstantContent::getTextNode($document, 'title');
            $summary = ConstantContent::getTextNode($document, 'summary');
            $length = ConstantContent::getTextNode($document, 'length');
            $category = ConstantContent::getTextNode($document, 'category');
            $penname = ConstantContent::getTextNode($document, 'author');
            $use = ConstantContent::getTextNode($document, 'usage_price');
            $unique = ConstantContent::getTextNode($document, 'unique_price');
            $fullrights = ConstantContent::getTextNode($document, 'fullrights_price');
            $usagelicense = ConstantContent::getTextNode($document, 'usagelicense');
            $order_number = ConstantContent::getTextNode($document, 'order_number');
            $revisable = ConstantContent::getTextNode($document, 'revisable');
            $license = ConstantContent::findFirstNode($document, 'license');
            if (!empty($license)) {
                $licenseType = $license->getAttribute('type');
            } else {
                $licenseType = false;
            }
            $files = ConstantContent::findFirstNode($document, 'files');
            $fileCount = $files->getAttribute('count');
            $easyPublish = $files->getAttribute('easyPublish');
            $easyPublishID = $files->getAttribute('easyPublishID');
            $easyPublishSource = $files->getAttribute('easyPublishSource');
            $file = ConstantContent::findFirstNode($files, 'file');
            if (!empty($file)) {
                $fileid = $file->getAttribute('id');
            } else {
                $fileid = 'false';
            }

            if ($easyPublish == 'TRUE') {
                $type = 'checkbox';
            } else {
                $type = 'hidden';
            }
            if (!empty($fileCount)) {
                $class = 'document_id clickable link';
            }

            $column_0 = '<input name="easyPublish[]" class="massAction" order="' . $order_number . '" title="' . $title . '" value="' . $easyPublishID . '" id="' . $easyPublishID . '" source="' . $easyPublishSource . '" type="' . $type . '">';
            $column_1 = '<span class="fancybox-preview clickable link"  onclick="fancyboxPreview(this)" link="' . admin_url('admin-ajax.php?action=constant-content-docdetails') . '&id=' . $id . '&order=' . $order_number . '">' . $title . '</span>';

            $column_1 .= '</span>';

            $filesList = $document->getElementsByTagName('file');
            $column_1 .= '<div class="order_details hidden">';
            $column_1 .= '<table width="100%">';
            $column_1 .= '<thead>';
            $column_1 .= '<tr>';
            $column_1 .= '<th style="width: 15px;border-bottom: 0px;">&nbsp;</th>';
            $column_1 .= '<th align="right">Filename</th>';
            $column_1 .= '<th align="right" style="width: 150px;">Action</th>';
            $column_1 .= '</tr>';
            $column_1 .= '</thead>';
            $column_1 .= '<tbody>';
            foreach ($filesList as $file) {
                $fileid = $file->getAttribute('id');
                $filename = $file->getAttribute('name');
                $filetype = $file->getAttribute('type');
                $column_1 .= '<tr>';
                $column_1 .= '<td valign="top">&nbsp;</td>';
                $column_1 .= '<td valign="top">' . $filename . '</td>';
                $column_1 .= '<td valign="top">';
                $column_1 .= '<form method="post" id="create_' . $fileid . '">';
                $column_1 .= '<input name="fileID" value="' . $fileid . '" type="hidden">';
                $column_1 .= '<input name="publishAction" value="none" id="action_' . $fileid . '" type="hidden">';
                $column_1 .= '<input name="title" value="' . $title . '" type="hidden">';
                $column_1 .= '<input name="documentID" value="' . $id . '" type="hidden">';
                $column_1 .= '<input name="license" value="' . $licenseType . '" type="hidden">';
                $column_1 .= '<div class="clickable link" onclick=\'submitAction(' . $fileid . ', "create_post")\'>Create Post</div>';
                $column_1 .= '<div class="clickable link" onclick=\'submitAction(' . $fileid . ', "create_draft_post")\'>Save Draft Post</div>';
                $column_1 .= '<br style="clear: both;display: inherit;">';
                $column_1 .= '<div class="clickable link" onclick=\'submitAction(' . $fileid . ', "create_page")\'>Create Page</div>';
                $column_1 .= '<div class="clickable link" onclick=\'submitAction(' . $fileid . ', "create_draft_page")\'>Save Draft Page</div>';
                $column_1 .= '</form>';
                $column_1 .= '</td>';
                $column_1 .= '</tr>';
            }
            $column_1 .= '</tbody>';
            $column_1 .= '</table>';
            $column_1 .= '</div>';

            $column_2 = $penname;
            $column_3 = $order_number;
            $column_4 = '<p>&nbsp</p>';
            $column_5 = '<p>Unpublished</p>';
            if (!empty($publicationDates[$easyPublishID])) {
                $publicationDate = $publicationDates[$easyPublishID];
                $theDate = new DateTime();
                $theDate->setTimestamp($publicationDate['lastPublished']);
                $column_4 = '<p>' . date_format($theDate, 'Y/m/d') . '</p>';
                $column_5 = '<p>' . $publicationDate['type'] . '</p>';
            }

            $column_6 = '';
            if ($easyPublish == 'TRUE') {
                $column_6 .= '<span class="' . $class . '">Publish</span>';
            }
            if ($revisable == 'TRUE') {
                $column_6 .= '<div><form method="post" id="revise_' . $id . '">';
                $column_6 .= '<input name="document" value="' . $id . '" type="hidden">';
                $column_6 .= '<input name="revision" value="true" type="hidden">';
                $column_6 .= '<span class = "fancybox-new clickable link" onclick="fancyboxAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-revision-edit&document=') . $id . '">Revision</span>';
                $column_6 .= '</form></div>';
            }
            if ($easyPublish == 'TRUE') {
                $column_6 .= '<div><form method="post" id="download_' . $easyPublishID . '" action=' . admin_url('admin-ajax.php?action=constant-content-ajax-data') . '>';
                $column_6 .= '<input name="download" value="download ' . $easyPublishSource . '" type="hidden">';
                $column_6 .= '<input name="download_id" value="' . $easyPublishID . '" type="hidden">';
                $column_6 .= '<span class="clickable link" onclick="downloadAction(' . $easyPublishID . ')">Download</span>';
                $column_6 .= '</form></div>';
            }

            $toReturn['data'][] = array(
                $column_0,
                $column_1,
                $column_2,
                $column_3,
                $column_4,
                $column_5,
                $column_6
            );
        }
        return $toReturn;
    }

    public static function orders($orders, $balance) {
        $toReturn = array('data' => array());
        foreach ($orders as $order) {
            $order_id = ConstantContent::getTextNode($order, 'order_id');
            $order_date = ConstantContent::getTextNode($order, 'order_date');
            $purchase_date = ConstantContent::getTextNode($order, 'purchase_date');
            $orderprice = ConstantContent::getTextNode($order, 'price');

            $column_0 = $order_id;
            $column_1 = $order_date;

            if (empty($purchase_date)) {
                $column_2 = 'Unpaid';
                if ($balance < $orderprice) {
                    $prices = ConstantContent::get('price_list');
                    $minPrice = ConstantContent::getMinPrice($prices, $orderprice);
                    $column_2 .= "<input type='hidden' name='purchase_credits' value='true'>";
                    $column_2 .= "<select name='price' id='safecart_price_2'>";
                    foreach ($prices as $price) {
                        $column_2 .= "<option ";
                        if ($minPrice === $price) {
                            $column_2 .= " selected='selected' ";
                        }
                        $column_2 .= " value='" . $price . "'>$" . number_format($price, 2) . "</option>";
                    }
                    $column_2 .= "</select>";
                    $column_2 .= "<input name='purchase' class='purchase' type='submit' onclick=\"purchase_safecart('#safecart_price_2');return false;\" value='Purchase Credits'>";
                }
            } else {
                $column_2 = 'Paid on ' . $purchase_date;
            }

            $column_2 .= '<div id="details_' . $order_id . '" class="hidden order_details">';
            $column_2 .= '<table class="subOrderTable" style="width: 100%" cellpadding="0" cellspacing="0">';
            $column_2 .= '<thead>';
            $column_2 .= '<tr>';
            $column_2 .= '<th align="left">Title</th>';
            $column_2 .= '<th align="left" style="width: 75px">License</th>';
            $column_2 .= '<th align="left" style="width: 150px">Price</th>';
            $column_2 .= '</tr>';
            $column_2 .= '</thead>';
            $column_2 .= '<tbody>';
            $documents = $order->getElementsByTagName('document');
            foreach ($documents as $document) {
                $document_id = ConstantContent::getTextNode($document, 'id');
                $title = ConstantContent::getTextNode($document, 'title');
                $license = ConstantContent::findFirstNode($document, 'license');
                $license_type = $license->getAttribute('type');
                $license_price = $license->getAttribute('price');
                $column_2 .= '<tr>';
                $column_2 .= '<td>';
                $column_2 .= '<span class="fancybox-preview clickable link" onclick="fancyboxPreview(this)" link="' . admin_url('admin-ajax.php?action=constant-content-docdetails') . '&id=' . $document_id . '&order=' . $order_id . '">' . $title . '</span>';
                if (empty($purchase_date)) {
                    $column_2 .= '<span class="right">';
                    $column_2 .= '<a class="request_link clickable link" message="Remove Document" onclick="orderAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-ajax-data')
                            . '&remove=yes&document=' . $document_id . '&order_id=' . $order_id . '">Remove Document</a>';
                    $column_2 .= '</span>';
                }
                $column_2 .= '</td>';
                $column_2 .= '<td>' . $license_type . '</td>';
                $column_2 .= '<td>$' . number_format($license_price, 2) . '</td>';
                $column_2 .= '</tr>';
            }
            $column_2 .= '</tbody>';
            $column_2 .= '</table>';
            $column_2 .= '</div>';

            $column_3 = '$' . number_format($orderprice, 2);

            $column_4 = '<span class="order_id clickable link" id="order_link_' . $order_id . '">View</span>';
            if (empty($purchase_date)) {
                if ($balance >= $orderprice) {
                    $column_4 .= '<span class="clickable link" message="Buy Order" onclick="orderAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-ajax-data')
                            . '&purchase=yes&order_id=' . $order_id . '">Buy Order</span>';
                }
                $column_4 .= '<span class="clickable link" message="Delete Order" onclick="orderAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-ajax-data')
                        . '&delete=yes&order_id=' . $order_id . '">Delete Order</span>';
            }
            $toReturn['data'][] = array(
                $column_0,
                $column_1,
                $column_2,
                $column_3,
                $column_4
            );
        }
        return $toReturn;
    }

    public static function catalog($documents) {
        $toReturn = array('data' => array());
        foreach ($documents as $document) {
            $id = ConstantContent::getTextNode($document, 'id');
            $title = ConstantContent::getTextNode($document, 'title');
            $summary = ConstantContent::getTextNode($document, 'summary');
            $length = ConstantContent::getTextNode($document, 'length');
            $category = ConstantContent::getTextNode($document, 'category');
            $category_id = ConstantContent::getTextNode($document, 'category_id');
            $penname = ConstantContent::getTextNode($document, 'penname');
            $use = ConstantContent::getTextNode($document, 'usage_price');
            $unique = ConstantContent::getTextNode($document, 'unique_price');
            $fullrights = ConstantContent::getTextNode($document, 'fullrights_price');
            $usagelicense = ConstantContent::getTextNode($document, 'usagelicense');

            $column_0 = '<div class="contentbox">';
            $column_0 .= '<img src="' . CONSTANTCONTENT_ASSET_DIR . 'images/contentboxtop.jpg" alt="" class="contentboximage">';
            $column_0 .= '<div class="contentbox_inner">';
            $column_0 .= '<div style="width: 15%;float: right;padding: 10px;text-align: right">';
            $column_0 .= '<form method="post" class="buydocform">';
            $column_0 .= '<input type="hidden" name="document_id" value="' . $id . '">';
            $column_0 .= '<select name="type" style="min-width: 160px;">';
            if ($usagelicense == 0) {
                if (!empty($fullrights)) {
                    $column_0 .= '<option value="fullrights">Full Rights $' . number_format($fullrights, 2) . '</option>';
                }
                if (!empty($unique)) {
                    $column_0 .= '<option value="unique">Unique $' . number_format($unique, 2) . '</option>';
                }
            }
            if (!empty($use)) {
                $column_0 .= '<option value="use">Usage $' . number_format($use, 2) . '</option>';
            }
            $column_0 .= '</select>';
            $column_0 .= '<div class="link clickable" onclick="jQuery(this).parent().submit();">Add To Order</div>';
            $column_0 .= '</form>';
            $column_0 .= '</div>';
            $column_0 .= '<div style="width: 70%;"><h2 class="pageTitle">' . $title . '</h2></div>';
            $column_0 .= '<div style="width: 70%;padding-top: 5px;">';
            $column_0 .= '<div>' . $summary . '</div>';
            $column_0 .= '<div>';
            $column_0 .= '<span style="min-width: 100px;display: inline-block;">';
            $column_0 .= '<strong>Words</strong>:' . $length . '</span>';
            $column_0 .= '<span style="min-width: 200px;display: inline-block;">';
            $column_0 .= '<strong>Category</strong>:' . $category . '</span>';
            $column_0 .= '<span style="min-width: 100px;display: inline-block;">';
            $column_0 .= '<strong>By</strong>:' . $penname . '</span>';
            $column_0 .= '<span class="right">';
            $column_0 .= '<a class="fancybox-preview clickable"  onclick="fancyboxPreview(this)" link="' . admin_url('admin-ajax.php?action=constant-content-docdetails') . '&id=' . $id . '">Document Sample</a>';
            $column_0 .= '</span>';
            $column_0 .= '</div>';
            $column_0 .= '</div>';
            $column_0 .= '</div>';
            $column_0 .= '<img src="' . CONSTANTCONTENT_ASSET_DIR . 'images/contentboxbottom.jpg" alt="" class="contentboximage">';
            $column_0 .= '</div>';

            $column_1 = $category_id;
            $toReturn['data'][] = array(
                $column_0,
                $column_1
            );
        }
        return $toReturn;
    }

    public static function revisions($revisions) {
        $toReturn = array('data' => array());
        foreach ($revisions as $revision) {
            $state = str_replace('_', ' ', $revision->getAttribute('state'));
            $revisionItems = $revision->getElementsByTagName('revision');
            foreach ($revisionItems as $revisionItem) {
                $revID = ConstantContent::getTextNode($revisionItem, 'id');
                $title = ConstantContent::getTextNode($revisionItem, 'title');
                $document = ConstantContent::getTextNode($revisionItem, 'document');
                $author = ConstantContent::getTextNode($revisionItem, 'author');
                $price = ConstantContent::getTextNode($revisionItem, 'price');
                $start_date = ConstantContent::getTextNode($revisionItem, 'start_date');
                $updated_date = ConstantContent::getTextNode($revisionItem, 'updated_date');
                $updateSort = $updated_date;
                if ($updated_date == '0000-00-00 00:00:00') {
                    $updated_date = 'Never';
                    $updateSort = $start_date;
                }
                $column_0 = '<a class = "fancybox-new clickable link" onclick="fancyboxAction(this)" link="' . admin_url('admin-ajax.php?action=constant-content-revision-edit&id=') . $revID . '">' . $title . '</a>';
                $column_0 .= '<br/>By: ' . $author;
                $column_1 = $price;
                $column_2 = '<div sort="' . $updateSort . '">';
                $column_2 .= $start_date . ' /<br/>' . $updated_date;
                $column_2 .= '</div>';
                $column_3 = $state;
                $toReturn['data'][] = array(
                    $column_0,
                    $column_1,
                    $column_2,
                    $column_3
                );
            }
        }
        return $toReturn;
    }

}
