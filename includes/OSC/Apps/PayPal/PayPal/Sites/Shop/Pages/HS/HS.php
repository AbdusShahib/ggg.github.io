<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Shop\Pages\HS;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

use OSC\Apps\PayPal\PayPal\Module\Payment\HS as PaymentModuleHS;

class HS extends \OSC\OM\PagesAbstract
{
    protected $file = null;
    protected $use_site_template = false;
    protected $pm;

    protected function init()
    {
        if (!defined('OSCOM_APP_PAYPAL_HS_STATUS') || !in_array(OSCOM_APP_PAYPAL_HS_STATUS, [
            '1',
            '0'
        ])) {
            return false;
        }

        $this->pm = new PaymentModuleHS();

        $route = Registry::get('Site')->getRoute();

        switch ($route['path']) {
            case 'order&ipn&paypal&hs':
                $this->doIPN();
                break;

            case 'order&paypal&checkout&hs':
                $this->doCheckout();
                break;
        }
    }

    protected function doIPN()
    {
        $result = false;

        if (isset($_POST['txn_id']) && !empty($_POST['txn_id'])) {
            $result = $this->pm->app->getApiResult('APP', 'GetTransactionDetails', [
                'TRANSACTIONID' => $_POST['txn_id']
            ], (OSCOM_APP_PAYPAL_HS_STATUS == '1') ? 'live' : 'sandbox', true);
        }

        if (is_array($result) && isset($result['ACK']) && (($result['ACK'] == 'Success') || ($result['ACK'] == 'SuccessWithWarning'))) {
            $_SESSION['pphs_result'] = $result;

            $this->pm->verifyTransaction(true);
        }

        Registry::get('Session')->kill();
    }

    protected function doCheckout()
    {
        $error = false;

        if (!isset($_GET['key']) || !isset($_SESSION['pphs_key']) || ($_GET['key'] != $_SESSION['pphs_key']) || !isset($_SESSION['pphs_result'])) {
            $error = true;
        }

        if ($error === false) {
            if (($_SESSION['pphs_result']['ACK'] != 'Success') && ($_SESSION['pphs_result']['ACK'] != 'SuccessWithWarning')) {
                $error = true;

                $_SESSION['pphs_error_msg'] = $_SESSION['pphs_result']['L_LONGMESSAGE0'];
            }
        }

        $this->data = [
            'hosted_button_id' => (isset($_SESSION['pphs_result']['HOSTEDBUTTONID'])) ? $_SESSION['pphs_result']['HOSTEDBUTTONID'] : '',
            'is_error' => $error
        ];

        if ($error === false) {
            if (OSCOM_APP_PAYPAL_HS_STATUS == '1') {
                $this->data['form_url'] = 'https://securepayments.paypal.com/webapps/HostedSoleSolutionApp/webflow/sparta/hostedSoleSolutionProcess';
            } else {
                $this->data['form_url'] = 'https://securepayments.sandbox.paypal.com/webapps/HostedSoleSolutionApp/webflow/sparta/hostedSoleSolutionProcess';
            }
        } else {
            $this->data['form_url'] = OSCOM::link('checkout_payment.php', 'payment_error=PayPal\HS');
        }

        $this->file = 'checkout.php';
    }
}
