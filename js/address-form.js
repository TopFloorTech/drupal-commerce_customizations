(function ($) {
  'use strict';

  var jobTitleFieldClasses = [
    '.field--name-field-job-title',
    '.Field--name-field-job-title'
  ];

  var phoneNumberFieldClasses = [
    '.field--name-field-phone-number',
    '.Field--name-field-phone-number'
  ];

  var companyClasses = [
    '.form-item-address-0-address-organization',
    '.form-item-shipping-information-shipping-profile-address-0-address-organization',
    '.form-item-payment-information-add-payment-method-billing-information-address-0-address-organization'
  ];

  var postalCodeClasses = [
    '.form-item-address-0-address-postal-code',
    '.form-item-shipping-information-shipping-profile-address-0-address-postal-code',
    '.form-item-payment-information-add-payment-method-billing-information-address-0-address-postal-code'
  ];

  function insertField(classes, anchorClasses, type, context) {
    $(classes.join(', '), context).each(function () {
      var field = $(this);
      var parent = field.parent();
      var anchor = $(anchorClasses.join(', '), parent);

      field.detach();

      if (type === 'before') {
        field.insertBefore(anchor);
      } else {
        field.insertAfter(anchor);
      }

      field.find('.Field-label').remove();
      field.next('br').remove();
    });
  }

  Drupal.behaviors.commerceCustomizationsJobTitle = {
    attach: function (context, settings) {
      //insertField(jobTitleFieldClasses, companyClasses, 'before', context);
      //insertField(phoneNumberFieldClasses, postalCodeClasses, 'after', context);
    }
  };
}(jQuery));
