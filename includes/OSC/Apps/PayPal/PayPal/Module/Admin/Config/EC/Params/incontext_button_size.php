<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\HTML;

class incontext_button_size extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '2';
    public $sort_order = 220;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_incontext_button_size_title');
        $this->description = $this->app->getDef('cfg_ec_incontext_button_size_desc');
    }

    public function getInputField()
    {
        $value = $this->getInputValue();

        $input = '<div class="btn-group" data-toggle="buttons">' .
                 '  <label class="btn btn-info' . ($value == '2' ? ' active' : '') . '">' . HTML::radioField($this->key, '2', ($value == '2')) . $this->app->getDef('cfg_ec_incontext_button_size_small') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '1' ? ' active' : '') . '">' . HTML::radioField($this->key, '1', ($value == '1')) . $this->app->getDef('cfg_ec_incontext_button_size_tiny') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '3' ? ' active' : '') . '">' . HTML::radioField($this->key, '3', ($value == '3')) . $this->app->getDef('cfg_ec_incontext_button_size_medium') . '</label>' .
                 '</div>';

        return $input;
    }
}
