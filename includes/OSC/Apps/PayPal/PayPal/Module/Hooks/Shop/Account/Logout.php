<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Hooks\Shop\Account;

class Logout implements \OSC\OM\Modules\HooksInterface
{
    public function execute()
    {
        if (isset($_SESSION['paypal_login_access_token'])) {
            unset($_SESSION['paypal_login_access_token']);
        }
    }
}
