<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an 'Order: Contains product' condition.
 *
 * @CommerceCondition(
 *   id = "commerce_promotion_order_contains_product",
 *   label = @Translation("Contains product"),
 *   display_label = @Translation("Limit by certain product(s) in order"),
 *   category = @Translation("Product"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderContainsProduct extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'included_products' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $products = $this->configuration['included_products'];
    if (is_array($products) && !empty($products)) {
      $products = \Drupal::entityTypeManager()
        ->getStorage('commerce_product')
        ->loadMultiple($products);
    }

    $form['included_products'] = [
      '#title' => $this->t('Included products'),
      '#description' => $this->t('The condition will match if any of the specified products are in the order.'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'commerce_product',
      '#default_value' => $products,
      '#tags' => TRUE,
      '#maxlength' => 3000,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);

    $product_ids = [];
    foreach ($values['included_products'] as $product) {
      $product_ids[] = $product['target_id'];
    }

    $this->configuration['included_products'] = $product_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $products = $this->configuration['included_products'];
    /** @var OrderInterface $order */
    $order = $entity;

    $match = FALSE;

    foreach ($order->getItems() as $orderItem) {
      $variation = $orderItem->getPurchasedEntity();

      if ($variation instanceof ProductVariationInterface) {
        if (in_array($variation->getProductId(), $products) !== FALSE) {
          $match = TRUE;
          break;
        }
      }
    }

    return $match;
  }

}
