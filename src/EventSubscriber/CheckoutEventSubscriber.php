<?php

namespace Drupal\commerce_customizations\EventSubscriber;

use Drupal\Core\Form\FormStateInterface;
use Drupal\hook_event_dispatcher\Event\Form\FormIdAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CheckoutEventSubscriber
 *
 * @package Drupal\commerce_customizations\EventSubscriber
 */
class CheckoutEventSubscriber implements EventSubscriberInterface {

  /**
   * @param \Drupal\hook_event_dispatcher\Event\Form\FormIdAlterEvent $event
   */
  public function alterCheckoutForm(FormIdAlterEvent $event) {
    $form = $event->getForm();

    if (isset($form['actions']['next'])) {
      $form['actions']['next']['#attributes']['class'][] = 'CheckoutButton-input';
      $form['actions']['next']['#prefix'] = '<span class="CheckoutButton">';
      $form['actions']['next']['#suffix'] = '</span>';
    }

    if (isset($form['shipping_information']['recalculate_shipping'])) {
      $form['shipping_information']['recalculate_shipping']['#value'] = t('Show My Shipping Options');
    }

    if (isset($form['payment_information']['add_payment_method'])) {
      $form['payment_information']['add_payment_method']['copy_from_shipping'] = [
        '#type' => 'checkbox',
        '#title' => t('My billing address is the same as my shipping address.'),
        '#default_value' => TRUE,
        '#weight' => -10,
      ];

      $form['payment_information']['add_payment_method']['#after_build'][] = [$this, 'processPaymentInformation'];
    }

    $event->setForm($form);
  }

  public function processPaymentInformation(array $element, FormStateInterface $form_state) {
    /*if (isset($element['#type'])) {
      $info = \Drupal::service('element_info')->getInfo($element['#type']);

      if (isset($info['#process'])) {
        foreach ($info['#process'] as $process) {
          $element = $process($element, $form_state, $form);
        }
      }
    }*/

    if (isset($element['payment_details'])) {
      $element['payment_details']['#weight'] = -15;

      $element['payment_details']['#sorted'] = FALSE;
      $element['payment_details']['number']['#prefix'] = $this->buildCreditCardFormIcons();

      $element['payment_details']['security_code']['#weight'] = 0.002;
      $element['payment_details']['expiration']['#weight'] = 0.003;
    }

    if (isset($element['billing_information'])) {
      $element['billing_information']['#states']['visible'] = [
        ':input[name="payment_information[add_payment_method][copy_from_shipping]"]' => [
          'checked' => FALSE,
        ],
      ];
    }

    return $element;
  }

  protected function buildCreditCardFormIcons() {
    $element = [
      '#type' => 'container',
      '#attributes' => ['class' => ['credit-card-form__icons']],
      'children' => []
    ];

    foreach (['visa', 'mastercard', 'discover', 'amex'] as $card) {
      $classes = [
        'credit-card-form__icon',
        'credit-card-form__icon--' . $card,
        'fa',
        'fa-cc-' . $card,
      ];

      $element['children'][] = [
        '#markup' => '<i class="' . implode(" ", $classes) . '"></i>',
      ];
    }

    return render($element);
  }

  /**
   * @inheritdoc
   */
  static function getSubscribedEvents() {
    return [
      'hook_event_dispatcher.form_multistep_default.alter' => [
        ['alterCheckoutForm'],
      ],
    ];
  }

}
