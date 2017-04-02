<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutFlow;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\MultistepDefault;

/**
 * Provides the quote multistep checkout flow.
 *
 * @CommerceCheckoutFlow(
 *   id = "multistep_order",
 *   label = "Multistep - Order",
 * )
 */
class MultistepOrder extends MultistepDefault {

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    $steps = parent::getSteps();

    $steps['payment']['label'] = $this->t('Place Order');

    return $steps;
  }

}
