<?php
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');

$categories = ConstantContent::get('category_list');
?>

<div class="titlebar">
    <div class="cc-content">
        <h2>The Catalog</h2>
        <div>Choose from thousands of pre-written unique articles to buy and publish immediately.</div>
    </div>
</div>

<div class="cc-content">
    <div class="error error_message fontRed<?php if (empty($message)) { ?> hidden <?php } ?>">
        <?php
        if (!empty($message)) {
            echo $message;
        }
        ?>
    </div>
    <select class='right hidden' name='categories' id='categories' style='width: 200px;padding-left: 15px;'>
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
    <table id="catalogTable" class="tablesorter">
        <thead>
            <tr>
            <th>The Catalog</th>
            <th>The Catalog</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <td>
                <center><img src="<?= CONSTANTCONTENT_ASSET_DIR ?>images/spinning_wheel.gif"></center>
                <center>Retrieving Articles</center>
            </td>
            <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    var oldStart = 0;
    var catalogLoaded = false;
    var catalogTable = jQuery('#catalogTable').dataTable({
        "bServerSide": true,
        serverSide: true,
        'dom': '<"top"f>rt<"bottom"i<"right"l><"pagination center"p>><"clear">',
        "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=catalog'); ?>",
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
        oLanguage: {
            sEmptyTable: "0 Articles Match Your Search"
        },
        fnPreDrawCallback: function (oSettings) {
        },
        fnServerData: function (sSource, aoData, fnCallback) {
            if (catalogLoaded) {
                showLoading('#catalogTable tbody', 'Updating Catalog');
            } else {
                var categorySearch = jQuery('#categories').removeClass('hidden');
                jQuery('#catalogTable_wrapper .top').prepend(categorySearch);
                jQuery('#categories').change(function () {
                    catalogTable.fnFilter(this.value, 1);
                });
                catalogLoaded = true;
            }
            jQuery.getJSON(sSource, aoData, function (json) {
                /* Do whatever additional processing you want on the callback, then tell DataTables */
                fnCallback(json)
            });
        },
        fnCreatedRow: function (nRow, aData, iDataIndex) {
            iDataIndex = aData[1]
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
                        var order_message = jQuery('.adding_to_order').clone().removeClass('added_to_order');
                        jQuery('.message', order_message).removeClass('hidden').html('Adding article to order <span class="loading"></span>');

                        catalogTable.fnUpdate(order_message.html(), varaPos[0], 0, false);
                    },
                    success: function (data) {
                        var order_message = jQuery('.added_to_order').clone().removeClass('added_to_order');

                        if (data.message != '') {
                            jQuery('.message', order_message).removeClass('hidden').html(data.message);

                        }
                        if (data.order_id != '') {
                            jQuery('.order_link', order_message).removeClass('hidden').html("<a style='float:none;' class='link' href='<?= admin_url('admin.php?page=constant-content-orders&order_id='); ?>" + data.order_id + "'>Proceed to Checkout</a>");
                        }
                        catalogTable.fnUpdate(order_message.html(), varaPos[0], 0, false);
                    }
                });
                return false;
            });
//            hideLoading("#catalogTable");
        },
    });
</script>
<?php
require_once CONSTANTCONTENT_INCLUDE_DIR . 'footer.php';
?>


