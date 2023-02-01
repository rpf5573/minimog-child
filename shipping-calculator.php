<?php
$woo_countries = WC()->countries->get_countries();
$shipping_fields = apmmust_get_shipping_fields();

$box_dimensions = apmmust_get_shipping_box_dimensions();
$countries = array_unique(array_reduce($shipping_fields, function ($acc, $cur) {
  if (!$acc) $acc = [];
  $acc[] = $cur['country'];
  var_dump($acc);
  return $acc;
}));
?>

<div class="shipping-calculator-container">
  <h3>예상비용 계산하기</h3>
  <ul>
    <li class="field country-field">
      <div class="left">
        <label>배송지</label>
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
        <label>배송방법</label>
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
        <label>무게 <span>(kg)</span></label>
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
      <span>예상비용 : </span>
      <span></span>
    </div>
    <div class="estimate-date">
      <span>예상시간 : </span>
      <span></span>
    </div>
  </div>
  <div class="calculate-container">
    <button class="calculate-button" type="button">계산하기</button>
  </div>
</div>

<div class="shipping-fee-table-container">
  <h3>국가별 배송 비용 ($)</h3>
  <div class="filter-container">
    <ul>
      <li class="field">
        <div class="left">배송지</div>
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
          <label>배송방법</label>
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
  </div>
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
</div>
