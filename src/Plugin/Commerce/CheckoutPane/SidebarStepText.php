<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutPane;

/**
 * Provides a custom text pane for the Sidebar step.
 *
 * @CommerceCheckoutPane(
 *   id = "custom_text_sidebar",
 *   label = @Translation("Custom text (Sidebar)"),
 *   default_step = "sidebar"
 * )
 */
class SidebarStepText extends TextPaneBase {}
