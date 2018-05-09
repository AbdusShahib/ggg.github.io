<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home;

use OSC\OM\Apps;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

use OSC\Apps\PayPal\PayPal\PayPal;

class Home extends \OSC\OM\PagesAbstract
{
    public $app;

    protected function init()
    {
        $OSCOM_PayPal = new PayPal();
        Registry::set('PayPal', $OSCOM_PayPal);

        $this->app = $OSCOM_PayPal;

        $Qcheck = $this->app->db->query('show tables like ":table_oscom_app_paypal_log"');

        if ($Qcheck->fetch() === false) {
            $sql = <<<EOD
CREATE TABLE :table_oscom_app_paypal_log (
  id int unsigned NOT NULL auto_increment,
  customers_id int NOT NULL,
  module varchar(8) NOT NULL,
  action varchar(255) NOT NULL,
  result tinyint NOT NULL,
  server tinyint NOT NULL,
  request text NOT NULL,
  response text NOT NULL,
  ip_address int unsigned,
  date_added datetime,
  PRIMARY KEY (id),
  KEY idx_oapl_module (module)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
EOD;

            $this->app->db->exec($sql);
        }

        $this->app->loadDefinitions('admin');
        $this->app->loadDefinitions('admin/start');

        if ($this->app->migrate()) {
            $admin_dashboard_modules = explode(';', MODULE_ADMIN_DASHBOARD_INSTALLED);

            foreach (Apps::getModules('adminDashboard', 'PayPal') as $k => $v) {
                if (!in_array($k, $admin_dashboard_modules)) {
                    $admin_dashboard_modules[] = $k;

                    $adm = new $v();
                    $adm->install();
                }
            }

            if (isset($adm)) {
                $this->app->db->save('configuration', [
                    'configuration_value' => implode(';', $admin_dashboard_modules)
                ], [
                    'configuration_key' => 'MODULE_ADMIN_DASHBOARD_INSTALLED'
                ]);
            }

            OSCOM::redirect('index.php', tep_get_all_get_params());
        }

        if (!$this->isActionRequest()) {
            $paypal_menu_check = [
                'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL',
                'OSCOM_APP_PAYPAL_LIVE_API_USERNAME',
                'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL',
                'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME',
                'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR'
            ];

            foreach ($paypal_menu_check as $value) {
                if (defined($value) && !empty(constant($value))) {
                    $this->runAction('Configure');
                    break;
                }
            }
        }
    }

    public function getFile2()
    {
        if (isset($this->file)) {
            var_dump(__DIR__ . '/templates/' . $this->file);
            return __DIR__ . '/templates/' . $this->file;
        }
    }
}
