<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\API;

class UserInfo extends \OSC\Apps\PayPal\PayPal\APIAbstract
{
    protected $type = 'login';

    public function execute(array $params = null)
    {
        $this->url = 'https://api.' . ($this->server != 'live' ? 'sandbox.' : '') . 'paypal.com/v1/identity/openidconnect/userinfo/?schema=openid&access_token=' . $params['access_token'];

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => (is_array($response) && !isset($response['error'])),
            'req' => $params
        ];
    }
}
