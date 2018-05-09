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
<form name="pe" action="<?php echo $OSCOM_Page->data['login_url']; ?>" method="post" target="_top">
  <?php echo HTML::hiddenField('email_address', $OSCOM_Page->data['email_address']); ?>
</form>
<script>
document.pe.submit();
</script>
</body>
</html>
