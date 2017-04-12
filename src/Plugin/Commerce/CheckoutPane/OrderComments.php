<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the order comments pane.
 *
 * @CommerceCheckoutPane(
 *   id = "order_comments",
 *   label = @Translation("Comments"),
 *   default_step = "review",
 *   wrapper_element = "fieldset",
 * )
 */
class OrderComments extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $comments = $this->order->get('field_comments');

    $pane_form['comments'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Comments'),
      '#default_value' => !$comments->isEmpty() ? $comments->value : '',
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $this->order->get('field_comments')->value = $values['comments'];
  }
}
