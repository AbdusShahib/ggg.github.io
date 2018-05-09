<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class ewp_status extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '-1';
    public $sort_order = 700;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_status_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_status_desc');
    }

    public function getInputField()
    {
        $value = $this->getInputValue();

        $input = '<div class="btn-group" data-toggle="buttons">' .
                 '  <label class="btn btn-info' . ($value == '1' ? ' active' : '') . '">' . HTML::radioField($this->key, '1', ($value == '1')) . $this->app->getDef('cfg_ps_ewp_status_true') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '-1' ? ' active' : '') . '">' . HTML::radioField($this->key, '-1', ($value == '-1')) . $this->app->getDef('cfg_ps_ewp_status_false') . '</label>' .
                 '</div>';

        return $input;
    }
}
