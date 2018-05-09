<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\Log;

use OSC\OM\Registry;

class DeleteAll extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_PayPal = Registry::get('PayPal');

        $OSCOM_PayPal->db->delete('oscom_app_paypal_log');

        $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_delete_success'), 'success', 'PayPal');

        $OSCOM_PayPal->redirect('Log');
    }
}
