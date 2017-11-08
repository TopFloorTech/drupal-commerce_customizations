(function ($) {
  'use strict';

  Drupal.behaviors.commerceCustomizationsJobTitle = {
    attach: function (context, settings) {
      // Move Job Title field when editing
      // Guest checkout Shipping form
      $('#edit-shipping-information-shipping-profile-field-job-title-wrapper', context)
          .detach()
          .insertAfter($('.FormItem--shipping-information-shipping-profile-address-0-address-family-name', context));
      // Guest checkout Billing Form
      $('#edit-payment-information-add-payment-method-billing-information-field-job-title-wrapper', context)
          .detach()
          .insertAfter($('.FormItem--payment-information-add-payment-method-billing-information-address-0-address-family-name', context));

      // Logged in Shipping form
      var $loggedInShipping = $('[data-drupal-selector="edit-shipping-information"]', context);
      $loggedInShipping.find($('.field--name-field-job-title', context))
          .detach()
          .insertAfter($loggedInShipping.find($('.FormItem--shipping-information-shipping-profile-address-0-address-family-name', context)));

      // Logged in Billing form
      var $loggedInBilling = $('[data-drupal-selector="edit-payment-information-add-payment-method-billing-information"]', context);
      $loggedInBilling.find($('.field--name-field-job-title', context))
          .detach()
          .insertAfter($loggedInBilling.find($('.FormItem--payment-information-add-payment-method-billing-information-address-0-address-family-name', context)));

      // Move Job Title field
      var $addressDisplayShipping = $('[data-drupal-selector="edit-shipping-information-shipping-profile-rendered-profile-0"]', context);
      $addressDisplayShipping.find($('.Field--name-field-job-title', context))
          .detach()
          .insertAfter($addressDisplayShipping.find($('.family-name', context)));

      var $addressDisplayBilling = $('[data-drupal-selector="edit-payment-information-add-payment-method-billing-information-rendered-profile-0"]', context);
      $addressDisplayBilling.find($('.Field--name-field-job-title', context))
          .detach()
          .insertAfter($addressDisplayBilling.find($('.family-name', context)));
      // Remove label
      $('.Field--name-field-job-title .Field-label', context)
          .remove();
      // Remove br tag for better formatting
      $('.Field--name-field-job-title + br', context)
          .remove();
    }
  };
}(jQuery));
