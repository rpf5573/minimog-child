// 모바일에서 환율 버튼 오류 해결 - 시작
(($) => {
  document.addEventListener("DOMContentLoaded", () => {
    const $trigger = $(".currency-switcher-menu .menu-item-has-children");
    if ($trigger.length === 0) {
      console.warn("concurrency switcher 가 없습니다");
      return;
    }
    const $topBarWrap = $(".top-bar-wrap");
    const $submenu = $(".currency-switcher-menu .sub-menu");
    $trigger.on("click", () => {
      $topBarWrap.toggleClass("overflow-visible");
      $submenu.toggleClass("swicher-visible");
    });
  });
})(jQuery);
// 모바일에서 환율 버튼 오류 해결 - 끝

// 배송비 계산기
(($) => {
  // calculator
  document.addEventListener("DOMContentLoaded", () => {
    const { ajaxurl } = apmmust_ajax_obj;
    if (!ajaxurl) return;

    const $shippingCalculatorContainer = $(".shipping-calculator-container");
    if ($shippingCalculatorContainer.length === 0) return;

    const $button = $(".shipping-calculator-container .calculate-button");
    if ($button.length === 0) return;

    const $country = $('.shipping-calculator-container select[name="country"]');
    const $weight = $('.shipping-calculator-container input[name="weight"]');
    const $boxDimension = $(
      '.shipping-calculator-container select[name="box-dimension"]'
    );
    const $result = $(".shipping-calculator-container .calculate-result");

    // 뭔가 사용자가 바꾸면, 결과 수치를 가려야 한다. 안그러면 오해한다
    $country.on("change", () => {
      $result.addClass("invisible");
    });

    $weight.on("input", () => {
      $result.addClass("invisible");
    });

    $boxDimension.on("change", () => {
      $result.addClass("invisible");
    });

    $(".shipping-calculator-container input[name='shipping_type']").on(
      "change",
      () => {
        $result.addClass("invisible");
      }
    );

    $button.on("click", () => {
      $country.removeClass("error");
      $weight.removeClass("error");
      $boxDimension.removeClass("error");

      const country = $country.val();
      const weight = parseInt($weight.val());
      const boxDimension = $boxDimension.val();

      let hasError = false;
      if (!country || !weight || !boxDimension) hasError = true;

      if (!country) {
        $country.addClass("error");
      }
      if (!weight) {
        $weight.addClass("error");
      }
      if (!boxDimension) {
        $boxDimension.addClass("error");
      }

      if (weight < 0) {
        $weight.addClass("error");
      }

      const [width, height, depth] = boxDimension.split("-");
      if (!width || !height || !depth) hasError = true;

      const shippingType = $(
        '.shipping-calculator-container input[name="shipping_type"]:checked'
      ).val();

      if (!shippingType) hasError = true;

      if (hasError) {
        alert("Check the field again please");
        return;
      }

      const data = {
        action: "apmmust_calculate_shipping_fee_action",
        country,
        shipping_type: shippingType,
        weight,
        box_dimension: {
          width,
          height,
          depth,
        },
      };

      $button.attr("disabled", true);
      $.post(ajaxurl, data, (response) => {
        if (!response.success) {
          if (response.data.message) {
            alert(response.data.message);
            $button.attr("disabled", false);
            return;
          }
          alert("Unknown Error");
          $button.attr("disabled", false);
          return;
        }

        $(
          ".shipping-calculator-container .calculate-result .estimate-price span:nth-of-type(2)"
        ).text(`$${response.data.price_usd}`);
        $(
          ".shipping-calculator-container .calculate-result .estimate-date span:nth-of-type(2)"
        ).text(`${response.data.estimate_date} days`);

        $result.removeClass("invisible");
        $button.attr("disabled", false);
      });
    });
  });

  // price table
  document.addEventListener("DOMContentLoaded", () => {
    const { ajaxurl } = apmmust_ajax_obj;
    if (!ajaxurl) return;

    const $priceTableContainer = $(".shipping-fee-table-container");
    if ($priceTableContainer.length === 0) return;

    const $priceTable = $(".price-table");
    if (!$priceTable) return;

    const $country = $('.shipping-fee-table-container select[name="country"]');
    const $shippingType = $(
      ".shipping-fee-table-container input[name='shipping_type_in_price_table']"
    );

    if ($country.length === 0 || $shippingType.length === 0) return;

    const action = "apmmust_shipping_fee_table_action";
    const generateData = () => {
      const $checkedShippingType = $(
        ".shipping-fee-table-container input[name='shipping_type_in_price_table']:checked"
      );
      return {
        action,
        country: $country.val(),
        shipping_type: $checkedShippingType.val(),
      };
    };
    const handleResponse = (response) => {
      if (!response.success) {
        alert(response.data.message);
        $priceTable.addClass("invisible");
        return;
      }

      const {
        data: { table },
      } = response;
      table.forEach((price, index) => {
        $(`#price-kg-${index + 1}`).text(`$${price}`);
      });
      $priceTable.removeClass("invisible");
    };

    $country.on("change", () => {
      const data = generateData();
      $.post(ajaxurl, data, (response) => {
        handleResponse(response);
      });
    });
    $shippingType.on("change", () => {
      const data = generateData();
      $.post(ajaxurl, data, (response) => {
        handleResponse(response);
      });
    });

    // 처음에 한번은 그냥 가져온다
    const data = generateData();
    $.post(ajaxurl, data, (response) => {
      handleResponse(response);
    });
  });
})(jQuery);
