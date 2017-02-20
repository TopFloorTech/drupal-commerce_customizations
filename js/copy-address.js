(function ($) {
  'use strict';

  function copyField(field, paymentInfo) {
    var name = field.attr('name');

    if (!name || !name.startsWith('shipping_information[shipping_profile]')) {
      return;
    }

    name = name.replace('shipping_information[shipping_profile]', 'payment_information[add_payment_method][billing_information]');

    $('input:text[name="' + name + '"], select[name="' + name + '"]', paymentInfo).val(field.val()).blur();
  }

  function copyModifiedFields(checkbox, paymentInfo, shippingInfo) {
    if (paymentInfo.length > 0) {
      $('input:text, select', shippingInfo).on('change', function () {
        if (checkbox.is(':checked')) {
          copyField($(this), paymentInfo);
        }
      });
    }
  }

  function setupCheckboxHandler(checkbox, paymentInfo, shippingInfo) {
    $(':input[name="payment_information[add_payment_method][copy_from_shipping]"]').on('change', function () {
      if (checkbox.is(':checked')) {
        $('input:text, select', shippingInfo).each(function () {
          copyField($(this), paymentInfo);
        });
      }
    });
  }

  Drupal.behaviors.commerceCustomizationsCopyAddress = {
    attach: function (context, settings) {
      var checkbox = $(':input[name="payment_information[add_payment_method][copy_from_shipping]"]');
      var paymentInfo = $('.Form-wrapper[data-drupal-selector="edit-payment-information-add-payment-method-billing-information"]', context);
      var shippingInfo = $('.Form-wrapper[data-drupal-selector="edit-shipping-information-shipping-profile"]');

      setupCheckboxHandler(checkbox, paymentInfo, shippingInfo);
      copyModifiedFields(checkbox, paymentInfo, shippingInfo);
    }
  };
}(jQuery));
