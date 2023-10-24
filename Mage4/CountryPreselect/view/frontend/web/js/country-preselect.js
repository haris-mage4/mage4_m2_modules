define(['jquery'], function($){
  var countryPreselectComponent = function(config)
  {
    var userCountryCode = config.userCountryCode,
      allowedCountryCodes = [];

    var isCountySelectLoaded = setInterval(function() {
      var countrySelect;
      if ($('body').hasClass('checkout-cart-index')) {
        countrySelect = $('#shipping-zip-form select[name="country_id"]');
      }
      if ($('body').hasClass('checkout-index-index')) {
        countrySelect = $('.billing-address-form select[name="country_id"]');
      }
      if (countrySelect.length) {
        countrySelect.find('option').each(function () {
          if ($(this).val().length) {
              // alert($(this).val());
            allowedCountryCodes.push($(this).val());
          }
        });
        if (allowedCountryCodes.indexOf(userCountryCode) > -1) {
            // alert(userCountryCode);
          countrySelect.val(userCountryCode).change();
        }
        clearInterval(isCountySelectLoaded);
      }
    }, 100);
  };

  return countryPreselectComponent;
});
