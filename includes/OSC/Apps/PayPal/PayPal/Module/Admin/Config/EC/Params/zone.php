<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\HTML;

class zone extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '0';
    public $sort_order = 900;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_zone_title');
        $this->description = $this->app->getDef('cfg_ec_zone_desc');
    }

    public function getInputField()
    {
        $zone_class_array = [
            [
                'id' => '0',
                'text' => $this->app->getDef('cfg_ec_zone_global')
            ]
        ];

        $Qclasses = $this->app->db->get('geo_zones', [
            'geo_zone_id',
            'geo_zone_name'
        ], null, 'geo_zone_name');

        while ($Qclasses->fetch()) {
            $zone_class_array[] = [
                'id' => $Qclasses->valueInt('geo_zone_id'),
                'text' => $Qclasses->value('geo_zone_name')
            ];
        }

        $input = HTML::selectField($this->key, $zone_class_array, $this->getInputValue());

        return $input;
    }
}
