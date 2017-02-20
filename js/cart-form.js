(function ($) {
  'use strict';

  function moveSubtotal(cartView) {
    $('.view-footer .OrderTotalSummary', cartView)
      .detach()
      .insertAfter($('.views-table', cartView));
  }

  Drupal.behaviors.commerceCustomizationsCartForm = {
    attach: function (context, settings) {
      moveSubtotal($('.view-commerce-cart-form', context));
    }
  };
}(jQuery));
