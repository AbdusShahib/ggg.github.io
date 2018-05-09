<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\Start;

use OSC\OM\HTTP;
use OSC\OM\Registry;

class Retrieve extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_PayPal = Registry::get('PayPal');

        $params = [
          'merchant_id' => OSCOM_APP_PAYPAL_START_MERCHANT_ID,
          'secret' => OSCOM_APP_PAYPAL_START_SECRET
        ];

        $result = HTTP::getResponse([
          'url' => 'https://www.oscommerce.com/index.php?RPC&Website&Index&PayPalStart&action=retrieve&v=2',
          'parameters' => $params
        ]);

        if (!empty($result)) {
            $result = json_decode($result, true);

            if (isset($result['rpcStatus']) && ($result['rpcStatus'] === 1) && isset($result['account_type']) && in_array($result['account_type'], ['live', 'sandbox']) && isset($result['account_id']) && isset($result['api_username']) && isset($result['api_password']) && isset($result['api_signature'])) {
                if ($result['account_type'] == 'live') {
                    $param_prefix = 'OSCOM_APP_PAYPAL_LIVE_';
                } else {
                    $param_prefix = 'OSCOM_APP_PAYPAL_SANDBOX_';
                }

                $OSCOM_PayPal->saveCfgParam($param_prefix . 'SELLER_EMAIL', str_replace('_api1.', '@', $result['api_username']));
                $OSCOM_PayPal->saveCfgParam($param_prefix . 'SELLER_EMAIL_PRIMARY', str_replace('_api1.', '@', $result['api_username']));
                $OSCOM_PayPal->saveCfgParam($param_prefix . 'MERCHANT_ID', $result['account_id']);
                $OSCOM_PayPal->saveCfgParam($param_prefix . 'API_USERNAME', $result['api_username']);
                $OSCOM_PayPal->saveCfgParam($param_prefix . 'API_PASSWORD', $result['api_password']);
                $OSCOM_PayPal->saveCfgParam($param_prefix . 'API_SIGNATURE', $result['api_signature']);

                $OSCOM_PayPal->deleteCfgParam('OSCOM_APP_PAYPAL_START_MERCHANT_ID');
                $OSCOM_PayPal->deleteCfgParam('OSCOM_APP_PAYPAL_START_SECRET');

                $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_onboarding_success'), 'success', 'PayPal');
            } else {
                $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_onboarding_retrieve_error'), 'error', 'PayPal');
            }
        } else {
            $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_onboarding_retrieve_connection_error'), 'error', 'PayPal');
        }

        $OSCOM_PayPal->redirect('Credentials');
    }
}
