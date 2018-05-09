<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\G\Params;

use OSC\OM\HTML;

class log_transactions extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '1';
    public $sort_order = 500;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_log_transactions_title');
        $this->description = $this->app->getDef('cfg_log_transactions_desc');
    }

    public function getInputField()
    {
        $value = $this->getInputValue();

        $input = '<div class="btn-group" data-toggle="buttons">' .
                 '  <label class="btn btn-info' . ($value == '1' ? ' active' : '') . '">' . HTML::radioField($this->key, '1', ($value == '1')) . $this->app->getDef('cfg_log_transactions_all') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '0' ? ' active' : '') . '">' . HTML::radioField($this->key, '0', ($value == '0')) . $this->app->getDef('cfg_log_transactions_errors') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '-1' ? ' active' : '') . '">' . HTML::radioField($this->key, '0', ($value == '-1')) . $this->app->getDef('cfg_log_transactions_disabled') . '</label>' .
                 '</div>';

        return $input;
    }
}
