<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config;

use OSC\OM\Registry;

abstract class ConfigParamAbstract extends \OSC\Sites\Admin\ConfigParamAbstract
{
    protected $app;
    protected $config_module;

    protected $key_prefix = 'oscom_app_paypal_';
    public $app_configured = true;

    public function __construct($config_module)
    {
        $this->app = Registry::get('PayPal');

        if ($config_module != 'G') {
            $this->key_prefix .= strtolower($config_module) . '_';
        }

        $this->config_module = $config_module;

        $this->code = (new \ReflectionClass($this))->getShortName();

        $this->app->loadDefinitions('modules/' . $config_module . '/Params/' . $this->code);

        parent::__construct();
    }
}
