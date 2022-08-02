<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\PromotionOffer;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\OrderPromotionOfferBase;
use Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\PercentageOffTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an 'Order: Percentage off' condition.
 *
 * @CommercePromotionOffer(
 *   id = "commerce_promotion_shipping_percentage_off",
 *   label = @Translation("Percentage amount off of the shipping total"),
 *   entity_type = "commerce_order",
 * )
 */
class ShippingPercentageOff extends OrderPromotionOfferBase {

  use PercentageOffTrait;

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, PromotionInterface $promotion) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $shipping_total = $this->getShippingTotal($order);
    $adjustment_amount = $shipping_total->multiply((string) $this->getPercentage());
    $adjustment_amount = $this->rounder->round($adjustment_amount);

    $order->addAdjustment(new Adjustment([
      'type' => 'promotion',
      'label' => t('Shipping Discount'),
      'amount' => $adjustment_amount->multiply('-1'),
      'percentage' => $this->getPercentage(),
      'source_id' => $promotion->id(),
    ]));
  }

  protected function getShippingTotal(OrderInterface $order) {
    $shipping_total = new Price('0.00', 'USD');
    if ($order->hasField('shipments') && !$order->get('shipments')->isEmpty()) {
      /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $shipments */
      $shipments = $order->get('shipments');

      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      foreach ($shipments->referencedEntities() as $shipment) {
        $amount = $shipment->getAmount();

        if ($amount instanceof Price) {
          $shipping_total = $shipping_total->add($amount);
        }
      }
    }

    return $shipping_total;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    if (empty($values['percentage']) && $values['percentage'] != '0') {
      $form_state->setError($form, $this->t('Percentage amount cannot be empty.'));
    }
  }

}
