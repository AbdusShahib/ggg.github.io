<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\Configure;

use OSC\OM\Registry;

class Install extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_PayPal = Registry::get('PayPal');

        $current_module = $this->page->data['current_module'];

        $m = Registry::get('PayPalAdminConfig' . $current_module);
        $m->install();

        $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_module_install_success'), 'success', 'PayPal');

        $OSCOM_PayPal->redirect('Configure&module=' . $current_module);
    }
}
