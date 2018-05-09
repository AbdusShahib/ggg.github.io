<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class prepare_order_status_id extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '0';
    public $sort_order = 400;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_prepare_order_status_id_title');
        $this->description = $this->app->getDef('cfg_ps_prepare_order_status_id_desc');

        if (!defined('OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID') || (strlen(OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID) < 1)) {
            $Qcheck = $this->app->db->get('orders_status', 'orders_status_id', ['orders_status_name' => 'Preparing [PayPal Standard]'], null, 1);

            if ($Qcheck->fetch() === false) {
                $Qstatus = $this->app->db->get('orders_status', 'max(orders_status_id) as status_id');

                $status_id = $Qstatus->valueInt('status_id') + 1;

                $languages = tep_get_languages();

                foreach ($languages as $lang) {
                    $this->app->db->save('orders_status', [
                        'orders_status_id' => $status_id,
                        'language_id' => $lang['id'],
                        'orders_status_name' => 'Preparing [PayPal Standard]',
                        'public_flag' => '0',
                        'downloads_flag' => '0'
                    ]);
                }
            } else {
                $status_id = $Qcheck->valueInt('orders_status_id');
            }
        } else {
            $status_id = OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID;
        }

        $this->default = $status_id;
    }

    public function getInputField()
    {
        $statuses_array = [
            [
                'id' => '0',
                'text' => $this->app->getDef('cfg_ps_prepare_order_status_id_default')
            ]
        ];

        $Qstatuses = $this->app->db->get('orders_status', [
            'orders_status_id',
            'orders_status_name'
        ], [
            'language_id' => $this->app->lang->getId()
        ], 'orders_status_name');

        while ($Qstatuses->fetch()) {
            $statuses_array[] = [
                'id' => $Qstatuses->valueInt('orders_status_id'),
                'text' => $Qstatuses->value('orders_status_name')
            ];
        }

        $input = HTML::selectField($this->key, $statuses_array, $this->getInputValue());

        return $input;
    }
}
