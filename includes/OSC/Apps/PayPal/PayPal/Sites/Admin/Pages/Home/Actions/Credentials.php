<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions;

use OSC\OM\Registry;

class Credentials extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        $this->page->setFile('credentials.php');
        $this->page->data['action'] = 'Credentials';

        $OSCOM_PayPal->loadDefinitions('admin/credentials');

        $modules = [
            'PP',
            'PF'
        ];

        $this->page->data['current_module'] = (isset($_GET['module']) && in_array($_GET['module'], $modules) ? $_GET['module'] : $modules[0]);

        $data = [
            'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL',
            'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY',
            'OSCOM_APP_PAYPAL_LIVE_API_USERNAME',
            'OSCOM_APP_PAYPAL_LIVE_API_PASSWORD',
            'OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE',
            'OSCOM_APP_PAYPAL_LIVE_MERCHANT_ID',
            'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL',
            'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY',
            'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME',
            'OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD',
            'OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE',
            'OSCOM_APP_PAYPAL_SANDBOX_MERCHANT_ID',
            'OSCOM_APP_PAYPAL_PF_LIVE_PARTNER',
            'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR',
            'OSCOM_APP_PAYPAL_PF_LIVE_USER',
            'OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_USER',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD'
        ];

        foreach ($data as $key) {
            if (!defined($key)) {
                $OSCOM_PayPal->saveCfgParam($key, '');
            }
        }
    }
}
