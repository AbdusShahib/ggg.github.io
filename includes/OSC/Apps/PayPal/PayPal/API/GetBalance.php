<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\API;

class GetBalance extends \OSC\Apps\PayPal\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
            'USER' => $this->app->getApiCredentials($this->server, 'username'),
            'PWD' => $this->app->getApiCredentials($this->server, 'password'),
            'SIGNATURE' => $this->app->getApiCredentials($this->server, 'signature'),
            'METHOD' => 'GetBalance',
            'RETURNALLCURRENCIES' => '1'
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
