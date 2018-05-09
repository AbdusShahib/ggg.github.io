<?php
use OSC\OM\HTML;
use OSC\OM\Registry;

$OSCOM_Page = Registry::get('Site')->getPage();

require(__DIR__ . '/template_top.php');
?>

<div class="text-right">
  <?= HTML::button($OSCOM_PayPal->getDef('button_back'), null, $OSCOM_PayPal->link('Log&page=' . $_GET['page']), null, 'btn-info'); ?>
</div>

<table class="table table-hover">
  <thead>
    <tr class="info">
      <th colspan="2"><?= $OSCOM_PayPal->getDef('table_heading_entries_request'); ?></th>
    </tr>
  </thead>
  <tbody>

<?php
foreach ($OSCOM_Page->data['log_request'] as $key => $value) {
?>

    <tr>
      <td width="25%"><?= HTML::outputProtected($key); ?></td>
      <td><?= HTML::outputProtected($value); ?></td>
    </tr>

<?php
}
?>

  </tbody>
</table>

<table class="table table-hover">
  <thead>
    <tr class="info">
      <th colspan="2"><?= $OSCOM_PayPal->getDef('table_heading_entries_response'); ?></th>
    </tr>
  </thead>
  <tbody>

<?php
foreach ($OSCOM_Page->data['log_response'] as $key => $value) {
?>

    <tr>
      <td width="25%"><?= HTML::outputProtected($key); ?></td>
      <td><?= HTML::outputProtected($value); ?></td>
    </tr>

<?php
}
?>

  </tbody>
</table>

<?php
require(__DIR__ . '/template_bottom.php');
?>
