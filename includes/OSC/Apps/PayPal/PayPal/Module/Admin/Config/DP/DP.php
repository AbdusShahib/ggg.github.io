<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\DP;

use OSC\OM\OSCOM;

class DP extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigAbstract
{
    protected $pm_code = 'paypal_pro_dp';
    protected $pm_pf_code = 'paypal_pro_payflow_dp';

    public $is_uninstallable = true;
    public $is_migratable = true;
    public $sort_order = 200;

    protected function init()
    {
        $this->title = $this->app->getDef('module_dp_title');
        $this->short_title = $this->app->getDef('module_dp_short_title');
        $this->introduction = $this->app->getDef('module_dp_introduction');

        $this->is_installed = defined('OSCOM_APP_PAYPAL_DP_STATUS') && (trim(OSCOM_APP_PAYPAL_DP_STATUS) != '');

        if (!function_exists('curl_init')) {
            $this->req_notes[] = $this->app->getDef('module_dp_error_curl');
        }

        if (defined('OSCOM_APP_PAYPAL_GATEWAY')) {
            if ((OSCOM_APP_PAYPAL_GATEWAY == '1') && !$this->app->hasCredentials('DP')) { // PayPal
                $this->req_notes[] = $this->app->getDef('module_dp_error_credentials');
            } elseif ((OSCOM_APP_PAYPAL_GATEWAY == '0') && !$this->app->hasCredentials('DP', 'payflow')) { // Payflow
                $this->req_notes[] = $this->app->getDef('module_dp_error_credentials_payflow');
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
        return $this->doMigrationCheck($this->pm_code) || $this->doMigrationCheck($this->pm_pf_code);
    }

    public function doMigrationCheck($class)
    {
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
        $is_payflow = false;

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER')) {
            $server = (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

            if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME'))) {
                        if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD'))) {
                            if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE'))) {
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME', MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME);
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD', MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD);
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE', MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE);
                            }
                        }
                    }
                }

                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_API_USERNAME');
                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_API_PASSWORD');
                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_API_SIGNATURE');
            }
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER')) {
            $is_payflow = true;

            $server = (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

            if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER)) {
                    if (!defined('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR'))) {
                        if (!defined('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD'))) {
                            if (!defined('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER') || !tep_not_null(constant('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER'))) {
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR);
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PF_' . $server . '_USER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME);
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD);
                                $this->app->saveCfgParam('OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER);
                            }
                        }
                    }
                }

                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VENDOR');
                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_USERNAME');
                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PASSWORD');
                $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PARTNER');
            }
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_METHOD');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_METHOD');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_ZONE', MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_ZONE');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_ZONE', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_ZONE');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_SORT_ORDER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER')) {
            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_SORT_ORDER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTIONS_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTIONS_ORDER_STATUS_ID')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTIONS_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS')) {
            $status = '-1';

            if ((MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER')) {
                if (MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER == 'Live') {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }

            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_STATUS', $status);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS')) {
            $status = '-1';

            if ((MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER')) {
                if ( MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER == 'Live' ) {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }

            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_STATUS', $status);
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_STATUS');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_TRANSACTION_SERVER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_TRANSACTION_SERVER');
        }

        $cards = [
            'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_VISA',
            'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_MASTERCARD',
            'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_DISCOVER',
            'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_AMEX',
            'MODULE_PAYMENT_PAYPAL_PRO_DP_CARDTYPE_MAESTRO'
        ];

        $cards_pass = true;

        foreach ($cards as $c) {
            if (!defined($c)) {
                $cards_pass = false;
                break;
            }
        }

        if ($cards_pass === true) {
            $cards_installed = [];

            foreach ($cards as $c) {
                if (constant($c) == 'True') {
                    $cards_installed[] = strtolower(substr($c, strrpos($c, '_')+1));
                }
            }

            $this->app->saveCfgParam('OSCOM_APP_PAYPAL_DP_CARDS', implode(';', $cards_installed));
        }

        foreach ($cards as $c) {
            $this->app->deleteCfgParam($c);
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_VERIFY_SSL')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_VERIFY_SSL');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VERIFY_SSL')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_VERIFY_SSL');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY')) {
            if (!empty(MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY) && empty(OSCOM_HTTP_PROXY)) {
                $this->app->saveCfgParam('OSCOM_HTTP_PROXY', MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_PROXY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PROXY')) {
            if (!empty(MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PROXY) && empty(OSCOM_HTTP_PROXY)) {
                $this->app->saveCfgParam('OSCOM_HTTP_PROXY', MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PROXY);
            }

            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_PROXY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_DP_DEBUG_EMAIL');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DEBUG_EMAIL')) {
            $this->app->deleteCfgParam('MODULE_PAYMENT_PAYPAL_PRO_PAYFLOW_DP_DEBUG_EMAIL');
        }

        if ($is_payflow === true) {
            $installed = explode(';', MODULE_PAYMENT_INSTALLED);
            $installed_pos = array_search($this->pm_pf_code . '.php', $installed);

            if ($installed_pos !== false) {
                unset($installed[$installed_pos]);

                $this->app->saveCfgParam('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
            }
        }
    }
}
