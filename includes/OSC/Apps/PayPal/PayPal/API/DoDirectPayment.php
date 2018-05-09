<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\API;

use OSC\OM\HTTP;

class DoDirectPayment extends \OSC\Apps\PayPal\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
          'USER' => $this->app->getCredentials('DP', 'username'),
          'PWD' => $this->app->getCredentials('DP', 'password'),
          'SIGNATURE' => $this->app->getCredentials('DP', 'signature'),
          'METHOD' => 'DoDirectPayment',
          'PAYMENTACTION' => (OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD == '1') ? 'Sale' : 'Authorization',
          'IPADDRESS' => HTTP::getIpAddress(),
          'BUTTONSOURCE' => $this->app->getIdentifier()
        ];

        if (!empty($extra_params)) {
            $params = array_merge($params, $extra_params);
        }

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => in_array($response['ACK'], ['Success', 'SuccessWithWarning']),
            'req' => $params
        ];
    }
}
