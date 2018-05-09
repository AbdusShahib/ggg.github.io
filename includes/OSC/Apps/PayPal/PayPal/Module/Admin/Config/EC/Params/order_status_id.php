<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\HTML;

class order_status_id extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '0';
    public $sort_order = 800;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_order_status_id_title');
        $this->description = $this->app->getDef('cfg_ec_order_status_id_desc');
    }

    public function getInputField()
    {
        $statuses_array = [
            [
                'id' => '0',
                'text' => $this->app->getDef('cfg_ec_order_status_id_default')
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
