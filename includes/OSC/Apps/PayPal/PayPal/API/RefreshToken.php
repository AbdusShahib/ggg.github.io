<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\API;

class RefreshToken extends \OSC\Apps\PayPal\PayPal\APIAbstract
{
    protected $type = 'login';

    public function execute(array $extra_params = null)
    {
        $params = [
            'client_id' => (OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') ? OSCOM_APP_PAYPAL_LOGIN_LIVE_CLIENT_ID : OSCOM_APP_PAYPAL_LOGIN_SANDBOX_CLIENT_ID,
            'client_secret' => (OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') ? OSCOM_APP_PAYPAL_LOGIN_LIVE_SECRET : OSCOM_APP_PAYPAL_LOGIN_SANDBOX_SECRET,
            'grant_type' => 'refresh_token'
        ];

        if (!empty($extra_params)) {
            $params = array_merge($params, $extra_params);
        }

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => (is_array($response) && !isset($response['error'])),
            'req' => $params
        ];
    }
}
