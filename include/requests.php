<?php
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');


$customerXML = ConstantContent::accessAPI('customer', 'get', null, '/');
$customerDoc = new DOMDocument();
$customerDoc->loadXML($customerXML);
$customer = ConstantContent::findFirstNode($customerDoc, 'customer');
$openRequests = ConstantContent::getTextNode($customer, 'requests_open');
$doc_for_sale = ConstantContent::getTextNode($customer, 'documents_for_purchase');
$doc_for_download = ConstantContent::getTextNode($customer, 'documents_for_download');
$credit_avail = ConstantContent::getTextNode($customer, 'credit_balance');
$credit_needed = ConstantContent::getTextNode($customer, 'credit_for_requests');
$credit_to_buy = 100;
if ($credit_avail < $credit_needed) {
    $credit_to_buy = ConstantContent::getTextNode($customer, 'credit_to_buy');
}

$publicationDates = ConstantContent::get('publishedDocs');
?>
<div class="titlebar">
    <div class="cc-content">
        <?php if ($archived) { ?>
            <h2>Archived Requests</h2>
            <div><a href="<?= (admin_url('admin.php?page=constant-content-requests')) ?>" style="text-decoration: underline;color: #7de9ba">Click here</a> to access your requests.</div>
        <?php } else { ?>
            <h2>My Requests</h2>
            <p>Issue a request to order custom written content.  All of your requests are displayed below.</p>
            <p><a href="<?= (admin_url('admin.php?page=constant-content-archived-requests')) ?>" style="text-decoration: underline;color: #7de9ba">Click here</a> to access your archived requests.</p>
        <?php } ?>
    </div>
</div>
<div class="cc-content">
    <table width='100%' class="stripe" id="requestTable">
        <thead>
            <tr>
                <th style="width: 3%; ">&nbsp;</th>
                <th style="width: ">Title</th>
                <th style="width: 15%; ">Expiry</th>
                <th style="width: 10%;">Request Type</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 10%;">Claimed</th>
                <th style="width: 15%;">Documents</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5">Loading Data</td>
            </tr>
        </tbody>
    </table>
</div>
<br class="clearleft">
<div class="hidden new_request left" style="margin-left: 15px;">
    <a class="fancybox-request clickable"  onclick="requestFancybox(this)" id="inline" link="<?= (admin_url('admin-ajax.php?action=constant-content-requestnew')) ?>">New Request</a>
</div>
<div class="hidden">
    <form method="post" name="massRequestActionForm" id="massRequestActionForm" action='<?php admin_url('admin.php?page=constant-content-requests') ?>'>
        <input type="hidden" name="massRequestAction" value="true">
    </form>
</div>
<div class="massRequestActionDropdown hidden left">
    Perform Mass Action:
    <select name="mass_request_action" onchange="massRequestAction(this);">
        <option value="">Choose Action</option>
        <option value="reopen">Reopen</option>
        <option value="close">Close</option>
        <?php if ($archived) { ?>
            <option value="unarchive">Unarchive</option>
        <?php } else { ?>
            <option value="archive">Archive</option>
        <?php } ?>
    </select>
</div>
<?php if ($credit_avail < $credit_needed) { ?>
    <div class="requestsBottom visable hidden">
        <div>
            Please buy credits to cover the cost of your requests
                <div class="">
                    <?php
                    $prices = ConstantContent::get('price_list');
                    ?>

                    <input type='hidden' name='purchase_credits' value='true'>
                    <select name='price' id="safecart_price_4">
                        <?php
                        foreach ($prices as $price) {
                            $option = "<option ";
                            if ($credit_to_buy == $price) {
                                $option .= " selected='selected' ";
                            }
                            $option .= " value='" . $price . "'>$" . number_format($price, 2) . "</option>";
                            echo $option;
                        }
                        ?>
                    </select>
                </div>
                <div class="">
                    <input name='purchase' class='purchase' type='submit' onclick="purchase_safecart('#safecart_price_4');return false;" value='Purchase Credits'>
                </div>
        </div>
    </div>
<?php } else { ?>
    <div class="requestsBottom hidden"></div>
<?php } ?>
<script type="text/javascript">
    var fancybox;
    jQuery(document).ready(function () {
        jQuery(function () {
            var requestTableLoaded = false;
            jQuery("#requestTable").dataTable({
                "order": [[2, "desc"]],
                oLanguage: {
                    sEmptyTable: "You haven’t issues any requests yet."
                },
                "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=requests&source=requests'); ?><?php if ($archived) { ?>&archived=true<?php } ?>",
                                'dom': '<"requests"<"top"f>rt<"bottom"i<"right"l><"pagination center"p>>><"clear">',
                                fnPreDrawCallback: function ( ) {
                                    if (!requestTableLoaded) {
                                        var massAction = jQuery('div.massRequestActionDropdown').clone().removeClass('hidden');
                                        jQuery('div.top').append(massAction);
                                        var new_request = jQuery('div.new_request').clone().removeClass('hidden');
                                        jQuery('div.top').append(new_request);
                                        var requestsBottom = jQuery('div.requestsBottom').clone().removeClass('hidden');
                                        jQuery('div.requests .bottom').prepend(requestsBottom);
                                        requestTableLoaded = true;
                                    }
                                },
                                "fnDrawCallback": function (oSettings, json) {
                                    jQuery('.request_docs').click(function () {
                                        var row = jQuery(this).parent().parent();
                                        if (jQuery('#requestTable').dataTable().fnIsOpen(row)) {
                                            jQuery('#requestTable').dataTable().fnClose(row);
                                            return false;
                                        }
                                        var requestDocuments = jQuery(row).find('.request_details').first().clone().removeClass('hidden');

                                        jQuery("#requestTable").dataTable().fnOpen(row, requestDocuments, "info_row");
                                    });
                                    hideLoading("#requestTable");
                                }
                            });
                        });
                    });
                    jQuery('.request_id').click(function () {
                        var row = jQuery(this).parent().parent();

                        if (jQuery('#ordersTable').dataTable().fnIsOpen(row)) {
                            jQuery('#ordersTable').dataTable().fnClose(row);
                            return false;
                        }

                        var request_id = jQuery(row).attr('request');
                        var orderDetailsHtml = jQuery('#details_' + request_id).clone().attr('id', 'info_' + order_id).removeClass('hidden');

                        jQuery("#requestTable").dataTable().fnOpen(row, orderDetailsHtml, "info_row");
                    });
</script>
<?php
require_once CONSTANTCONTENT_INCLUDE_DIR . 'footer.php';
