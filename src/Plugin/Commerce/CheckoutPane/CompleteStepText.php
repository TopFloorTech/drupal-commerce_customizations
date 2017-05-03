<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutPane;

/**
 * Provides a custom text pane for the Complete step.
 *
 * @CommerceCheckoutPane(
 *   id = "custom_text_complete",
 *   label = @Translation("Custom text (Complete)"),
 *   default_step = "complete"
 * )
 */
class CompleteStepText extends TextPaneBase {}
