(function ($) {
  'use strict';

  Drupal.behaviors.shippingForm = {
    attach: function (context, settings) {
      var $body = $('body');
      if (!$body.hasClass('js-shipping-triggered')) {
        var $method = $('input[name="shipping_information[shipments][0][shipping_method][0]"]:checked', context);

        if ($method.length > 0) {
          $method.trigger('click');
          $body.addClass('js-shipping-triggered');
        }
      }
    }
  };
}(jQuery));
