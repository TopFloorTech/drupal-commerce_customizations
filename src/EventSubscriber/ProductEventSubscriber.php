<?php

namespace Drupal\commerce_customizations\EventSubscriber;

use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;
use Drupal\commerce_product\Event\ProductVariationTitleGenerateEvent;
use Drupal\Core\Ajax\InvokeCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
    $command = new InvokeCommand('.ProductMediaGallery-largeImage a', 'swipebox');
    $event->getResponse()->addCommand($command);
  }

  public function onProductVariationTitleGenerate(ProductVariationTitleGenerateEvent $event) {
    $title = $event->getTitle();

    if (strpos($title, ' - ') === FALSE) {
      return;
    }

    [$title, $parts] = explode(' - ', $title);

    $parts = array_filter(explode(', ', $parts), function ($part) {
      return trim($part) !== 'N/A';
    });

    if (!empty($parts)) {
      $title .= ' - ' . implode(', ', $parts);
    }

    $event->setTitle($title);
  }

}
