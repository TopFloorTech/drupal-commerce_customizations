<?php

namespace Drupal\commerce_customizations\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides Commerce Product Variations Available For Purchase action.
 *
 * @Action(
 *   id = "variation_available_for_purchase",
 *   label = @Translation("Set Available for Purchase"),
 *   type = "commerce_product_variation",
 *   confirm = TRUE,
 *   requirements = {
 *     "_permission" = "administer commerce_product"
 *   }
 * )
 */
class VariationsAvailableForPurchaseAction extends ViewsBulkOperationsActionBase implements PluginFormInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $new_value = $this->configuration['available_for_purchase'];
    if ($entity) {
      $entity->set('field_available_for_purchase', $new_value);
      $entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object->access('update', $account, $return_as_object);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['available_for_purchase'] = [
      '#type' => 'radios',
      '#title' => $this->t('Available for Purchase'),
      '#options' => [
        0 => $this->t('False'),
        1 => $this->t('True'),
      ],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['available_for_purchase'] = $form_state->getValue('available_for_purchase');
  }

}
