<?php

namespace Drupal\commerce_customizations\Plugin\Condition;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Product discontinued' condition to enable a condition based in module selected status.
 *
 * @Condition(
 *   id = "product_discontinued",
 *   label = @Translation("Product Discontinued"),
 *   context = {
 *     "commerce_product" = @ContextDefinition("entity:commerce_product", required = FALSE, label = @Translation("Product"))
 *   }
 * )
 *
 */
class ProductDiscontinued extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Creates a new ProductDiscontinued object.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['is_discontinued'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Product is discontinued'),
      '#default_value' => $this->configuration['is_discontinued'],
      '#description' => $this->t('Only valid on product pages. The condition will evaluate based on whether the current product is discontinued.'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['is_discontinued'] = $form_state->getValue('is_discontinued');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['is_discontinued' => ''] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['is_discontinued']) && !$this->isNegated()) {
      return TRUE;
    }

    /** @var ProductInterface $product */
    $product = $this->getContextValue('commerce_product');

    if (!$product instanceof ProductInterface) {
      return TRUE;
    }

    return $this->productIsDiscontinued($product);
  }

  private function productIsDiscontinued(ProductInterface $product) {
    $field = 'field_discontinued';
    $discontinued = FALSE;

    if ($product->hasField($field) && !$product->get($field)->isEmpty()) {
      $discontinued = (bool) $product->get($field)->value;
    }

    return $discontinued;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $response = 'The product is ';
    $response .= $this->isNegated() ? 'not discontinued.' : 'discontinued.';
    return $this->t($response);
  }

}
