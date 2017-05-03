<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutPane;

/**
 * Provides a custom text pane for the Order Information step.
 *
 * @CommerceCheckoutPane(
 *   id = "custom_text_order_information",
 *   label = @Translation("Custom text (Order Information)"),
 *   default_step = "order_information"
 * )
 */
class OrderInformationStepText extends TextPaneBase {}
