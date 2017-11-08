(function ($) {
  'use strict';

  var jobTitleFieldClasses = [
    '.field--name-field-job-title',
    '.Field--name-field-job-title'
  ];

  var familyNameClasses = [
    'div.family-name',
    'span.family-name',
    '.form-item-shipping-information-shipping-profile-address-0-address-family-name',
    '.form-item-payment-information-add-payment-method-billing-information-address-0-address-family-name'
  ];

  Drupal.behaviors.commerceCustomizationsJobTitle = {
    attach: function (context, settings) {
      $(jobTitleFieldClasses.join(', '), context).each(function () {
        var parent = $(this).parent();

        $(this)
            .detach()
            .insertAfter($(familyNameClasses.join(', '), parent));
      });

      $('.Field--name-field-job-title .Field-label', context).remove();
      $('.Field--name-field-job-title + br', context).remove();
    }
  };
}(jQuery));
