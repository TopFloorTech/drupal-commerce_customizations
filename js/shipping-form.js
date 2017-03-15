(function ($) {
  'use strict';

  Drupal.behaviors.shippingForm = {
    attach: function (context, settings) {
      $('input[name="shipping_information[shipments][0][shipping_method][0]"]', context)
        .trigger('click');
    }
  };
}(jQuery));
