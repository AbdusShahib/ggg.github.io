<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

class ewp_working_directory extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 1200;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_working_directory_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_working_directory_desc');
    }
}
