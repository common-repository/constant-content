<?php
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');

$publicationDates = ConstantContent::get('publishedDocs');
?>

<div class="titlebar">
    <div class="cc-content">
        <h2>My Content</h2>
    </div>
</div>
<div class="cc-content">
    <div id="massActionMessage"></div>
    <div class="updated success-msg"><?= $message ?></div>
    <table width='100%' class="stripe" id="contentTable">
        <thead>
            <tr>
                <th align='left' style='width: 25px;'>&nbsp;</th>
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
                <td colspan="6">
        <center><img src="<?= CONSTANTCONTENT_ASSET_DIR ?>images/spinning_wheel.gif"></center>
        <center>Loading Articles</center>
        </td>
        </tr>
        </tbody>
    </table>
    <br class="clearleft">
</div>
<div class="hidden">
    <form method="post" name="massActionForm" id="massActionForm">
        <input type="hidden" name="massAction" value="true">
    </form>
</div>
<div class="massActionDropdown hidden">
    Perform Mass Action:
    <select name="mass_action" onchange="massAction(this);">
        <option value="">Choose Action</option>
        <option value="create_post">Create Post</option>
        <option value="create_draft_post">Save Draft Post</option>
        <option value="create_page">Create Page</option>
        <option value="create_draft_page">Save Draft Page</option>
    </select>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery(function () {
            var contentTableLoaded = false;
            jQuery("#contentTable").dataTable({
                "order": [[3, "desc"]],
                "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=content'); ?>",
                'dom': '<"top"f>rt<"bottom"i<"right"l><"pagination center"p>><"clear">',
                oLanguage: {
                    sEmptyTable: "You haven’t purchased any content yet."
                },
                fnPreDrawCallback: function ( ) {
                    if (!contentTableLoaded) {
                        var dropdown = jQuery('div.massActionDropdown').clone().removeClass('hidden');
                        jQuery('div.top').append(dropdown);
                        contentTableLoaded = true;
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
                }
            });
        });
    });
</script>
<?php
require_once CONSTANTCONTENT_INCLUDE_DIR . 'footer.php';

