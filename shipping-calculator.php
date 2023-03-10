<?php
$woo_countries = WC()->countries->get_countries();
$shipping_fields = apmmust_get_shipping_fields();

if (empty($shipping_fields)) { ?>
<div class="woocommerce-NoticeGroup">
  <ul class="woocommerce-error" role="alert">
    <li>배송비가 설정되지 않았습니다. 관리자에게 문의해주세요</li>
  </ul>
</div> <?php
} else {

$box_dimensions = apmmust_get_shipping_box_dimensions();
$countries = array_unique(array_reduce($shipping_fields, function ($acc, $cur) {
  if (!$acc) $acc = [];
  $acc[] = $cur['country'];
  return $acc;
}));
?>

<div class="shipping-calculator-container">
  <h3><?php echo __("Calculate your estimated shipping fee", "apmmust"); ?></h3>
  <ul>
    <li class="field country-field">
      <div class="left">
        <label><?php echo __("Country", "apmmust") ?></label>
      </div>
      <div class="right">
        <p>
          <select name="country"> <?php
            foreach ($countries as $country) {
              $full_name = $woo_countries[strtoupper($country)]; ?>
              <option value="<?php echo $country; ?>"><?php echo $full_name; ?></option> <?php
            } ?>
          </select>
        </p>
      </div>
    </li>
    <li class="field shipping-type">
      <div class="left">
        <label><?php echo __("Shipping method", "apmmust") ?></label>
      </div>
      <div class="right">
        <p>
          <input type="radio" name="shipping_type" id="shipping_type_esm" value="ems" checked>
          <label for="shipping_type_esm">EMS</label>
          <input type="radio" name="shipping_type" id="shipping_type_ups" value="ups">
          <label for="shipping_type_ups">UPS</label>
        </p>
      </div>
    </li>
    <li class="field weight-field">
      <div class="left">
        <label><?php echo __("Weight", "apmmust") ?> <span>(kg)</span></label>
      </div>
      <div class="right">
        <p>
          <input type="number" name="weight" placeholder="0" />
        </p>
      </div>
    </li>
    <li class="field box-dimension-field">
      <div class="left">
        <label>Box dimension</label>
      </div>
      <div class="right">
        <p>
          <select name="box-dimension"> <?php
            foreach ($box_dimensions as $dimension) { 
              $val = $dimension['width'] . '-' . $dimension['height'] . '-' . $dimension['depth'];
              $full_name = $dimension['width'] . 'cm x ' . $dimension['height'] . 'cm x ' . $dimension['depth'] . 'cm'; ?>
              <option value="<?php echo $val; ?>"><?php echo $full_name; ?></option> <?php
            } ?>
          </select>
        </p>
      </div>
    </li>
  </ul>
  <div class="calculate-result invisible">
    <div class="estimate-price">
      <span><?php echo __("Total price", "apmmust"); ?> : </span>
      <span></span>
    </div>
    <div class="estimate-date">
    <span><?php echo __("Estimate date", "apmmust"); ?> : </span>
      <span></span>
    </div>
  </div>
  <div class="calculate-container">
    <button class="calculate-button" type="button"><?php echo __("Calculate", "apmmust") ?></button>
  </div>
</div>

<div class="shipping-fee-table-container">
  <h3><?php echo __("Shipping Price Table", "apmmust"); ?> ($)</h3>
  <ul>
    <li class="field">
      <div class="left"><?php echo __("Country", "apmmust") ?></div>
      <div class="right">
        <p>
          <select name="country"> <?php
            foreach ($countries as $country) {
              $full_name = $woo_countries[strtoupper($country)]; ?>
              <option value="<?php echo $country; ?>"><?php echo $full_name; ?></option> <?php
            } ?>
          </select>
        </p>
      </div>
    </li>
    <li class="field">
      <div class="left">
        <label><?php echo __("Shipping method", "apmmust") ?></label>
      </div>
      <div class="right">
        <p>
          <input type="radio" name="shipping_type_in_price_table" id="shipping_type_esm_in_price_table" value="ems" checked>
          <label for="shipping_type_esm_in_price_table">EMS</label>
          <input type="radio" name="shipping_type_in_price_table" id="shipping_type_ups_in_price_table" value="ups">
          <label for="shipping_type_ups_in_price_table">UPS</label>
        </p>
      </div>
    </li>
  </ul>
  <table class="price-table invisible"> <?php
    for ($i = 1; $i <= 10; $i += 1) { ?>
      <tr> <?php
        for ($j = 0; $j < 3; $j += 1) { 
          $index = $i + 10 * $j - 1;
          $kg = $index + 1; ?>
          <td id="<?php echo "kg-{$kg}"; ?>" class="kg"><?php echo $kg; ?>kg</td>
          <td id="<?php echo "price-kg-{$kg}" ?>">...</td> <?php
        } ?>
      </tr> <?php
    } ?>
  </table>
</div> <?php

}
