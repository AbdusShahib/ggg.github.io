<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Menu;

use OSC\OM\Registry;

use OSC\Apps\PayPal\PayPal\PayPal as PayPalApp;

class PayPal implements \OSC\OM\Modules\AdminMenuInterface
{
    public static function execute()
    {
        if (!Registry::exists('PayPal')) {
            Registry::set('PayPal', new PayPalApp());
        }

        $OSCOM_PayPal = Registry::get('PayPal');

        $OSCOM_PayPal->loadDefinitions('admin/modules/boxes/paypal');

        $paypal_menu = [
            [
                'code' => $OSCOM_PayPal->getVendor() . '\\' . $OSCOM_PayPal->getCode(),
                'title' => $OSCOM_PayPal->getDef('module_admin_menu_start'),
                'link' => $OSCOM_PayPal->link()
            ]
        ];

        $paypal_menu_check = [
            'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL',
            'OSCOM_APP_PAYPAL_LIVE_API_USERNAME',
            'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL',
            'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME',
            'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR'
        ];

        foreach ($paypal_menu_check as $value) {
            if (defined($value) && !empty(constant($value))) {
                $paypal_menu = [
                    [
                        'code' => $OSCOM_PayPal->getVendor() . '\\' . $OSCOM_PayPal->getCode(),
                        'title' => $OSCOM_PayPal->getDef('module_admin_menu_balance'),
                        'link' => $OSCOM_PayPal->link('Balance')
                    ],
                    [
                        'code' => $OSCOM_PayPal->getVendor() . '\\' . $OSCOM_PayPal->getCode(),
                        'title' => $OSCOM_PayPal->getDef('module_admin_menu_configure'),
                        'link' => $OSCOM_PayPal->link('Configure')
                    ],
                    [
                        'code' => $OSCOM_PayPal->getVendor() . '\\' . $OSCOM_PayPal->getCode(),
                        'title' => $OSCOM_PayPal->getDef('module_admin_menu_manage_credentials'),
                        'link' => $OSCOM_PayPal->link('Credentials')
                    ],
                    [
                        'code' => $OSCOM_PayPal->getVendor() . '\\' . $OSCOM_PayPal->getCode(),
                        'title' => $OSCOM_PayPal->getDef('module_admin_menu_log'),
                        'link' => $OSCOM_PayPal->link('Log')
                    ]
                ];

                break;
            }
        }

        return array('heading' => $OSCOM_PayPal->getDef('module_admin_menu_title'),
                     'apps' => $paypal_menu);
    }
}
