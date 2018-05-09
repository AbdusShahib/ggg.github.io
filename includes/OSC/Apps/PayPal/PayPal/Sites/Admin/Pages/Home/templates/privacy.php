<?php
require(__DIR__ . '/template_top.php');
?>

<h2><?= $OSCOM_PayPal->getDef('privacy_title'); ?></h2>

<?= $OSCOM_PayPal->getDef('privacy_body'); ?>

<?php
require(__DIR__ . '/template_bottom.php');
?>
