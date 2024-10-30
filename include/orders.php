<?php
$message = '';
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');

$credit_balance = ConstantContent::get('credit_balance');
$customerXML = ConstantContent::accessAPI('customer', 'get', null, '/');
$customerDoc = new DOMDocument();
$customerDoc->loadXML($customerXML);
$customer = ConstantContent::findFirstNode($customerDoc, 'customer');
$balance = ConstantContent::getTextNode($customer, 'credit_balance');
if ($balance != $credit_balance) {
    ConstantContent::cacheAPIClean('order');
}
ConstantContent::save('credit_balance', $balance);
?>
<div class="titlebar">
    <div class="cc-content">
        <h2>My Orders</h2>
    </div>
</div>
<div class="cc-content">
    <div class="error error_message fontRed<?php if (empty($message)) { ?> hidden <?php } ?>">
        <?php if (!empty($message)) {
            echo $message;
        } ?>
    </div>
    <div class="right" style="width:100%;">
        <table id="ordersTable" width="100%">
            <thead>
                <tr>
                <th style='width: 60px;'>Order #</th>
                <th align='left'>Date Placed</th>
                <th align='left'>Order Status</th>
                <th align='left' style='width: 150px;'>Price</th>
                <th align='left'>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td colspan="5">
                    <center><img src="<?= CONSTANTCONTENT_ASSET_DIR ?>images/spinning_wheel.gif"></center>
                    <center>Loading Orders</center>
                </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class='topItem hidden left' style='width: 150px;'>
    Account Balance: $<span id='accountBalance'><?= number_format($balance, 2) ?></span>
</div>
<br class="clearleft">
<?php
require_once CONSTANTCONTENT_INCLUDE_DIR . 'footer.php';
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var ordersTableLoaded = false;
        jQuery("#ordersTable").dataTable({
            "order": [[0, "desc"]],
            oLanguage: {
                sEmptyTable: "You haven’t placed any orders yet."
            },
            "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=orders'); ?>",
            'dom': '<"top"f>rt<"bottom"i<"right"l><"pagination center"p>><"clear">',
            fnPreDrawCallback: function ( ) {
                if (!ordersTableLoaded) {
                    var dropdown = jQuery('div.topItem').clone().removeClass('hidden');
                    jQuery('div.top').append(dropdown);
                    ordersTableLoaded = true;
                }
            },
            fnDrawCallback: function (oSettings) {
                jQuery('.order_id').unbind("click");
                jQuery('.order_id').click(function () {
                    var row = jQuery(this).parent().parent();

                    if (jQuery('#ordersTable').dataTable().fnIsOpen(row)) {
                        jQuery('#ordersTable').dataTable().fnClose(row);
                        return false;
                    }
                    var orderDetailsHtml = jQuery(row).find('.order_details').first().clone().removeClass('hidden');

                    jQuery("#ordersTable").dataTable().fnOpen(row, orderDetailsHtml, "info_row");
                });
<?php if (!empty($_REQUEST['order_id'])) { ?>
                    jQuery("#order_link_<?= (int) $_REQUEST['order_id'] ?>").click();
<?php } ?>
            }
        });
    });
</script>
