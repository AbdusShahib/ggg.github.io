<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\G;

class G extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigAbstract
{
    public $is_installed = true;
    public $sort_order = 100000;

    protected function init()
    {
        $this->title = $this->app->getDef('module_g_title');
        $this->short_title = $this->app->getDef('module_g_short_title');
    }

    public function install()
    {
        return false;
    }

    public function uninstall()
    {
        return false;
    }
}
