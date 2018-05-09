<?php
use OSC\OM\HTML;
?>

<div class="panel panel-warning">
  <div class="panel-heading">
    <?= $OSCOM_PayPal->getDef('payflow_live_title'); ?>
  </div>

  <div class="panel-body">
    <div class="form-group">
      <label for="live_partner"><?= $OSCOM_PayPal->getDef('payflow_partner'); ?></label>
      <?php echo HTML::inputField('live_partner', OSCOM_APP_PAYPAL_PF_LIVE_PARTNER, 'id="live_partner"'); ?>
    </div>

    <div class="form-group">
      <label for="live_vendor"><?= $OSCOM_PayPal->getDef('payflow_merchant_login'); ?></label>
      <?php echo HTML::inputField('live_vendor', OSCOM_APP_PAYPAL_PF_LIVE_VENDOR, 'id="live_vendor"'); ?>
    </div>

    <div class="form-group">
      <label for="live_user"><?= $OSCOM_PayPal->getDef('payflow_user'); ?></label>
      <?php echo HTML::inputField('live_user', OSCOM_APP_PAYPAL_PF_LIVE_USER, 'id="live_user"'); ?>
    </div>

    <div class="form-group">
      <label for="live_password"><?= $OSCOM_PayPal->getDef('payflow_password'); ?></label>
      <?php echo HTML::inputField('live_password', OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD, 'id="live_password"'); ?>
    </div>
  </div>
</div>

<div class="panel panel-warning">
  <div class="panel-heading">
    <?= $OSCOM_PayPal->getDef('payflow_sandbox_title'); ?>
  </div>

  <div class="panel-body">
    <div class="form-group">
      <label for="sandbox_partner"><?= $OSCOM_PayPal->getDef('payflow_partner'); ?></label>
      <?php echo HTML::inputField('sandbox_partner', OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER, 'id="sandbox_partner"'); ?>
    </div>

    <div class="form-group">
      <label for="sandbox_vendor"><?= $OSCOM_PayPal->getDef('payflow_merchant_login'); ?></label>
      <?php echo HTML::inputField('sandbox_vendor', OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR, 'id="sandbox_vendor"'); ?>
    </div>

    <div class="form-group">
      <label for="sandbox_user"><?= $OSCOM_PayPal->getDef('payflow_user'); ?></label>
      <?php echo HTML::inputField('sandbox_user', OSCOM_APP_PAYPAL_PF_SANDBOX_USER, 'id="sandbox_user"'); ?>
    </div>

    <div class="form-group">
      <label for="sandbox_password"><?= $OSCOM_PayPal->getDef('payflow_password'); ?></label>
      <?php echo HTML::inputField('sandbox_password', OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD, 'id="sandbox_password"'); ?>
    </div>
  </div>
</div>
