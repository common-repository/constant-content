<?php
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');

$categories = ConstantContent::get('category_list');
$prices = ConstantContent::get('price_list');

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
?>

<div class="cc-dash-glance">
    <div class="full_width">
        <div class="one_fifth">
            <div class="tbl-heading">Number of Open<br>Requests</div>
            <div class="tbl-copy"><?= $openRequests ?></div>
        </div>
        <div class="one_fifth">
            <div class="tbl-heading">Content Awaiting<br>Purchase</div>
            <div class="tbl-copy"><?= $doc_for_sale ?></div>
        </div>
        <div class="one_fifth">
            <div class="tbl-heading">Content for<br>Download</div>
            <div class="tbl-copy"><?= $doc_for_download ?></div>
        </div>
        <div class="one_fifth">
            <div class="tbl-heading">Credits<br>Available</div>
            <div class="tbl-copy">$<?= number_format((double) $credit_avail, 2) ?></div>
        </div>
        <div class="one_fifth no-border">
            <div class="tbl-heading">Credits Required<br>for Requests</div>
            <div class="tbl-copy">$<?= number_format((double) $credit_needed, 2) ?></div>
        </div>
        <div class="full_width">
            <div class="credit center">
                <div class="one_half">
                    <select name='price' id="safecart_price_1">
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
                <div class="one_half">
                    <input name='purchase' class="purchase" type='submit' onclick="purchase_safecart('#safecart_price_1');
                            return false;" value='Purchase Credits'>
                </div>
            </div>
        </div><!-- /full-width -->
    </div>
</div>

<div class="cc-content">

    <div class="clearleft left" style="width:100%;" id='notificationsContainer'>
        <table id="notificationTable" width="100%">
            <thead>
                <tr>
                <th>Notification</th>
                <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td colspan="2">Loading Data</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="clearleft left" style="width: 100%;" id='requestsContainer'>
        <table width='100%' class="stripe" id="requestTable">
            <thead>
                <tr>
                <th align='left'>&nbsp</th>
                <th align='left'>Request</th>
                <th align='left' style='width: 125px;'>Expiry</th>
                <th align='left' style='width: 100px;'>Request Type</th>
                <th align='left' style='width: 75px;'>Status</th>
                <th align='left' style='width: 75px;'>Claimed</th>
                <th align='left' style='width: 125px;'>Content</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td colspan="6">Loading Data</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="clearleft left" style="width: 100%;" id='contentContainer'>
        <table width='100%' class="stripe" id="contentTable">
            <thead>
                <tr>
                <th align='left'>&nbsp</th>
                <th align='left'>Title</th>
                <th align='left' style='width: 75px;'>Penname</th>
                <th align='left' style='width: 75px;'>Order #</th>
                <th align='left' style='width: 100px;'>Publication Date</th>
                <th align='left' style='width: 100px;'>Publication Type</th>
                <th align='left' style='width: 75px;'>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td colspan="6">Loading Data</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="clearleft left" style="width: 100%;" id='catalogContainer'>
        <table width='100%' class="stripe" id="catalogTable">
            <thead>
                <tr>
                <th>The Catalog</th>
                <th>The Catalog</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td>
                    <center>
                        <img src="<?= CONSTANTCONTENT_ASSET_DIR ?>images/spinning_wheel.gif">
                    </center>
                    <center>Retrieving Articles</center>
                </td>
                <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="hidden ataglanceTitle">
    <div class="left bold largeFont">At A Glance</div>
    <div class="right hide" hide='ataglance'><?php
        if ($toHide['ataglance'] == true) {
            echo 'open';
        } else {
            echo 'hide';
        }
        ?></div>
</div>
<div class="hidden notificationTitle">
    <div class="left bold largeFont">
        <a href="<?= (admin_url('admin.php?page=constant-content-notifications')) ?>">Notifications</a>
    </div>
    <div class="right hide" hide='notifications'><?php
        if ($toHide['notifications'] == true) {
            echo 'open';
        } else {
            echo 'hide';
        }
        ?></div>
</div>
<div class="hidden requestsTitle">
    <div class="bold largeFont left">
        <a href="<?= (admin_url('admin.php?page=constant-content-requests')) ?>">Requests</a>
    </div>
    <div class="right hide" hide='requests'><?php
        if ($toHide['requests'] == true) {
            echo 'open';
        } else {
            echo 'hide';
        }
        ?></div>
    <div class="left new_request" style='margin-left: 40px;'>
        <a class="fancybox-request  clickable"  onclick="requestFancybox(this)" id="inline" link="<?= (admin_url('admin-ajax.php?action=constant-content-requestnew')) ?>">New Request</a>
    </div>
</div>
<div class="requestsBottom hidden">
<?php if ($credit_avail < $credit_needed) { ?>
        <div>
            Please buy credits to cover the cost of your requests
            <div class="">
                <?php
                $prices = ConstantContent::get('price_list');
                ?>

                <input type='hidden' name='purchase_credits' value='true'>
                <select name='price' id="safecart_price">
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
                <input name='purchase' class="purchase" type='submit' onclick="purchase_safecart('#safecart_price');
                        return false;" value='Purchase Credits'>
            </div>
        </div>
<?php } ?>
</div>
<div class="hidden contentTitle">
    <div class=" bold largeFont left">
        <a href="<?= (admin_url('admin.php?page=constant-content-content')) ?>">Content</a>
    </div>
    <div class="right hide" hide='content'><?php
        if ($toHide['content'] == true) {
            echo 'open';
        } else {
            echo 'hide';
        }
        ?></div>
</div>
<div class="hidden catalogTitle">
    <div class=" bold largeFont left">
        <a href="<?= (admin_url('admin.php?page=constant-content-catalog')) ?>">Catalog</a>
    </div>
    <select class='right' name='categories' id='categories' style='width: 200px;padding-left: 15px;'>
        <option value=''>Filter By Category</option>
        <?php foreach ($categories as $category) {
            ?>
            <option value="<?= $category['id'] ?>" class="optionGroup"><?= $category['name'] ?></option>
            <?php foreach ($category['subcat'] as $subcat) { ?>
                <option value="<?= $subcat['id'] ?>" class="optionChild"><?= $subcat['name'] ?></option>
                <?php
            }
        }
        ?>
    </select>
</div>
<div class="right hide catalogHide hidden" hide='catalog'><?php
    if ($toHide['catalog'] == true) {
        echo 'open';
    } else {
        echo 'hide';
    }
    ?></div>
<script type = "text/javascript">
    jQuery(document).ready(function () {
        jQuery(function () {
            var ataglanceTableLoaded = false;
            var ataglanceTable = jQuery("#ataglance").dataTable({
                "aoColumns": [{"bSortable": false}],
                'iDisplayLength': 1,
                'bLengthChange': false,
                'bFilter': false,
                'bPaginate': false,
                'bInfo': false,
                'dom': '<"ataglance"<"top"f>rt<"bottom<?php
    if ($toHide['ataglance'] == true) {
        echo ' hidden ';
    }
    ?>"i<"right"l><"pagination right"p>>><"clear">',
                "fnDrawCallback": function (oSettings) {
                    jQuery('.ataglance tr th').removeClass('sorting_asc');
                },
                fnPreDrawCallback: function ( ) {
                    if (!ataglanceTableLoaded) {
                        var notificationTitle = jQuery('div.ataglanceTitle').clone().removeClass('hidden');
                        jQuery('div.ataglance .top').append(notificationTitle);
                        ataglanceTableLoaded = true;
                        activateHide();
                    }
                },
            });

            var notificationTableLoaded = false;
            var notificationTable = jQuery("#notificationTable").dataTable({
                "order": [[1, "esc"]],
                'iDisplayLength': 5,
                'bLengthChange': false,
                'bFilter': false,
                oLanguage: {
                    sEmptyTable: "You haven’t received any notifications yet."
                },
                "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=notifications'); ?>",
                'dom': '<"notifications"<"top"f>rt<"bottom<?php
    if ($toHide['notifications'] == true) {
        echo ' hidden ';
    }
    ?>"i<"right"l><"pagination right"p>>><"clear">',
                fnPreDrawCallback: function ( ) {
                    if (!notificationTableLoaded) {
                        var notificationTitle = jQuery('div.notificationTitle').clone().removeClass('hidden');
                        jQuery('div.notifications .top').append(notificationTitle);
                        notificationTableLoaded = true;
                        activateHide();
                    }
                },
                "fnDrawCallback": function (oSettings) {
                    hideLoading("#notificationTable");
                }
            });

            var requestTableLoaded = false;
            var requestTable = jQuery("#requestTable").dataTable({
                "order": [[2, "desc"]],
                'iDisplayLength': 5,
                'bLengthChange': false,
                'bFilter': false,
                oLanguage: {
                    sEmptyTable: "You haven’t issues any requests yet."
                },
                "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=requests&source=dashboard'); ?>",
                'dom': '<"requests"<"top"f>rt<"bottom<?php
    if ($toHide['requests'] == true) {
        echo ' hidden ';
    }
    ?>"i<"right"l><"pagination right"p>>><"clear">',
                "columnDefs": [
                    {
                        "targets": [0],
                        "visible": false,
                    }
                ],
                fnPreDrawCallback: function ( ) {
                    if (!requestTableLoaded) {
                        var requestsTitle = jQuery('div.requestsTitle').clone().removeClass('hidden');
                        jQuery('div.requests .top').append(requestsTitle);
                        var requestsBottom = jQuery('div.requestsBottom').clone().removeClass('hidden');
                        jQuery('div.requests .bottom').prepend(requestsBottom);
                        requestTableLoaded = true;
                        activateHide();
                    }
                },
                fnDrawCallback: function (oSettings, json) {
                    jQuery('.request_docs').click(function () {
                        var row = jQuery(this).parent().parent();
                        if (jQuery('#requestTable').dataTable().fnIsOpen(row)) {
                            jQuery('#requestTable').dataTable().fnClose(row);
                            return false;
                        }
                        var requestDocuments = jQuery(row).find('.request_details').first().clone().removeClass('hidden');

                        jQuery("#requestTable").dataTable().fnOpen(row, requestDocuments, "info_row");
                        jQuery(".requestDocForm").submit(function () {
                            var source = this;
                            jQuery.ajax({
                                url: "<?= (admin_url('admin-ajax.php?action=constant-content-ajax-data')) ?>",
                                data: jQuery(this).serialize(),
                                dataType: 'json',
                                beforeSend: function () {
                                    jQuery(source).html('Adding Document To Order');
                                },
                                success: function (data) {
                                    if (data.message != '') {
                                        jQuery(source).html(data.message);
                                    }
                                    if (data.order_id != '') {
                                        jQuery(source).html(jQuery(source).html() + "<br/><a href='<?= admin_url('admin.php?page=constant-content-orders&order_id='); ?>" + data.order_id + "'>Proceed to Checkout</a>");
                                    }
                                }
                            });
                            return false;
                        });

                    });
                    hideLoading("#requestTable");
                }
            });

            var contentTableLoaded = false;
            var contentTable = jQuery("#contentTable").dataTable({
                "order": [[2, "desc"]],
                "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=content'); ?>",
                'iDisplayLength': 5,
                'bLengthChange': false,
                'bFilter': false,
                'autoSize': false,
                oLanguage: {
                    sEmptyTable: "You haven’t purchased any content yet."
                },
                'dom': '<"content"<"top"f>rt<"bottom<?php
    if ($toHide['content'] == true) {
        echo ' hidden ';
    }
    ?>"i<"right"l><"pagination right"p>>><"clear">',
                "columnDefs": [
                    {
                        "targets": [0],
                        "visible": false,
                    }
                ],
                fnPreDrawCallback: function ( ) {
                    if (!contentTableLoaded) {
                        var notificationTitle = jQuery('div.contentTitle').clone().removeClass('hidden');
                        jQuery('div.content .top').append(notificationTitle);
                        contentTableLoaded = true;
                        activateHide();
                    }
                },
                "fnDrawCallback": function (oSettings, json) {
                    jQuery('.document_id').click(function () {
                        var row = jQuery(this).parent().parent();
                        if (jQuery('#contentTable').dataTable().fnIsOpen(row)) {
                            jQuery('#contentTable').dataTable().fnClose(row);
                            return false;
                        }
                        var orderDetailsHtml = jQuery(row).find('.order_details').first().clone().removeClass('hidden');

                        jQuery("#contentTable").dataTable().fnOpen(row, orderDetailsHtml, "info_row");
                    });
                    hideLoading("#contentTable");
                }
            });

            var oldStart = 0;
            var catalogLoaded = false;
            var catalogTableLoaded = false;
            var catalogTable = jQuery('#catalogTable').dataTable({
                'iDisplayLength': 5,
                'bLengthChange': false,
                "bServerSide": true,
                serverSide: true,
                oLanguage: {
                    sEmptyTable: "0 Articles Match Your Search"
                },
                "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=catalog'); ?>",
                'dom': '<"catalog"<"top"<"right"fr>>t<"bottom<?php
    if ($toHide['catalog'] == true) {
        echo ' hidden ';
    }
    ?>"i<"right"l><"pagination right"p>>><"clear">',
                "columnDefs": [
                    {
                        "targets": [1],
                        "visible": false,
                    },
                    {
                        "targets": [0],
                        "sortable": false,
                    }
                ],
                aoColumns: [
                    {"sName": "details"},
                    {"sName": "category"},
                ],
                fnServerData: function (sSource, aoData, fnCallback) {
                    if (catalogLoaded) {
                        showLoading('#catalogTable tbody', 'Updating Catalog');
                    } else {
                        catalogLoaded = true;
                    }
                    jQuery.getJSON(sSource, aoData, function (json) {
                        /* Do whatever additional processing you want on the callback, then tell DataTables */
                        fnCallback(json)
                    });
                },
                fnPreDrawCallback: function (oSettings) {
                    if (!catalogTableLoaded) {
                        var catalogTitle = jQuery('div.catalogTitle').clone().removeClass('hidden');
                        jQuery('div.catalog .top').append(catalogTitle);
                        var catalogHide = jQuery('div.catalogHide').clone().removeClass('hidden');
                        jQuery('div.catalog .top').prepend(catalogHide);
                        jQuery('#categories').change(function () {
                            catalogTable.fnFilter(this.value, 1);
                        });
                        activateHide();
                        catalogTableLoaded = true;
                    }
                },
                fnDrawCallback: function (oSettings) {
                    if (oSettings._iDisplayStart != oldStart) {
                        var targetOffset = jQuery('#catalogTable').offset().top;
                        jQuery('html,body').animate({scrollTop: targetOffset}, 500);
                        oldStart = oSettings._iDisplayStart;
                    }
                    jQuery(".buydocform").submit(function () {
                        var source = this;
                        var articleTD = jQuery(source).parent().parent().parent().parent()[0];
                        var varaPos = catalogTable.fnGetPosition(articleTD);
                        jQuery.ajax({
                            url: "<?= (admin_url('admin-ajax.php?action=constant-content-ajax-data')) ?>",
                            data: jQuery(this).serialize(),
                            dataType: 'json',
                            beforeSend: function () {
                                var order_start_message = jQuery('.adding_to_order').clone().removeClass('added_to_order');
                                jQuery('.message', order_start_message).removeClass('hidden').html('Adding article to order <span class="loading"></span>');
                                catalogTable.fnUpdate(order_start_message.html(), varaPos[0], 0, false);
                            },
                            success: function (data) {
                                var order_end_message = jQuery('.added_to_order').clone().removeClass('added_to_order');
                                if (data.message != '') {
                                    jQuery('.message', order_end_message).removeClass('hidden').html(data.message);
                                }
                                if (data.order_id != '') {
                                    jQuery('.order_link', order_end_message).removeClass('hidden').html("<a style='float:none;' class='link' href='<?= admin_url('admin.php?page=constant-content-orders&order_id='); ?>" + data.order_id + "'>Proceed to Checkout</a>");
                                }
                                catalogTable.fnUpdate(order_end_message.html(), varaPos[0], 0, false);
                            }
                        });
                        return false;
                    });
                    hideLoading("#catalogTable");
                },
            });
        });
    });
</script>
<?php
require_once CONSTANTCONTENT_INCLUDE_DIR . 'footer.php';
