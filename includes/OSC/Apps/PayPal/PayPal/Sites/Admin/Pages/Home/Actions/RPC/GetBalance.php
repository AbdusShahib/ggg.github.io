<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\RPC;

use OSC\OM\Cache;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class GetBalance extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        include(OSCOM::getConfig('dir_root') . 'includes/classes/currencies.php');
        $currencies = new \currencies();

        $result = [
            'rpcStatus' => -1
        ];

        if (isset($_GET['type']) && in_array($_GET['type'], [
            'live',
            'sandbox'
        ])) {
            $PayPalCache = new Cache('app_paypal-balance');

            if (!isset($_GET['force']) && $PayPalCache->exists(15)) {
                $response = $PayPalCache->get();
            } else {
                $response = $OSCOM_PayPal->getApiResult('APP', 'GetBalance', null, $_GET['type']);

                if (is_array($response) && isset($response['ACK']) && ($response['ACK'] == 'Success')) {
                    $PayPalCache->save($response);
                }
            }

            if (is_array($response) && isset($response['ACK']) && ($response['ACK'] == 'Success')) {
                $result['rpcStatus'] = 1;

                $counter = 0;

                while (true) {
                    if (isset($response['L_AMT' . $counter]) && isset($response['L_CURRENCYCODE' . $counter])) {
                        $balance = $response['L_AMT' . $counter];

                        if (isset($currencies->currencies[$response['L_CURRENCYCODE' . $counter]])) {
                            $balance = $currencies->format($balance, false, $response['L_CURRENCYCODE' . $counter]);
                        }

                        $result['balance'][$response['L_CURRENCYCODE' . $counter]] = $balance;

                        $counter++;
                    } else {
                        break;
                    }
                }
            }
        }

        echo json_encode($result);
    }
}
