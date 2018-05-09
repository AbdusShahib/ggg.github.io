<?php
use OSC\OM\HTML;

require(__DIR__ . '/template_top.php');
?>

<div class="row">
  <div class="col-sm-6">
    <div class="panel panel-info">
      <div class="panel-heading">
        <?= $OSCOM_PayPal->getDef('online_documentation_title'); ?>
      </div>

      <div class="panel-body">
        <?=
            $OSCOM_PayPal->getDef('online_documentation_body', [
                'button_online_documentation' => HTML::button($OSCOM_PayPal->getDef('button_online_documentation'), null, 'https://library.oscommerce.com/Package&paypal&oscom24', ['newwindow' => true], 'btn-info')
            ]);
        ?>
      </div>
    </div>
  </div>

  <div class="col-sm-6">
    <div class="panel panel-warning">
      <div class="panel-heading">
        <?= $OSCOM_PayPal->getDef('online_forum_title'); ?>
      </div>

      <div class="panel-body">
        <?=
            $OSCOM_PayPal->getDef('online_forum_body', [
                'button_online_forum' => HTML::button($OSCOM_PayPal->getDef('button_online_forum'), null, 'http://forums.oscommerce.com/forum/54-paypal/', ['newwindow' => true], 'btn-warning')
            ]);
        ?>
      </div>
    </div>
  </div>
</div>

<div id="ppNewsContent"></div>

<script>
$(function() {
  OSCOM.APP.PAYPAL.versionCheck();

  $.getJSON('<?php echo addslashes($OSCOM_PayPal->link('RPC&GetNews')); ?>', function (data) {
    if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
      var ppNewsContent = '<div style="display: block; margin-top: 5px; min-height: 65px;"><a href="' + data.url + '" target="_blank"><img src="' + data.image + '" width="468" height="60" alt="' + data.title + '" border="0" /></a>';

      if ( data.status_update.length > 0 ) {
        ppNewsContent = ppNewsContent + '<div style="font-size: 0.95em; padding-left: 480px; margin-top: -70px; padding-top: 4px; min-height: 60px;"><p>' + data.status_update + '</p></div>';
      }

      ppNewsContent = ppNewsContent + '</div>';

      $('#ppNewsContent').html(ppNewsContent);
    }
  });
});
</script>

<?php
require(__DIR__ . '/template_bottom.php');
?>
