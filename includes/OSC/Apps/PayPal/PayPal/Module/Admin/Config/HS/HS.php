<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\HS;

use OSC\OM\OSCOM;

class HS extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigAbstract
{
    protected $pm_code = 'paypal_pro_hs';

    public $is_uninstallable = true;
    public $is_migratable = true;
    public $sort_order = 300;

    protected function init()
    {
        $this->title = $this->app->getDef('module_hs_title');
        $this->short_title = $this->app->getDef('module_hs_short_title');
        $this->introduction = $this->app->getDef('module_hs_introduction');

        $this->is_installed = defined('OSCOM_APP_PAYPAL_HS_STATUS') && (trim(OSCOM_APP_PAYPAL_HS_STATUS) != '');

        if (!function_exists('curl_init')) {
            $this->req_notes[] = $this->app->getDef('module_hs_error_curl');
        }

        if (defined('OSCOM_APP_PAYPAL_GATEWAY')) {
            if ((OSCOM_APP_PAYPAL_GATEWAY == '1') && !$this->app->hasCredentials('HS')) { // PayPal
                $this->req_notes[] = $this->app->getDef('module_hs_error_credentials');
            } elseif (OSCOM_APP_PAYPAL_GATEWAY == '0') { // Payflow
                $this->req_notes[] = $this->app->getDef('module_hs_error_payflow');
            }
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
        if (is_file(OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/payment/' . $this->pm_code . '.php')) {
            if (!class_exists($this->pm_code)) {
                include(OSCOM::getConfig('dir_root', 'Shop') . 'includes/modules/payment/' . $this->pm_code . '.php');
            }

            $module = new $this->pm_code();

            if (isset($module->signature)) {
                $sig = explode('|', $module->signature);

                if (isset($sig[0]) && ($sig[0] == 'paypal') && isset($sig[1]) && ($sig[1] == $this->pm_code) && isset($sig[2])) {
                    return version_compare($sig[2], 4) >= 0;
                }
            }
        }

        return false;
    }

    public function migrate()
    {
        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER')) {
            $server = (MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

            if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ID')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_ID)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL'))) {
                        $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL', MODULE_PAYMENT_PAYPAL_PRO_HS_ID);
                    }
                }

                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_ID');
            }

            if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY'))) {
                        $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY', MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID);
                    }
                }

                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID');
            }

            if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME'))) {
                        if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD'))) {
                            if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE'))) {
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME', MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME);
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD', MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD);
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE', MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE);
                            }
                        }
                    }
                }

                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME');
                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD');
                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE');
            }
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_HS_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_HS_ZONE', MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_HS_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS')) {
            $status = '-1';

            if ((MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER')) {
                if (MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Live') {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }

            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_HS_STATUS', $status);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY')) {
            if (!empty(MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY) && empty(OSCOM_HTTP_PROXY)) {
                $this->app->saveCfgParam('OSCOM_HTTP_PROXY', MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL');
        }
    }
}
