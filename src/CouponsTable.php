<?php

namespace Drupal\commerce_customizations;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;

class CouponsTable {
  /**
   * Adjustments table builder.
   *
   * @param OrderInterface $order
   *   The order.
   *
   * @return array Render array.
   *   Render array.
   */
  public static function build(OrderInterface $order) {
    $table = [
      '#type' => 'table',
      '#header' => [t('Label'), t('Amount')],
      '#empty' => t('There are no special offers applied.'),
    ];

    $adjustments = $order->getAdjustments();
    foreach ($order->getItems() as $orderItem) {
      if ($item_adjustments = $orderItem->getAdjustments()) {
        $adjustments = array_merge($adjustments, $item_adjustments);
      }
    }
    $promotion_ids = array_map(function (Adjustment $adjustment) {
      return $adjustment->getSourceId();
    }, $adjustments);

    /** @var \Drupal\commerce_promotion\Entity\CouponInterface[] $coupons */
    $coupons = $order->get('coupons')->referencedEntities();
    if (empty($coupons) || empty($adjustments)) {
      return $table;
    }

    // Use special format for promotion with coupon.
    /** @var \Drupal\commerce_promotion\Entity\CouponInterface $coupon */
    foreach ($coupons as $index => $coupon) {
      $adjustment_index = array_search($coupon->getPromotion()->id(), $promotion_ids);
      $adjustment = $adjustments[$adjustment_index];

      $label = t(':title (code: :code)', [
        ':title' => $coupon->getPromotion()->getName(),
        ':code' => $coupon->get('code')->value,
      ]);
      $table[$index]['label'] = [
        '#type' => 'inline_template',
        '#template' => '{{ label }}',
        '#context' => [
          'label' => $label,
        ],
      ];
      $table[$index]['amount'] = [
        '#type' => 'inline_template',
        '#template' => '{{ price|commerce_price_format }}',
        '#context' => [
          'price' => $adjustment->getAmount(),
        ],
      ];
    }
    return $table;
  }
}
