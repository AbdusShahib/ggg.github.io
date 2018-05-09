<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

$OSCOM_PayPal = Registry::get('PayPal');
$OSCOM_Page = Registry::get('Site')->getPage();
?>

<script>
var OSCOM = {
  htmlSpecialChars: function(string) {
    if ( string == null ) {
      string = '';
    }

    return $('<span />').text(string).html();
  },
  nl2br: function(string) {
    return string.replace(/\n/g, '<br />');
  },
  APP: {
    PAYPAL: {
      action: '<?php echo isset($OSCOM_Page->data['action']) ? $OSCOM_Page->data['action'] : ''; ?>',
      accountTypes: {
        live: <?php echo ($OSCOM_PayPal->hasApiCredentials('live') === true) ? 'true' : 'false'; ?>,
        sandbox: <?php echo ($OSCOM_PayPal->hasApiCredentials('sandbox') === true) ? 'true' : 'false'; ?>
      }
    }
  }
};
</script>

<div class="row" style="padding-bottom: 30px;">
  <div class="col-sm-6">
    <a href="<?= $OSCOM_PayPal->link(); ?>"><img src="<?= OSCOM::link('Shop/public/Apps/PayPal/PayPal/images/paypal.png', '', false); ?>" /></a>
  </div>

  <div class="col-sm-6 text-right text-muted">
    <?= $OSCOM_PayPal->getTitle() . ' v' . $OSCOM_PayPal->getVersion() . ' <a href="' . $OSCOM_PayPal->link('Info') . '">' . $OSCOM_PayPal->getDef('app_link_info') . '</a> <a href="' . $OSCOM_PayPal->link('Privacy') . '">' . $OSCOM_PayPal->getDef('app_link_privacy') . '</a>'; ?>
  </div>
</div>

<?php
if ($OSCOM_MessageStack->exists('PayPal')) {
    echo $OSCOM_MessageStack->get('PayPal');
}
?>
