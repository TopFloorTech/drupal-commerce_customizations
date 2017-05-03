<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutPane;

/**
 * Provides a custom text pane for the Place Order step.
 *
 * @CommerceCheckoutPane(
 *   id = "custom_text_place_order",
 *   label = @Translation("Custom text (Place Order)"),
 *   default_step = "payment"
 * )
 */
class PlaceOrderStepText extends TextPaneBase {}
