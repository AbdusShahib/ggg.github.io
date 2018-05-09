<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

use OSC\OM\HTML;

class attributes extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 700;

    protected $attributes = [
        'personal' => [
            'full_name' => 'profile',
            'date_of_birth' => 'profile',
            'age_range' => 'https://uri.paypal.com/services/paypalattributes',
            'gender' => 'profile'
        ],
        'address' => [
            'email_address' => 'email',
            'street_address' => 'address',
            'city' => 'address',
            'state' => 'address',
            'country' => 'address',
            'zip_code' => 'address',
            'phone' => 'phone'
        ],
        'account' => [
            'account_status' => 'https://uri.paypal.com/services/paypalattributes',
            'account_type' => 'https://uri.paypal.com/services/paypalattributes',
            'account_creation_date' => 'https://uri.paypal.com/services/paypalattributes',
            'time_zone' => 'profile',
            'locale' => 'profile',
            'language' => 'profile'
        ],
        'checkout' => [
            'seamless_checkout' => 'https://uri.paypal.com/services/expresscheckout'
        ]
    ];

    protected $required = [
        'full_name',
        'email_address',
        'street_address',
        'city',
        'state',
        'country',
        'zip_code'
    ];

    protected function init()
    {
        $this->default = implode(';', $this->getAttributes());

        $this->title = $this->app->getDef('cfg_login_attributes_title');
        $this->description = $this->app->getDef('cfg_login_attributes_desc');
    }

    public function getInputField()
    {
        $values_array = explode(';', $this->getInputValue());

        $input = '';

        foreach ($this->attributes as $group => $attributes) {
            $input .= '<strong>' . $this->app->getDef('cfg_login_attributes_group_' . $group) . '</strong><br />';

            foreach ($attributes as $attribute => $scope) {
                if (in_array($attribute, $this->required)) {
                    $input .= '<div class="radio">' .
                              '  <label>' . HTML::radioField('ppLogInAttributesTmp' . ucfirst($attribute), $attribute, true) . $this->app->getDef('cfg_login_attributes_attribute_' . $attribute) . '</label>' .
                              '</div>';
                } else {
                    $input .= '<div class="checkbox">' .
                              '  <label>' . HTML::checkboxField('ppLogInAttributes[]', $attribute, in_array($attribute, $values_array)) . $this->app->getDef('cfg_login_attributes_attribute_' . $attribute) . '</label>' .
                              '</div>';
                }
            }
        }

        $input .= HTML::hiddenField($this->key);

        $fieldName = $this->key;

        $result = <<<EOT
<div id="attributesSelection">
  {$input}
</div>

<script>
function ppLogInAttributesUpdateCfgValue() {
  var pp_login_attributes_selected = '';

  if ($('input[name^="ppLogInAttributesTmp"]').length > 0) {
    $('input[name^="ppLogInAttributesTmp"]').each(function() {
      pp_login_attributes_selected += $(this).attr('value') + ';';
    });
  }

  if ($('input[name="ppLogInAttributes[]"]').length > 0) {
    $('input[name="ppLogInAttributes[]"]:checked').each(function() {
      pp_login_attributes_selected += $(this).attr('value') + ';';
    });
  }

  if (pp_login_attributes_selected.length > 0) {
    pp_login_attributes_selected = pp_login_attributes_selected.substring(0, pp_login_attributes_selected.length - 1);
  }

  $('input[name="${fieldName}"]').val(pp_login_attributes_selected);
}

$(function() {
  ppLogInAttributesUpdateCfgValue();

  if ($('input[name="ppLogInAttributes[]"]').length > 0) {
    $('input[name="ppLogInAttributes[]"]').change(function() {
      ppLogInAttributesUpdateCfgValue();
    });
  }
});
</script>
EOT;

        return $result;
    }

    protected function getAttributes()
    {
        $data = [];

        foreach ($this->attributes as $group => $attributes) {
            foreach ($attributes as $attribute => $scope) {
                $data[] = $attribute;
            }
        }

        return $data;
    }
}
