<?php
ob_start();
?>
<div style="width: 100%;">
    <h2>Create Your Free Account</h2>
    <form name="signup" id="signupForm" method="post">
        <input type="hidden" name="signup" value="true">
        <div style="float: left;clear: left">
            Name:
        </div>
        <div style="float: left;clear: left">
            <input name="name" size="40" value="">
        </div>
        <div style="float: left;clear: left">
            Email:
        </div>
        <div style="float: left;clear: left">
            <input name="email" size="40" value="">
        </div>
        <div style="float: left;clear: left">
            Phone:
        </div>
        <div style="float: left;clear: left">
            <input name="phone" size="40" value="">
        </div>
        <div style="float: left;clear: left">
            <input type="checkbox" name="licensing_rights"  style="float: left;width: 20px;margin: 0px;"/>
            <label style="font-size: 75%;float: left;">I understand the
                <a href="https://www.constant-content.com/about/how-does-the-license-system-work.htm" target="_blank">licensing rights</a>
                and accept the full
                <a href="https://www.constant-content.com/about/how-does-the-license-system-work.htm" target="_blank">
                    terms and conditions</a>.
            </label>
        </div>
        <div style="width: 140px;float: left;clear: left">
            <input id='submit_button' type="submit" name="submit" value="Sign Up" class="btn btn-orange btn-large">
        </div>
    </form>

</div>
<script type="text/javascript">
    jQuery('#signupForm').validate({
        rules: {
            'name': 'required',
            'email': {
                'required': true,
                'email': true,
            },
            'phone': 'required',
            'licensing_rights': 'required'
        },
        submitHandler: function (form) {
            jQuery('#submit_button').replaceWith('<input type="submit" disabled class="btn btn-orange btn-large" value="Working . . .">');
            form.submit();
        }
    });
</script>
<?php
$toReturn = array(
    'success' => true,
    'data' => ob_get_contents()
);
ob_clean();
wp_send_json($toReturn);
