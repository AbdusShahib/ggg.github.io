<?php
use OSC\OM\HTML;
use OSC\OM\Registry;

$OSCOM_Page = Registry::get('Site')->getPage();

$current_module = $OSCOM_Page->data['current_module'];

require(__DIR__ . '/template_top.php');
?>

<div class="row" style="padding-bottom: 15px;">
  <div class="col-sm-6">
    <ul id="appPayPalToolbar" class="nav nav-pills">
      <li data-module="PP"><a href="<?= $OSCOM_PayPal->link('Credentials&module=PP'); ?>"><?= $OSCOM_PayPal->getDef('section_paypal'); ?></a></li>
      <li data-module="PF"><a href="<?= $OSCOM_PayPal->link('Credentials&module=PF'); ?>"><?= $OSCOM_PayPal->getDef('section_payflow'); ?></a></li>
    </ul>
  </div>

<?php
if ($current_module == 'PP') {
?>

  <div class="col-sm-6 text-right">
    <?= HTML::button($OSCOM_PayPal->getDef('button_retrieve_live_credentials'), null, $OSCOM_PayPal->link('Start&Process&type=live'), null, 'btn-warning'); ?>
    <?= HTML::button($OSCOM_PayPal->getDef('button_retrieve_sandbox_credentials'), null, $OSCOM_PayPal->link('Start&Process&type=sandbox'), null, 'btn-warning'); ?>
  </div>

<?php
}
?>

</div>

<script>
$('#appPayPalToolbar li[data-module="<?= $current_module; ?>"]').addClass('active');
</script>

<form name="paypalCredentials" action="<?= $OSCOM_PayPal->link('Credentials&Process&module=' . $current_module); ?>" method="post">

<?php
require(__DIR__ . '/credentials_' . strtolower($current_module) . '.php');
?>

<p><?= HTML::button($OSCOM_PayPal->getDef('button_save'), null, null, null, 'btn-success'); ?></p>

</form>

<?php
require(__DIR__ . '/template_bottom.php');
?>
