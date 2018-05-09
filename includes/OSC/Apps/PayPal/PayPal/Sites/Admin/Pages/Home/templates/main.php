<?php
use OSC\OM\HTML;

require(__DIR__ . '/template_top.php');
?>

<div class="row">
  <div class="col-sm-6">
    <div class="panel panel-primary">
      <div class="panel-heading"><?= $OSCOM_PayPal->getDef('onboarding_intro_title'); ?></div>
      <div class="panel-body">
        <?=
            $OSCOM_PayPal->getDef('onboarding_intro_body', [
                'button_retrieve_live_credentials' => HTML::button($OSCOM_PayPal->getDef('button_retrieve_live_credentials'), null, $OSCOM_PayPal->link('Start&Process&type=live'), null, 'btn-primary'),
                'button_retrieve_sandbox_credentials' => HTML::button($OSCOM_PayPal->getDef('button_retrieve_sandbox_credentials'), null, $OSCOM_PayPal->link('Start&Process&type=sandbox'), null, 'btn-primary')
            ]);
        ?>
      </div>
    </div>
  </div>

  <div class="col-sm-6">
    <div class="panel panel-info">
      <div class="panel-heading"><?= $OSCOM_PayPal->getDef('manage_credentials_title'); ?></div>
      <div class="panel-body">
        <?=
            $OSCOM_PayPal->getDef('manage_credentials_body', [
                'button_manage_credentials' => HTML::button($OSCOM_PayPal->getDef('button_manage_credentials'), null, $OSCOM_PayPal->link('Credentials'), null, 'btn-info')
            ]);
        ?>
      </div>
    </div>
  </div>
</div>

<?php
require(__DIR__ . '/template_bottom.php');
?>
