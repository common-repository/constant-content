<?php
require_once( CONSTANTCONTENT_INCLUDE_DIR . 'header.php');
$errorCode = (int)$_REQUEST['code'];
$message = 'Unknown error occured';
switch ($errorCode) {
    case 1:
        $message = 'Invalid Price Selected';
}
?>
<div class="container">
        <div class="error">
            <h1><?= $message ?></h1>
        </div>
</div>
<?php
require_once CONSTANTCONTENT_INCLUDE_DIR . 'footer.php';

