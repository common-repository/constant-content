<?php
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');

?>
<div class="titlebar">
    <div class="cc-content">
        <h2>My Revisions</h2>
    </div>
</div>
<?php
if (!empty($message)) {
    echo '<div class="error error_message">' . $message . '</div>';
}
?>
<div class="cc-content">

    <table style="width: 100%" id="revisionsTable" class="tablesorter">
        <thead>
            <tr>
                <th align="left">Document</th>
                <th align="left" style="width: 150px;">Cost</th>
                <th align="left" style="width: 150px;">Started / Updated</th>
                <th align="left" style="width: 150px;">State</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5">
        <center><img src="<?= CONSTANTCONTENT_ASSET_DIR ?>images/spinning_wheel.gif"></center>
        <center>Loading Revisions</center>
        </td>
        </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery(function () {
            var revisionsTable = jQuery("#revisionsTable").dataTable({
                "order": [[2, "desc"]],
                columnDefs: [
                    {type: 'sort-string', targets: 2}
                ],
                oLanguage: {
                    sEmptyTable: "You haven’t requested any revisions yet."
                },
                dom: '<"revisions"<"top"f>rt<"bottom"i<"right"l><"pagination center"p>>><"clear">',
                sAjaxSource: "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=revisions'); ?>"
            });
        });
    });
    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
        "sort-string-pre": function (a) {
            return a.match(/sort="(.*?)"/)[1].toLowerCase();
        },
        "sort-string-asc": function (a, b) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },
        "sort-string-desc": function (a, b) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        }
    });
</script>
<?php
require_once CONSTANTCONTENT_INCLUDE_DIR . 'footer.php';
