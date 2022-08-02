<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_price\Price;

/**
 * Provides the total price condition for orders.
 *
 * @CommerceCondition(
 *   id = "order_total_price_range",
 *   label = @Translation("Total price (range)"),
 *   display_label = @Translation("Limit by total price (range)"),
 *   category = @Translation("Order"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderTotalPriceRange extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'minimum_operator' => '>',
      'minimum_amount' => NULL,
      'maximum_operator' => '<=',
      'maximum_amount' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $minimum_amount = $this->configuration['minimum_amount'];
    // An #ajax bug can cause $amount to be incomplete.
    if (isset($minimum_amount) && !isset($minimum_amount['number'], $minimum_amount['currency_code'])) {
      $minimum_amount = NULL;
    }

    $maximum_amount = $this->configuration['maximum_amount'];
    // An #ajax bug can cause $amount to be incomplete.
    if (isset($maximum_amount) && !isset($maximum_amount['number'], $maximum_amount['currency_code'])) {
      $maximum_amount = NULL;
    }

    $form['minimum_operator'] = [
      '#type' => 'select',
      '#title' => t('Minimum operator'),
      '#options' => $this->getMinimumComparisonOperators(),
      '#default_value' => $this->configuration['minimum_operator'],
      '#required' => TRUE,
    ];
    $form['minimum_amount'] = [
      '#type' => 'commerce_price',
      '#title' => t('Minimum amount'),
      '#default_value' => $minimum_amount,
      '#required' => TRUE,
    ];

    $form['maximum_operator'] = [
      '#type' => 'select',
      '#title' => t('Maximum operator'),
      '#options' => $this->getMaximumComparisonOperators(),
      '#default_value' => $this->configuration['maximum_operator'],
      '#required' => TRUE,
    ];
    $form['maximum_amount'] = [
      '#type' => 'commerce_price',
      '#title' => t('Maximum amount'),
      '#default_value' => $maximum_amount,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['minimum_operator'] = $values['minimum_operator'];
    $this->configuration['maximum_operator'] = $values['maximum_operator'];
    $this->configuration['minimum_amount'] = $values['minimum_amount'];
    $this->configuration['maximum_amount'] = $values['maximum_amount'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $total_price = $order->getTotalPrice();

    $matches = TRUE;
    foreach (['minimum', 'maximum'] as $type) {
      $condition_price = new Price($this->configuration[$type . '_amount']['number'], $this->configuration[$type . '_amount']['currency_code']);
      if ($total_price->getCurrencyCode() != $condition_price->getCurrencyCode()) {
        $matches = FALSE;
      } else {
        switch ($this->configuration[$type . '_operator']) {
          case '>=':
            $matches = $total_price->greaterThanOrEqual($condition_price);
            break;
          case '>':
            $matches = $total_price->greaterThan($condition_price);
            break;
          case '<=':
            $matches = $total_price->lessThanOrEqual($condition_price);
            break;
          case '<':
            $matches = $total_price->lessThan($condition_price);
            break;
          case '==':
            $matches = $total_price->equals($condition_price);
            break;
          default:
            throw new \InvalidArgumentException("Invalid operator {$this->configuration['operator']}");
        }
      }

      if (!$matches) {
        break;
      }
    }

    return $matches;
  }

  protected function getMaximumComparisonOperators() {
    return [
      '<=' => $this->t('Less than or equal to'),
      '<' => $this->t('Less than'),
    ];
  }

  protected function getMinimumComparisonOperators() {
    return [
      '>' => $this->t('Greater than'),
      '>=' => $this->t('Greater than or equal to'),
    ];
  }
}
