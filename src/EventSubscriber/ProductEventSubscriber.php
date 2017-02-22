<?php

namespace Drupal\commerce_quote_cart\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartOrderItemUpdateEvent;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderItemEvent;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;
use Drupal\commerce_product\Event\ProductVariationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\OrderItemComparisonFieldsEvent;

class ProductEventSubscriber implements EventSubscriberInterface {

  var $fieldName = 'field_quote';

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE][] = ['onProductVariationAjaxChange'];

    return $events;
  }

  public function onProductVariationAjaxChange(ProductVariationAjaxChangeEvent $event) {
    $response = $event->getResponse();
    $commands = $response->getCommands();

    // TODO: Add command to reload product gallery JS after replacing image
  }
}
