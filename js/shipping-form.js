(function ($) {
  'use strict';

  Drupal.behaviors.shippingForm = {
    attach: function (context, settings) {
      if (context !== document) {
        $('input[name="shipping_information[shipments][0][shipping_method][0]"]', context)
          .trigger('click');
      }
    }
  };
}(jQuery));
