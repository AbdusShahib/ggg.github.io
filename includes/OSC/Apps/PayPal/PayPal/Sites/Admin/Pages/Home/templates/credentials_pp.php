<?php
use OSC\OM\HTML;
?>

<div class="panel panel-warning">
  <div class="panel-heading">
    <?= $OSCOM_PayPal->getDef('paypal_live_title'); ?>
  </div>

  <div class="panel-body">
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label for="live_username"><?= $OSCOM_PayPal->getDef('paypal_api_username'); ?></label>
          <?php echo HTML::inputField('live_username', OSCOM_APP_PAYPAL_LIVE_API_USERNAME, 'id="live_username"'); ?>
        </div>

        <div class="form-group">
          <label for="live_password"><?= $OSCOM_PayPal->getDef('paypal_api_password'); ?></label>
          <?php echo HTML::inputField('live_password', OSCOM_APP_PAYPAL_LIVE_API_PASSWORD, 'id="live_password"'); ?>
        </div>

        <div class="form-group">
          <label for="live_signature"><?= $OSCOM_PayPal->getDef('paypal_api_signature'); ?></label>
          <?php echo HTML::inputField('live_signature', OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE, 'id="live_signature"'); ?>
        </div>
      </div>

      <div class="col-sm-6">
        <div class="form-group">
          <label for="live_merchant_id"><?= $OSCOM_PayPal->getDef('paypal_merchant_id'); ?></label>
          <?php echo HTML::inputField('live_merchant_id', OSCOM_APP_PAYPAL_LIVE_MERCHANT_ID, 'id="live_merchant_id"'); ?>
          <span class="help-block"><?= $OSCOM_PayPal->getDef('paypal_merchant_id_desc'); ?></span>
        </div>

        <div class="form-group">
          <label for="live_email"><?= $OSCOM_PayPal->getDef('paypal_email_address'); ?></label>
          <?php echo HTML::inputField('live_email', OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL, 'id="live_email"'); ?>
        </div>

        <div class="form-group">
          <label for="live_email_primary"><?= $OSCOM_PayPal->getDef('paypal_primary_email_address'); ?></label>
          <?php echo HTML::inputField('live_email_primary', OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY, 'id="live_email_primary"'); ?>
          <span class="help-block"><?= $OSCOM_PayPal->getDef('paypal_primary_email_address_desc'); ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="panel panel-warning">
  <div class="panel-heading">
    <?=$OSCOM_PayPal->getDef('paypal_sandbox_title'); ?>
  </div>

  <div class="panel-body">
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label for="sandbox_username"><?= $OSCOM_PayPal->getDef('paypal_api_username'); ?></label>
          <?php echo HTML::inputField('sandbox_username', OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME, 'id="sandbox_username"'); ?>
        </div>

        <div class="form-group">
          <label for="sandbox_password"><?= $OSCOM_PayPal->getDef('paypal_api_password'); ?></label>
          <?php echo HTML::inputField('sandbox_password', OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD, 'id="sandbox_password"'); ?>
        </div>

        <div class="form-group">
          <label for="sandbox_signature"><?= $OSCOM_PayPal->getDef('paypal_api_signature'); ?></label>
          <?php echo HTML::inputField('sandbox_signature', OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE, 'id="sandbox_signature"'); ?>
        </div>
      </div>

      <div class="col-sm-6">
        <div class="form-group">
          <label for="sandbox_merchant_id"><?= $OSCOM_PayPal->getDef('paypal_merchant_id'); ?></label>
          <?php echo HTML::inputField('sandbox_merchant_id', OSCOM_APP_PAYPAL_SANDBOX_MERCHANT_ID, 'id="sandbox_merchant_id"'); ?>
          <span class="help-block"><?= $OSCOM_PayPal->getDef('paypal_merchant_id_desc'); ?></span>
        </div>

        <div class="form-group">
          <label for="sandbox_email"><?= $OSCOM_PayPal->getDef('paypal_email_address'); ?></label>
          <?php echo HTML::inputField('sandbox_email', OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL, 'id="sandbox_email"'); ?>
        </div>

        <div class="form-group">
          <label for="sandbox_email_primary"><?= $OSCOM_PayPal->getDef('paypal_primary_email_address'); ?></label>
          <?php echo HTML::inputField('sandbox_email_primary', OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY, 'id="sandbox_email_primary"'); ?>
          <span class="help-block"><?= $OSCOM_PayPal->getDef('paypal_primary_email_address_desc'); ?></span>
        </div>
      </div>
    </div>
  </div>
</div>
