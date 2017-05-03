<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutPane;

/**
 * Provides a custom text pane for the Review step.
 *
 * @CommerceCheckoutPane(
 *   id = "custom_text_review",
 *   label = @Translation("Custom text (Review)"),
 *   default_step = "review"
 * )
 */
class ReviewStepText extends TextPaneBase {}
