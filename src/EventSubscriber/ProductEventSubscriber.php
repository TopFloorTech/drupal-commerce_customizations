<?php

namespace Drupal\commerce_customizations\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartOrderItemUpdateEvent;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderItemEvent;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;
use Drupal\commerce_product\Event\ProductVariationEvent;
use Drupal\commerce_product\Event\ProductVariationTitleGenerateEvent;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\OrderItemComparisonFieldsEvent;

class ProductEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE][] = ['onProductVariationAjaxChange'];
    $events[ProductEvents::PRODUCT_VARIATION_TITLE_GENERATE][] = ['onProductVariationTitleGenerate'];

    return $events;
  }

  public function onProductVariationAjaxChange(ProductVariationAjaxChangeEvent $event) {
    $event
      ->getResponse()
      ->addCommand(new InvokeCommand('.ProductMediaGallery-largeImage a', 'swipebox'));
  }

  public function onProductVariationTitleGenerate(ProductVariationTitleGenerateEvent $event) {
    $title = $event->getTitle();

    if (strpos($title, ' - ') === FALSE) {
      return;
    }

    list($title, $parts) = explode(' - ', $title);

    $parts = array_filter(explode(', ', $parts), function ($part) {
      return trim($part) !== 'N/A';
    });

    if (!empty($parts)) {
      $title .= ' - ' . implode(', ', $parts);
    }

    $event->setTitle($title);

    dpm($event->getTitle());
  }
}
