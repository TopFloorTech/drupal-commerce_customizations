<?php

namespace Drupal\commerce_customizations\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductDefaultVariationEvent;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;
use Drupal\commerce_product\Event\ProductVariationEvent;
use Drupal\commerce_product\Event\ProductVariationTitleGenerateEvent;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductEventSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ProductEventSubscriber object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entity_type_manager) {
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE][] = ['onProductVariationAjaxChange'];
    $events[ProductEvents::PRODUCT_VARIATION_TITLE_GENERATE][] = ['onProductVariationTitleGenerate'];
    $events[ProductEvents::PRODUCT_VARIATION_PRESAVE][] = ['onProductVariationPresave'];
    $events[ProductEvents::PRODUCT_DEFAULT_VARIATION][] = ['onProductDefaultVariation'];

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

  /**
   * Sets the default variation.
   *
   * @param \Drupal\commerce_product\Event\ProductDefaultVariationEvent $event
   *   The product default variation event.
   */
  public function onProductDefaultVariation(ProductDefaultVariationEvent $event) {
    // Set the default variation based on the draggableviews table.
    $view = 'product_variations';
    $display = 'page_product_variations_sort';
    $product = $event->getProduct();
    $args = json_encode([$product->id()]);
    $query_string = "SELECT entity_id FROM {draggableviews_structure} WHERE view_name=:view AND view_display=:display AND weight=0 AND args=:args";
    $replacements = [
      ':view' => $view,
      ':display' => $display,
      ':args' => $args,
    ];
    $logger->notice('$query_string: ' . $query_string . '; $replacements: <pre>' . print_r($replacements, TRUE) . '</pre>');
    $query = $this->connection->query($query_string, $replacements);
    if ($query) {
      while ($row = $query->fetchAssoc()) {
        $logger->notice('$row: ' . print_r($row, TRUE) );
        $variation_id = $row['entity_id'];
        /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
        $variation = $this->entityTypeManager->getStorage('commerce_product_variation')->load($variation_id);
        if ($variation) {
          $event->setDefaultVariation($variation);
        }
      }
    }
  }

}
