<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

class pdt_identity_token extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 650;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_pdt_identity_token_title');
        $this->description = $this->app->getDef('cfg_ps_pdt_identity_token_desc');
    }
}
