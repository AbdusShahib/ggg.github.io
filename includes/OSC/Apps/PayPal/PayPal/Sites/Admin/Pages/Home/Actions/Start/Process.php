<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\Start;

use OSC\OM\HTTP;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Process extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_PayPal = Registry::get('PayPal');

        if (isset($_GET['type']) && in_array($_GET['type'], [
            'live',
            'sandbox'
        ])) {
            $params = [
                'return_url' => $OSCOM_PayPal->link('Start&Retrieve'),
                'type' => $_GET['type'],
                'site_url' => OSCOM::link('Shop/index.php', null, false),
                'site_currency' => DEFAULT_CURRENCY
            ];

            if (!empty(STORE_OWNER_EMAIL_ADDRESS) && (filter_var(STORE_OWNER_EMAIL_ADDRESS, FILTER_VALIDATE_EMAIL) !== false)) {
                $params['email'] = STORE_OWNER_EMAIL_ADDRESS;
            }

            if (!empty(STORE_OWNER)) {
                $name_array = explode(' ', STORE_OWNER, 2);

                $params['firstname'] = $name_array[0];
                $params['surname'] = isset($name_array[1]) ? $name_array[1] : '';
            }

            if (!empty(STORE_NAME)) {
                $params['site_name'] = STORE_NAME;
            }

            $result = HTTP::getResponse([
              'url' => 'https://www.oscommerce.com/index.php?RPC&Website&Index&PayPalStart&v=2',
              'parameters' => $params
            ]);

            $result = json_decode($result, true);

            if (!empty($result) && is_array($result) && isset($result['rpcStatus'])) {
                if (($result['rpcStatus'] === 1) && isset($result['merchant_id']) && (preg_match('/^[A-Za-z0-9]{32}$/', $result['merchant_id']) === 1) && isset($result['redirect_url']) && isset($result['secret'])) {
                    $OSCOM_PayPal->saveCfgParam('OSCOM_APP_PAYPAL_START_MERCHANT_ID', $result['merchant_id']);
                    $OSCOM_PayPal->saveCfgParam('OSCOM_APP_PAYPAL_START_SECRET', $result['secret']);

                    HTTP::redirect($result['redirect_url']);
                } elseif ($result['rpcStatus'] === -110) {
                    $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_onboarding_currently_unavailable_error'), 'error', 'PayPal');
                } else {
                    $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_onboarding_initialization_error'), 'error', 'PayPal');
                }
            } else {
                $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_onboarding_connection_error'), 'error', 'PayPal');
            }
        } else {
            $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_onboarding_account_type_error'), 'error', 'PayPal');
        }

        $OSCOM_PayPal->redirect('Credentials');
    }
}
