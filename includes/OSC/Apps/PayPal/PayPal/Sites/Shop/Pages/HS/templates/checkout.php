<?php
use OSC\OM\HTML;
use OSC\OM\Registry;

$OSCOM_Page = Registry::get('Site')->getPage();
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta charset="<?php echo CHARSET; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo HTML::outputProtected($oscTemplate->getTitle()); ?></title>
</head>
<body>
<div style="text-align: center;">
  <?php echo HTML::image('public/Apps/PayPal/PayPal/images/HS_load.gif'); ?>
</div>

<form name="pphs" action="<?php echo $OSCOM_Page->data['form_url']; ?>" method="post" <?php echo ($OSCOM_Page->data['is_error'] == true ? 'target="_top"' : ''); ?>>
  <?php echo HTML::hiddenField('hosted_button_id', $OSCOM_Page->data['hosted_button_id']); ?>
</form>
<script>
document.pphs.submit();
</script>
</body>
</html>
