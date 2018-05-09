<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

use OSC\OM\HTML;

class content_width extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = 'Full';
    public $app_configured = false;
    public $set_func = 'tep_cfg_select_option(array(\'Full\', \'Half\'), ';

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_content_width_title');
        $this->description = $this->app->getDef('cfg_login_content_width_desc');
    }

    public function getInputField()
    {
        $value = $this->getInputValue();

        $input = '<div class="btn-group" data-toggle="buttons">' .
                 '  <label class="btn btn-info' . ($value == 'Half' ? ' active' : '') . '">' . HTML::radioField($this->key, 'Half', ($value == 'Half')) . $this->app->getDef('cfg_login_content_width_half') . '</label>' .
                 '  <label class="btn btn-info' . ($value == 'Full' ? ' active' : '') . '">' . HTML::radioField($this->key, 'Full', ($value == 'Full')) . $this->app->getDef('cfg_login_content_width_full') . '</label>' .
                 '</div>';

        return $input;
    }
}
