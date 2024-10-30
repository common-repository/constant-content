<?php

$id = $_REQUEST['id'];
$order = $_REQUEST['order'];

$result = ConstantContent::accessAPI('document', 'get', array(), 'preview/' . $id . '/');

$doc = new DOMDocument();
$doc->loadXML($result);
$documents = ConstantContent::findFirstNode($doc, 'documents');
$count = $documents->getAttribute('count');

if ($count > 0) {
    if (!empty($order)) {
        $documents = $doc->getElementsByTagName('document');
        foreach ($documents as $document) {
            $order_number = ConstantContent::getTextNode($document, 'order_number');
            if ($order_number == $_REQUEST['order']) {
                break;
            }
        }
    } else {
        $document = ConstantContent::findFirstNode($doc, 'document');
    }
    $preview = ConstantContent::findFirstNode($document, 'preview');
    $locked = $preview->getAttribute('locked');
    $height = $preview->getAttribute('height');
    $width = $preview->getAttribute('width');
    ?>
    <div style="width: 690px;position: relative;">
        <div class="formactions" style="margin: 8px;width:100px;float:right;position:relative;left:500;">
            <?php
            $files = ConstantContent::findFirstNode($doc, 'files');
            $publicationDates = ConstantContent::get('publishedDocs');

            if (!empty($files)) {
                $easyPublish = $files->getAttribute('easyPublish');
                $easyPublishID = $files->getAttribute('easyPublishID');
                $easyPublishSource = $files->getAttribute('easyPublishSource');
                $id = ConstantContent::getTextNode($document, 'id');
                $title = ConstantContent::getTextNode($document, 'title');
                $license = ConstantContent::findFirstNode($document, 'license');
                if (!empty($license)) {
                    $licenseType = $license->getAttribute('type');
                } else {
                    $licenseType = false;
                }

                if ($easyPublish == 'TRUE') {
                    if (!empty($publicationDates[$easyPublishID])) {
                        $publicationDate = $publicationDates[$easyPublishID];
                        $theDate = new DateTime();
                        $theDate->setTimestamp($publicationDate['lastPublished']);
                        ?>
                        <p>
                            Published: <?= date_format($theDate, 'Y/m/d') ?>
                            <br/>
                            <?= $publicationDate['type'] ?>
                        </p>
                    <?php } ?>
                    <form target="_top" method='post' id='create_<?= $easyPublishID ?>' action="<?= admin_url('admin-ajax.php?action=constant-content-ajax-data') ?>">
                        <input name='fileID' value='<?= $easyPublishID ?>' type='hidden'>
                        <input name='publishAction' value='none' id='action_<?= $easyPublishID ?>' type='hidden'>
                        <input name='title' value='<?= $title ?>' type='hidden'>
                        <input name='documentID' value='<?= $id ?>' type='hidden'>
                        <input name='license' value='<?= $licenseType ?>' type='hidden'>
                        <div class="clickable link" onclick='submitAction(<?= $easyPublishID ?>, "create_post")'>Create Post</div>
                        <div class="clickable link" onclick='submitAction(<?= $easyPublishID ?>, "create_draft_post")'>Save Draft Post</div>
                        <div class="clickable link" onclick='submitAction(<?= $easyPublishID ?>, "create_page")'>Create Page</div>
                        <div class="clickable link" onclick='submitAction(<?= $easyPublishID ?>, "create_draft_page")'>Save Draft Page</div>
                    </form>
                    <?php
                }
            }
            ?>
        </div>
        <div style='position: relative;height: <?= $height ?>px;width: <?= $width ?>px;'>
            <?php
            if (!empty($document)) {
                echo ConstantContent::getTextNode($document, 'preview');
            }
            ?>
        </div>
        <?php
        if ($locked == 'TRUE') {
            $prices = ConstantContent::get('price_list');
            ?>
            <div class='left' style='clear: left;position: relative;'>
                    <input type='hidden' name='purchase_credits' value='true'>
                    <select name='price' id="safecart_price_3">
                        <?php foreach ($prices as $price) { ?>
                            <option <?php if ($price == 100) { ?>selected='selected'<?php } ?>value='<?= $price ?>'>$<?= number_format($price, 2) ?></option>
                        <?php } ?>
                    </select>
                    <input name='purchase' class='purchase' type='submit' onclick="purchase_safecart('#safecart_price_3');return false;" value='Purchase Credits'>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
} else {
    ?>
    <div>Document Not Found</div>
    <?php
}

exit;
