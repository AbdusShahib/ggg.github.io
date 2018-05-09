<?php
use OSC\OM\HTML;
use OSC\OM\Registry;

$OSCOM_Page = Registry::get('Site')->getPage();

$current_module = $OSCOM_Page->data['current_module'];

$OSCOM_PayPal_Config = Registry::get('PayPalAdminConfig' . $current_module);

require(__DIR__ . '/template_top.php');
?>

<ul id="appPayPalToolbar" class="nav nav-pills" style="padding-bottom: 15px;">

<?php
foreach ($OSCOM_PayPal->getConfigModules() as $m) {
    if ($OSCOM_PayPal->getConfigModuleInfo($m, 'is_installed') === true) {
        echo '<li data-module="' . $m . '"><a href="' . $OSCOM_PayPal->link('Configure&module=' . $m) . '">' . $OSCOM_PayPal->getConfigModuleInfo($m, 'short_title') . '</a></li>';
    }
}
?>

  <li class="dropdown"><a class="dropdown-toggle" href="#" data-toggle="dropdown">Install <span class="caret"></span></a>
    <ul class="dropdown-menu">

<?php
foreach ($OSCOM_PayPal->getConfigModules() as $m) {
    if ($OSCOM_PayPal->getConfigModuleInfo($m, 'is_installed') === false) {
        echo '<li><a href="' . $OSCOM_PayPal->link('Configure&module=' . $m) . '">' . $OSCOM_PayPal->getConfigModuleInfo($m, 'title') . '</a></li>';
    }
}
?>

    </ul>
  </li>
</ul>

<script>
if ($('#appPayPalToolbar li.dropdown ul.dropdown-menu li').size() === 0) {
  $('#appPayPalToolbar li.dropdown').hide();
}

$(function() {
  var active = '<?= ($OSCOM_PayPal->getConfigModuleInfo($current_module, 'is_installed') === true) ? $current_module : 'new'; ?>';

  if (active !== 'new') {
    $('#appPayPalToolbar li[data-module="' + active + '"]').addClass('active');
  } else {
    $('#appPayPalToolbar li.dropdown').addClass('active');
  }
});
</script>

<?php
if ($OSCOM_PayPal_Config->is_installed === true) {
    foreach ($OSCOM_PayPal_Config->req_notes as $rn) {
        echo '<div class="alert alert-warning"><p>' . $rn . '</p></div>';
    }
?>

<form name="paypalConfigure" action="<?= $OSCOM_PayPal->link('Configure&Process&module=' . $current_module); ?>" method="post">

<div class="panel panel-info oscom-panel">
  <div class="panel-heading">
    <?= $OSCOM_PayPal->getConfigModuleInfo($current_module, 'title'); ?>
  </div>

  <div class="panel-body">
    <div class="container-fluid">

<?php
    foreach ($OSCOM_PayPal_Config->getInputParameters() as $cfg) {
        echo $cfg;
    }
?>

    </div>
  </div>
</div>

<p>

<?php
    echo HTML::button($OSCOM_PayPal->getDef('button_save'), null, null, null, 'btn-success');

    if ($OSCOM_PayPal->getConfigModuleInfo($current_module, 'is_uninstallable') === true) {
        echo '<span class="pull-right">' . HTML::button($OSCOM_PayPal->getDef('button_dialog_uninstall'), null, '#', ['params' => 'data-toggle="modal" data-target="#ppUninstallModal"'], 'btn-warning') . '</span>';
    }
?>

</p>

</form>

<?php
    if ($OSCOM_PayPal->getConfigModuleInfo($current_module, 'is_uninstallable') === true) {
?>

<div id="ppUninstallModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?= $OSCOM_PayPal->getDef('dialog_uninstall_title'); ?></h4>
      </div>
      <div class="modal-body">
        <?= $OSCOM_PayPal->getDef('dialog_uninstall_body'); ?>
      </div>
      <div class="modal-footer">
        <?= HTML::button($OSCOM_PayPal->getDef('button_uninstall'), null, $OSCOM_PayPal->link('Configure&Uninstall&module=' . $current_module), null, 'btn-danger'); ?>
        <?= HTML::button($OSCOM_PayPal->getDef('button_cancel'), null, '#', ['params' => 'data-dismiss="modal"'], 'btn-link'); ?>
      </div>
    </div>
  </div>
</div>

<?php
    }
} else {
?>

<div class="panel panel-warning">
  <div class="panel-heading">
    <?= $OSCOM_PayPal->getConfigModuleInfo($current_module, 'title'); ?>
  </div>

  <div class="panel-body">
    <?= $OSCOM_PayPal->getConfigModuleInfo($current_module, 'introduction'); ?>
  </div>
</div>

<p>
  <?= HTML::button($OSCOM_PayPal->getDef('button_install_title', ['title' => $OSCOM_PayPal->getConfigModuleInfo($current_module, 'title')]), null, $OSCOM_PayPal->link('Configure&Install&module=' . $current_module), null, 'btn-warning'); ?>
</p>

<?php
}

require(__DIR__ . '/template_bottom.php');
?>
