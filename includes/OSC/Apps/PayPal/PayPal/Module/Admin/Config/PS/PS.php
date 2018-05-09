<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS;

use OSC\OM\OSCOM;

class PS extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigAbstract
{
    protected $pm_code = 'paypal_standard';

    public $is_uninstallable = true;
    public $is_migratable = true;
    public $sort_order = 400;

    protected function init()
    {
        $this->title = $this->app->getDef('module_ps_title');
        $this->short_title = $this->app->getDef('module_ps_short_title');
        $this->introduction = $this->app->getDef('module_ps_introduction');

        $this->is_installed = defined('OSCOM_APP_PAYPAL_PS_STATUS') && (trim(OSCOM_APP_PAYPAL_PS_STATUS) != '');

        if (!function_exists('curl_init')) {
            $this->req_notes[] = $this->app->getDef('module_ps_error_curl');
        }

        if (!$this->app->hasCredentials('PS', 'email')) {
            $this->req_notes[] = $this->app->getDef('module_ps_error_credentials');
        }
    }

    public function install()
    {
        parent::install();

        $installed = explode(';', MODULE_PAYMENT_INSTALLED);
        $installed[] = $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code;

        $this->app->saveCfgParam('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
    }

    public function uninstall()
    {
        parent::uninstall();

        $installed = explode(';', MODULE_PAYMENT_INSTALLED);
        $installed_pos = array_search($this->app->vendor . '\\' . $this->app->code . '\\' . $this->code, $installed);

        if ($installed_pos !== false) {
            unset($installed[$installed_pos]);

            $this->app->saveCfgParam('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
        }
    }

    public function canMigrate()
    {
        $class = $this->pm_code;

        if (is_file(OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/payment/' . $class . '.php')) {
            if (!class_exists($class)) {
                include(OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/payment/' . $class . '.php');
            }

            $module = new $class();

            if (isset($module->signature)) {
                $sig = explode('|', $module->signature);

                if (isset($sig[0]) && ($sig[0] == 'paypal') && isset($sig[1]) && ($sig[1] == $class) && isset($sig[2])) {
                    return version_compare($sig[2], 4) >= 0;
                }
            }
        }

        return false;
    }

    public function migrate()
    {
        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER')) {
            $server = (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

            if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_ID')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_ID)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL'))) {
                        $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL', MODULE_PAYMENT_PAYPAL_STANDARD_ID);
                    }
                }

                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_ID');
            }

            if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY'))) {
                        $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY', MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID);
                    }
                }

                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_PRIMARY_ID');
            }
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_PAGE_STYLE', MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_PAGE_STYLE');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTION_METHOD');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_PREPARE_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_ZONE')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_ZONE', MODULE_PAYMENT_PAYPAL_STANDARD_ZONE);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_ZONE');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_SORT_ORDER', MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_SORT_ORDER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTIONS_ORDER_STATUS_ID')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_TRANSACTIONS_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS')) {
            $status = '-1';

            if ((MODULE_PAYMENT_PAYPAL_STANDARD_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER')) {
                if (MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER == 'Live') {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }

            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_STATUS', $status);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_STATUS');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_GATEWAY_SERVER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_VERIFY_SSL')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_VERIFY_SSL');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_PROXY')) {
            if (!empty(MODULE_PAYMENT_PAYPAL_STANDARD_PROXY) && empty(OSCOM_HTTP_PROXY)) {
                $this->app->saveCfgParam('OSCOM_HTTP_PROXY', MODULE_PAYMENT_PAYPAL_STANDARD_PROXY);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_PROXY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_DEBUG_EMAIL');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS')) {
            if (!defined('OSCOM_APP_PAYPAL_PS_EWP_STATUS')) {
                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_EWP_STATUS', (MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS == 'True') ? '1' : '-1');
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_STATUS');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY')) {
            if (!defined('OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY')) {
                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PRIVATE_KEY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY')) {
            if (!defined('OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT')) {
                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PUBLIC_KEY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID')) {
            if (!defined('OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID')) {
                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_CERT_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY')) {
            if (!defined('OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT')) {
                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_PAYPAL_KEY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY')) {
            if (!defined('OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY')) {
                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_WORKING_DIRECTORY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL')) {
            if (!defined('OSCOM_APP_PAYPAL_PS_EWP_OPENSSL')) {
                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PS_EWP_OPENSSL', MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_STANDARD_EWP_OPENSSL');
        }
    }
}
