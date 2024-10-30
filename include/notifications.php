<?php
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');

$message = '';
?>
<div class="titlebar">
    <div class="cc-content">
        <h2>Notifications</h2>
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
    <div class="right" style="width:100%;">
        <table id="notificationTable" width="100%">
            <thead>
                <tr>
                <th>Notifications</th>
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
</div>
<script type = "text/javascript">
    jQuery(document).ready(function () {
        jQuery(function () {
            jQuery("#notificationTable").dataTable({
                "order": [[1, "desc"]],
                oLanguage: {
                    sEmptyTable: "You haven’t received any notifications yet."
                },
                "sAjaxSource": "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=notifications'); ?>",
                'dom': '<"top"f>rt<"bottom"i<"right"l><"pagination center"p>><"clear">'
            });
        });
    });
</script>