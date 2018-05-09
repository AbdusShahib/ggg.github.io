<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\DP\Params;

use OSC\OM\HTML;

class cards extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = 'visa;mastercard;discover;amex;maestro';
    public $sort_order = 200;

    protected $cards = [
        'visa' => 'Visa',
        'mastercard' => 'MasterCard',
        'discover' => 'Discover Card',
        'amex' => 'American Express',
        'maestro' => 'Maestro'
    ];

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_dp_cards_title');
        $this->description = $this->app->getDef('cfg_dp_cards_desc');
    }

    public function getInputField()
    {
        $active = explode(';', $this->getInputValue());

        $input = '';

        foreach ($this->cards as $key => $value) {
            $input .= '<div class="checkbox">' .
                      '  <label>' . HTML::checkboxField($this->key . '_cb', $key, in_array($key, $active)) . $value . '</label>' .
                      '</div>';
        }

        $input .= HTML::hiddenField($this->key);

        $result = <<<EOT
<div id="cardsSelection">
  {$input}
</div>

<script>
$(function() {
  $('#cardsSelection input').closest('form').submit(function() {
    $('#cardsSelection input[name="{$this->key}"]').val($('input[name="{$this->key}_cb"]:checked').map(function() {
      return this.value;
    }).get().join(';'));
  });
});
</script>
EOT;

        return $result;
    }
}
