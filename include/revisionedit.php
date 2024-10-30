<?php
$success = false;
$message = '';
if (!empty($_REQUEST['id'])) {
    $id = (int) $_REQUEST['id'];
    $result = ConstantContent::accessAPI('revision', 'get', array(), $id . '/');
} elseif (!empty($_REQUEST['document'])) {
    $postArray['document'] = (int) $_REQUEST['document'];
    $result = ConstantContent::accessAPI('revision', 'post', $postArray);
} else {
    $message = 'Unable to find revision.';
}
if (!empty($result)) {
    $doc = new DOMDocument();
    $doc->loadXML($result);
    $succesCheck = ConstantContent::checkElementValue($doc, 'success', 'true');
    if ($succesCheck === true) {

        $revisionItem = ConstantContent::findFirstNode($doc, 'revision');

        $revision_id = ConstantContent::getTextNode($revisionItem, 'id');
        $contents = ConstantContent::getTextNode($revisionItem, 'contents');
        $title = ConstantContent::getTextNode($revisionItem, 'title');
        $document = ConstantContent::getTextNode($revisionItem, 'document');
        $instructions = ConstantContent::getTextNode($revisionItem, 'instructions');
        $author = ConstantContent::getTextNode($revisionItem, 'author');
        $price = ConstantContent::getTextNode($revisionItem, 'price');
        $count = ConstantContent::getTextNode($revisionItem, 'count');
        $state = ConstantContent::getTextNode($revisionItem, 'state');
        $state_code = ConstantContent::getTextNode($revisionItem, 'state_code');
        $buyable = ConstantContent::getTextNode($revisionItem, 'buyable');
        $order = ConstantContent::getTextNode($revisionItem, 'order');
        $downloadable = ConstantContent::getTextNode($revisionItem, 'downloadable');
        $download_id = ConstantContent::getTextNode($revisionItem, 'download_id');
        $dispalyPrice = $price;
        if (is_numeric($price)) {
            $dispalyPrice = '$' . number_format($price, 2);
        }
        $start_date = ConstantContent::getTextNode($revisionItem, 'start_date');
        $updated_date = ConstantContent::getTextNode($revisionItem, 'updated_date');
        if ($updated_date == '0000-00-00 00:00:00') {
            $updated_date = 'Never';
        }

        $contentsElement = ConstantContent::findFirstNode($revisionItem, 'contents');
        $height = $contentsElement->getAttribute('height');
        $width = $contentsElement->getAttribute('width');
        ob_start();
        ?>
        <div class="">
            <h2>Revision: Edit</h2>
            <form method="post" class='revisionEditForm'>
                <div class="error error_message fontRed<?php if (empty($message)) { ?> hidden <?php } ?>">
                    <?php
                    if (!empty($message)) {
                        echo $message;
                    }
                    ?>
                </div>
                <input type="hidden" name="revision" value="<?= $revision_id ?>">
                <table width="100%">
                    <tr>
                    <th align="left" valign="top" style="width: 200px;" align="left">Title:</th>
                    <td>
        <?= $title ?> By: <?= $author ?>
                    </td>
                    </tr>
                    <tr>
                    <th style="width: 200px;" align="left">State:</th>
                    <td><?= $state ?></td>
                    </tr>
                    <tr>
                    <th style="width: 200px;" align="left">Count:</th>
                    <td><?= $count ?></td>
                    </tr>
                    <tr>
                    <th style="width: 200px;" align="left">Price:</th>
                    <td>
                        <?php if ($state_code === '100' || $state_code == '300') {
                            ?><input type="text" name="price" value="<?= $price ?>"><?php
                        } else {
                            echo $dispalyPrice;
                        }
                        ?>
                    </td>
                    </tr>
                    <tr>
                    <th style="width: 200px;" align="left">Date Started:</th>
                    <td><?= $start_date ?></td>
                    </tr>
                    <tr>
                    <th style="width: 200px;" align="left">Date Updated:</th>
                    <td><?= $updated_date ?></td>
                    </tr>
                    <tr>
                    <th valign="top" style="width: 200px;" align="left">Instructions:</th>
                    <td>
                        <?php if ($state_code == '100' || $state_code == '300') {
                            ?><textarea name="instructions" rows="4" style="width: 100%"><?= $instructions ?></textarea><?php
                        } else {
                            echo $instructions;
                        }
                        ?>
                    </td>
                    </tr>
                    <tr>
                    <th valign="top" style="width: 200px;" align="left" colspan='2'>Content:</th>
                    </tr>
                    <tr>
                    <th colspan='2'>
                        <div style="height: 250px;overflow-y: scroll">
                            <div style="position: relative;width: 775px;height: <?= $height ?>px;"><?= $contents ?></div>
                        </div>
                        </td>
                        </tr>
                    <tr>
                    <td align="right" valign="top" colspan="2">
                        <?php if ($state_code == '100') { ?>
                            <input type="submit" name="action" value="Submit Revision Request">
        <?php } elseif ($state_code == '300') { ?>
                            <input type="submit" name="action" value="Counter Revision Price">
                            <input type="submit" name="action" value="Accept Revision Price">
                            <input type="submit" name="action" value="Reject Revision Price">
        <?php } elseif ($state_code == '500') { ?>
                            <input type="submit" name="action" value="Request New Revision">
                            <input type="submit" name="action" value="Accept Revision">
                            <input type="submit" name="action" value="Reject Revision">
        <?php } elseif ($state_code == '520') { ?>
                            <?php if ($buyable == 'TRUE') { ?>
                                <input type="hidden" name="order" value="<?= $order ?>">
                                <input type="submit" name="action" value="View Order">
                            <?php } elseif ($downloadable == 'TRUE') { ?>
                                <a href='<?= (admin_url('admin-ajax.php?action=constant-content-ajax-data')) ?>&download=download revision&download_id=<?= $download_id ?>'>Download Revision</a>
                            <?php } else { ?>
                                <input type="hidden" name="document" value="<?= $document ?>">
                                <input type="submit" name="action" value="Buy Revision">
                            <?php } ?>
                        <?php } elseif ($state_code == '310' || $state_code == '510' || $state_code == '600') { ?>
                            <input type="submit" name="action" value="Request New Revision">
        <?php } ?>
                    </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
        $message = ob_get_contents();
        ob_clean();
        $success = true;
    }
}
$toReturn = array(
    'success' => $success,
    'data' => $message
);
wp_send_json($toReturn);
