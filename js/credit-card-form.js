(function ($) {
  'use strict';

  function setupCardIcons(context) {
    $('.credit-card-form', context).not('.credit-card-form--processed').each(function () {
      var $form = $(this);
      var $icons = $('.credit-card-form__icons', $form);

      $form.addClass('.credit-card-form--processed');

      var cards = {
        visa: '4',
        mastercard: '5',
        discover: '6',
        amex: '3'
      };

      var $ccNum = $('.FormItem--payment-information-add-payment-method-payment-details-number, .FormItem--payment-method-payment-details-number', $form);

      $icons.detach().prependTo($ccNum);

      $ccNum.find('.form-text').on('keyup', function () {
        var $input = $(this);

        var digit = $input.val().substr(0, 1);

        var found = false;
        $.each(cards, function (card) {
          if (digit === cards[card]) {
            $form.find('.credit-card-form__icon').not('.credit-card-form__icon--' + card).removeClass('is-active');
            $form.find('.credit-card-form__icon--' + card).addClass('is-active');
            found = true;
            return false;
          }
        });

        if (!found) {
          $icons.find('.credit-card-form__icon').removeClass('is-active');
        }
      }).trigger('keyup');
    });
  }

  Drupal.behaviors.commerceCustomizationsCreditCardForm = {
    attach: function (context, settings) {
      setupCardIcons(context);
    }
  };
}(jQuery));
