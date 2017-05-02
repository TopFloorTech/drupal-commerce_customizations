<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutPane;

/**
 * Provides a custom text pane for the Login step.
 *
 * @CommerceCheckoutPane(
 *   id = "custom_text_login",
 *   label = @Translation("Custom text (Login)"),
 *   default_step = "login"
 * )
 */
class LoginStepText extends TextPaneBase {}
