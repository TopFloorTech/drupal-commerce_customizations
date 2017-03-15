<?php

namespace Drupal\commerce_customizations;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

class CommerceEmailHelper {
  public static function orderItems(OrderInterface $order) {
    $items = [];

    foreach ($order->getItems() as $item) {
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
      $variation = $item->getPurchasedEntity();

      $items[$item->id()] = [
        'item' => $item,
        'id' => $item->id(),
        'title' => $variation->getProduct()->getTitle(),
        'url' => $variation->toUrl('canonical')->setAbsolute(TRUE)->toString(),
        'sku' => $variation->getSku(),
        'image' => self::productImageUrl($variation),
        'quantity' => $item->getQuantity(),
        'price' => $item->getTotalPrice()
      ];
    }

    return $items;
  }

  public static function productImageUrl(ProductVariationInterface $variation) {
    $image = $variation->get('field_image')->entity->getFileUri();

    return ImageStyle::load('email')->buildUrl($image);
  }

  public static function nodeUrl($nid, $options = []) {
    $options += ['absolute' => TRUE];

    $url = Url::fromRoute('entity.node.canonical', ['node' => $nid], $options);

    return $url->toString();
  }
}
