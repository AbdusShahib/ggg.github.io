<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

class page_style extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 600;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_page_style_title');
        $this->description = $this->app->getDef('cfg_ec_page_style_desc');
    }
}
