<?php

namespace Drupal\commerce_customizations;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;

class CommerceEmailHelper {
  public static function orderItems(OrderInterface $order) {
    $items = [];

    foreach ($order->getItems() as $item) {
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
      $variation = $item->getPurchasedEntity();
      $itemId = $item->id();

      $items[$itemId] = [
        'item' => $item,
        'id' => $itemId,
        'title' => $variation->getProduct()->getTitle(),
        'url' => $variation->toUrl('canonical')->setAbsolute(TRUE)->toString(),
        'sku' => $variation->getSku(),
        'image' => self::productImageUrl($variation),
        'quantity' => $item->getQuantity(),
        'price' => $item->getTotalPrice(),
        'is_quote' => $item->get('field_quote')->value
      ];
    }

    return $items;
  }

  public static function productImageUrl(ProductVariationInterface $variation) {
    $imageField = $variation->get('field_image');

    $uri = '';

    if (!$imageField->isEmpty() && $variation->get('field_image')->entity instanceof FileInterface) {
      $entity = $variation->get('field_image')->entity;

      if ($entity instanceof FileInterface) {
        $uri = $entity->getFileUri();
      }
    }

    if (!empty($uri)) {
      $uri = ImageStyle::load('email')->buildUrl($uri);
    }

    return $uri;
  }

  public static function nodeUrl($nid, $options = []) {
    $options += ['absolute' => TRUE];

    $url = Url::fromRoute('entity.node.canonical', ['node' => $nid], $options);

    return $url->toString();
  }

}
