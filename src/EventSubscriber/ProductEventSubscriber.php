<?php

namespace Drupal\commerce_customizations\EventSubscriber;

use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;
use Drupal\commerce_product\Event\ProductVariationEvent;
use Drupal\commerce_product\Event\ProductVariationTitleGenerateEvent;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE][] = ['onProductVariationAjaxChange'];
    $events[ProductEvents::PRODUCT_VARIATION_TITLE_GENERATE][] = ['onProductVariationTitleGenerate'];
    $events[ProductEvents::PRODUCT_VARIATION_PRESAVE][] = ['onProductVariationPresave'];

    return $events;
  }

  public function onProductVariationAjaxChange(ProductVariationAjaxChangeEvent $event) {
    $classCommand = new InvokeCommand('.ProductMediaGallery-largeImage a', 'addClass', ['js-swipebox']);
    $swipeboxCommand = new InvokeCommand('.js-swipebox', 'swipebox');

    $event
      ->getResponse()
      ->addCommand($classCommand)
      ->addCommand($swipeboxCommand);
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

  public function onProductVariationPresave(ProductVariationEvent $event) {
    $variation = $event->getProductVariation();

    if ($variation->hasField('field_suggestion_terms')) {
      $values = [];

      foreach ($this->getSkuVariations($variation->getSku()) as $value) {
        $values[] = ['value' => $value];
      }

      foreach ($this->getTitleVariations($variation->getTitle()) as $value) {
        $values[] = ['value' => $value];
      }

      $product = $variation->getProduct();
      if ($product) {
        $items = $product->get('field_product_category')->getValue();
        foreach ($items as $item) {
          $term = Term::load($item['target_id']);
          $values[] = ['value' => $term->label()];
        }
      }

      $variation->get('field_suggestion_terms')->setValue($values);
    }
  }

  private function getSkuVariations($sku) {
    $values = [];

    $values[] = $sku;

    $count = strlen($sku);
    $i = 0;
    $currentSku = '';

    while ($i < $count) {
      $char = $sku[$i];
      $currentSku .= $char;

      if ($currentSku !== $sku && ctype_digit($char)) {
        $values[] = $currentSku;
      }

      $i++;
    }

    if (strpos($sku, '-') !== FALSE) {
      $values[] = str_replace('-', '', $sku);
    }

    return $values;
  }

  private function getTitleVariations($title) {
    $values = [];

    $values[] = $title;

    $words = preg_split('/\s+/', $title);
    $phrases = [];

    if (is_array($words)) {
      array_shift($words);

      foreach ($words as $word) {
        foreach ($phrases as $index => $phrase) {
          $phrases[$index] .= ' ' . $word;
        }

        $phrases[] = $word;
      }
    }

    foreach ($phrases as $phrase) {
      $values[] = $phrase;
    }

    return $values;
  }

}
