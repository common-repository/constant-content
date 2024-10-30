<?php
ConstantContent::cacheItems();
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'actions.php');
$toHide = ConstantContent::get('dashboard_hide');
?>
<div class="wrap">
    <div class="loader loader_template hidden">
        <div class="cc_spinner">
            <div></div>
        </div>
        <!--<img src="<?= CONSTANTCONTENT_ASSET_DIR ?>css/fancybox_loading@2x.gif">-->
        <center><div class="load-txt">Loading...</div></center>
    </div>
    <div id="confirm_dialog"></div>
    <div class="header">
        <img class="banner_top" src="<?= CONSTANTCONTENT_ASSET_DIR ?>images/cc-logo.png">
        <div id="documentationContainer" class='documentationContainer'>
            <div class="bottom <?php
            if ($toHide['documentation'] === true) {
                echo ' hidden ';
            }
            ?>">
                <div class="right hide" hide='documentation'>hide</div>
                <p>Use this plugin to order content and publish it directly to your WordPress site.
                    <br/>
                    Order custom content from the Requests section, and buy existing unique content from the Catalog section.
                    <br/>
                    Manage and publish your purchased content from the Content section.
                </p>
                <a class="btn" href="https://www.constant-content.com/about/constant-content-plugin-for-WordPress.htm" target="_blank">Read Documentation</a>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="defaultModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <a class='modal-close-fancy' data-dismiss="modal"></a>
            <div class="modal-content fancybox-inner">
                <!--<div class="modal-header">-->
                  <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
                <!--<h4 class="modal-title" id="myModalLabel">Modal title</h4>-->
                <!--</div>-->
                <div class="modal-body modal-overlay">
                    Text Goes Here
                </div>
                <!--<div class="modal-footer">-->
                <!--<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
                <!--<button type="button" class="btn btn-primary">Save changes</button>-->
                <!--</div>-->
            </div>
        </div>
    </div>
    <a class="hidden" href="" id="safecart_link" target="_blank">Buy Credits</a>
    <script type="text/javascript">
        var adminLink = "<?= admin_url('admin-ajax.php') ?>";
        var safecart_link = "<?= admin_url('admin-ajax.php?action=constant-content-ajax-data&data=buyCredit'); ?>";
    </script>
