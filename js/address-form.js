(function ($) {
  'use strict';

  var jobTitleFieldClasses = [
    '.field--name-field-job-title',
    '.Field--name-field-job-title'
  ];

  var companyClasses = [
    '.form-item-address-0-address-organization',
    '.form-item-shipping-information-shipping-profile-address-0-address-organization',
    '.form-item-payment-information-add-payment-method-billing-information-address-0-address-organization'
  ];

  Drupal.behaviors.commerceCustomizationsJobTitle = {
    attach: function (context, settings) {
      $(jobTitleFieldClasses.join(', '), context).each(function () {
        var parent = $(this).parent();

        $(this)
          .detach()
          .insertBefore($(companyClasses.join(', '), parent));
      });

      $('.Field--name-field-job-title .Field-label', context).remove();
      $('.Field--name-field-job-title + br', context).remove();
    }
  };
}(jQuery));
