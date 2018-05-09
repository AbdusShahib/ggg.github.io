<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

class sandbox_secret extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 500;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_sandbox_secret_title');
        $this->description = $this->app->getDef('cfg_login_sandbox_secret_desc');
    }
}
