<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;

require(__DIR__ . '/template_top.php');

$Qlog = $OSCOM_PayPal->db->prepare('select SQL_CALC_FOUND_ROWS l.id, l.customers_id, l.module, l.action, l.result, l.ip_address, unix_timestamp(l.date_added) as date_added, c.customers_firstname, c.customers_lastname from :table_oscom_app_paypal_log l left join :table_customers c on (l.customers_id = c.customers_id) order by l.date_added desc limit :page_set_offset, :page_set_max_results');
$Qlog->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
$Qlog->execute();
?>

<div class="text-right">
  <?= HTML::button($OSCOM_PayPal->getDef('button_dialog_delete'), null, '#', ['params' => 'data-button="delLogs"'], 'btn-warning'); ?>
</div>

<table id="ppTableLog" class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th colspan="2"><?= $OSCOM_PayPal->getDef('table_heading_action'); ?></th>
      <th><?= $OSCOM_PayPal->getDef('table_heading_ip'); ?></th>
      <th><?= $OSCOM_PayPal->getDef('table_heading_customer'); ?></th>
      <th class="text-right"><?= $OSCOM_PayPal->getDef('table_heading_date'); ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php
if ($Qlog->getPageSetTotalRows() > 0) {
    while ($Qlog->fetch()) {
        $customers_name = null;

        if ($Qlog->valueInt('customers_id') > 0) {
            $customers_name = trim($Qlog->value('customers_firstname') . ' ' . $Qlog->value('customers_lastname'));

            if (empty($customers_name)) {
                $customers_name = '- ? -';
            }
        }
?>

    <tr>
      <td style="text-align: center; width: 30px;"><span class="label <?= ($Qlog->valueInt('result') === 1) ? 'label-success' : 'label-danger'; ?>"><?= $Qlog->value('module'); ?></span></td>
      <td><?= $Qlog->value('action'); ?></td>
      <td><?= long2ip($Qlog->value('ip_address')); ?></td>
      <td><?= (!empty($customers_name)) ? HTML::outputProtected($customers_name) : '<i>' . $OSCOM_PayPal->getDef('guest') . '</i>'; ?></td>
      <td class="text-right"><?= date(OSCOM::getDef('php_date_time_format'), $Qlog->value('date_added')); ?></td>
      <td class="action"><a href="<?= $OSCOM_PayPal->link('Log&View&page=' . (isset($_GET['page']) ? $_GET['page'] : 1) . '&lID=' . $Qlog->valueInt('id')); ?>"><i class="fa fa-file-text-o" title="<?= $OSCOM_PayPal->getDef('button_view'); ?>"></i></a></td>
    </tr>

<?php
    }
  } else {
?>

    <tr>
      <td colspan="6"><?= $OSCOM_PayPal->getDef('no_entries'); ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

<div>
  <span class="pull-right"><?= $Qlog->getPageSetLinks(tep_get_all_get_params(array('page'))); ?></span>
  <?= $Qlog->getPageSetLabel($OSCOM_PayPal->getDef('listing_number_of_log_entries')); ?>
</div>

<div id="delLogs-dialog-confirm" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= $OSCOM_PayPal->getDef('dialog_delete_title'); ?></h4>
      </div>

      <div class="modal-body">
        <p><?= $OSCOM_PayPal->getDef('dialog_delete_body'); ?></p>
      </div>

      <div class="modal-footer">
        <?= HTML::button($OSCOM_PayPal->getDef('button_delete'), null, $OSCOM_PayPal->link('Log&DeleteAll'), null, 'btn-danger'); ?>
        <?= HTML::button($OSCOM_PayPal->getDef('button_cancel'), null, '#', ['params' => 'data-dismiss="modal"'], 'btn-link'); ?>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  $('a[data-button="delLogs"]').click(function(e) {
    e.preventDefault();

    $('#delLogs-dialog-confirm').modal('show');
  });
});
</script>

<?php
require(__DIR__ . '/template_bottom.php');
?>
