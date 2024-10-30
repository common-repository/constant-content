<?php
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');

$updated = null;
$message = '';
$created = false;
if (!empty($_REQUEST['signup'])) {
    if ($_REQUEST['signup'] == 'true') {
        if (!empty($_REQUEST['name']) || !empty($_REQUEST['email']) || !empty($_REQUEST['phone']) || !empty($_REQUEST['country'])) {
            $postValues = array(
                'name' => $_REQUEST['name'],
                'email' => $_REQUEST['email'],
                'phone' => $_REQUEST['phone']
            );
            $resultXML = ConstantContent::accessAPI('customer', 'post', $postValues);
            $doc = new DOMDocument();
            $doc->loadXML($resultXML);
            $result = ConstantContent::checkElementValue($doc, 'success', 'true');
            if ($result === true) {
                $created = true;
                $customer = ConstantContent::findFirstNode($doc, 'customer');
                $email = ConstantContent::getTextNode($customer, 'email');
                $account_key = ConstantContent::getTextNode($customer, 'api_key');
                $updated = ConstantContent::validateKey($email, $account_key);
            } else {
                $theMessage = ConstantContent::getTextNode($doc, 'message');
                $theError = ConstantContent::getTextNode($doc, 'error');
                if (!empty($theMessage)) {
                    ?>
                    <div class="error">
                        <p>
                            <strong><?= $theMessage ?></strong>
                        </p>
                    </div>
                    <?php
                }
                if (!empty($theError)) {
                    ?>
                    <div class="error">
                        <p>
                            <strong><?= $theError ?></strong>
                        </p>
                    </div>
                    <?php
                }
            }
        }
    }
}
if (!empty($_REQUEST['update'])) {
    $try_email = $_REQUEST['account_email'];
    $try_key = $_REQUEST['account_key'];
    $updated = ConstantContent::validateKey($try_email, $try_key);
}
if (!empty($_REQUEST['unlink'])) {
    ConstantContent::clearCache();
    ConstantContent::save('account_email', null);
    ConstantContent::save('account_key', null);
    ConstantContent::save('valid_key', false);
}
?>
<div class="container">
    <div class="cc-content">
        <h2>Constant Content Plugin Settings</h2>
        <h4>
            version <?= ConstantContent::version ?>
        </h4>
        <div>
            <?php
            if ($updated === true) {
                echo '<div class="updated">';
                echo '<p>';
                echo '<strong>Connected to Account.</strong>';
                echo '</p>';
                echo '</div>';
            } elseif ($updated === false) {
                echo '<div class="error">';
                echo '<p>';
                echo '<strong>Error linking account.  Invalid Account Email and Site Key combination.</strong>';
                echo '</p>';
                echo '</div>';
            }

            $valid_key = ConstantContent::get('valid_key');
            $account_email = ConstantContent::get('account_email');
            $account_key = ConstantContent::get('account_key');
            if ($valid_key === true) {
                ConstantContent::versionCheck();
            }
            $version_message = ConstantContent::get('version_message');
            if ($version_message) {
                ?>
                <div class="error">
                    <p>
                        <strong><?= $version_message ?></strong>
                    </p>
                </div>
                <?php
            }
            if (!ConstantContent::_is_curl_installed() && !ConstantContent::_is_https_installed()) {
                ?>
                <div class="error">
                    <p>
                        <strong>Unable to access the API via Curl or Https, make sure one of these extentions is installed.</strong>
                    </p>
                </div>

                <?php
            }
            if ($valid_key === true) {
                if ($updated === true) {
                    ?>
                    <div class="updated">
                        <h3>Success!</h3>
                        <div><?= $message ?></div>
                        <br/>
                        <?php if ($created) { ?>
                            <div>Your account has been created and you can now use the plugin.  Please check your email for more instructions and your temporary password.</div>
                            <br/>
                        <?php } ?>
                        <div><a class="" href="<?= (admin_url('admin.php?page=constant-content-dashboard')) ?>">Get Started</a> using the Constant Content Plugin.
                        </div>
                    </div>
                    <br/>
                    <?php
                }
                ?>
                <div>
                    <p>You have installed the Content Content Plugin for WordPress and linked it to the account listed below</p>
                    <p>You may update the email or site key used as needed, or click the Unlink Account button to remove all account details.</p>
                    </p>
                </div>
                <br/>
                <?php
            } else {
                if (empty($account_key)) {
                    if (!empty($message)) {
                        ?>
                        <div class="error">
                            <h3>Error Registering Account</h3>
                            <div><?= $message ?></div>
                        </div>
                        <br/>
                        <?php
                    } else {
                        ?>
                        <div>
                            <p>You have installed the Constant Content Plugin for WordPress.</p>
                            <p>To get started, please enter your login email and Site Key provided to you in your account on <a href="https://www.constant-content.com/account/sitekey.htm" target='_blank'>www.constant-content.com</a>.</p>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="error">
                        <h3>Account Error</h3>
                        <p>There has been a problem accessing the constant content site using the provided Site Key.  If your Site Key has changed please update it.</p>
                        <p>If you are unsure if your Site Key has changed please visit <a href="https://www.constant-content.com/account/sitekey.htm" target='_blank'>www.constant-content.com</a> to verify it is unchanged.</p>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <form id="linkForm" method="post" action="<?= admin_url('admin.php?page=constant-content-settings') ?>">
            <div style="width: 140px;float: left;">
                Account Email:
            </div>
            <div style="width: 250px;float: left;">
                <input name="account_email" size="40" value="<?= $account_email ?>">
            </div>
            <br style="clear: left;">
            <div style="width: 140px;float: left;">
                Site Key:
            </div>
            <div style="width: 250px;float: left;">
                <input name="account_key" size="40" value="<?= $account_key ?>">
            </div>
            <?php if ($valid_key === true) { ?>
                <div style="width: 250px;clear: left;">
                    <input name="update" type="hidden" value="true" />
                    <input id="linkButton" name="submit" type="submit" value="Update Account" />
                </div>
                <div class="left" style="width: 250px;clear: left;">
                    <input id="unlinkButton"  name="unlink" type="submit" value="Unlink Account" onclick="unlinkAction();return false;">
                </div>
            <?php } else { ?>
                <div style="width: 250px;clear: left;">
                    <input name="update" type="hidden" value="true" />
                    <input id="linkButton" name="submit" type="submit" value="Link Account" />
                </div>
            <?php } ?>
        </form>
        <form id="unlinkForm" method="post">
            <input type="hidden" name="unlink" value="true">
        </form>
        <?php
        if ($valid_key !== true) {
            if (empty($account_key)) {
                ?>
                <br/>
                <div>
                    Have an account but don't have an Site key?  <a href="https://www.constant-content.com/account/sitekey.htm" target='_blank'>Click here to get one</a>
                </div>
                <br/>
                <div>
                    Don't have an account yet?
                    <br/>
                    <a class="fancybox-new btn btn-orange" onclick="signupAction(this)" link = "<?= (admin_url('admin-ajax.php?action=constant-content-signup')) ?>">Sign Up For Free</a>
                </div>
                <br/>
                <?php
            }
        }
        ?>
    </div>
</div>
<script type="text/javascript">
    jQuery('#linkForm').validate({
        rules: {
            'account_key': 'required',
            'account_email': {
                'required': true,
                'email': true,
            }
        },
        submitHandler: function (form) {
            jQuery('#linkButton').replaceWith('<input type="submit" disabled class="signup-btn" value="Working . . .">');
            jQuery('#unlinkButton').addClass('hidden');
            form.submit();
        }
    });
</script>
<?php
require_once CONSTANTCONTENT_INCLUDE_DIR . 'footer.php';
