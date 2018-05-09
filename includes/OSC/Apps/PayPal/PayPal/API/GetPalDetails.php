<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\API;

class GetPalDetails extends \OSC\Apps\PayPal\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
            'METHOD' => 'GetPalDetails',
            'USER' => $this->app->getCredentials('EC', 'username'),
            'PWD' => $this->app->getCredentials('EC', 'password'),
            'SIGNATURE' => $this->app->getCredentials('EC', 'signature')
        ];

        if (!empty($extra_params)) {
            $params = array_merge($params, $extra_params);
        }

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => isset($response['PAL']),
            'req' => $params
        ];
    }
}
