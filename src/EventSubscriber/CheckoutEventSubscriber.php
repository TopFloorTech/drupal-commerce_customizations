<?php

namespace Drupal\commerce_customizations\EventSubscriber;

use Drupal\Core\Form\FormStateInterface;
use Drupal\hook_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CheckoutEventSubscriber
 *
 * @package Drupal\commerce_customizations\EventSubscriber
 */
class CheckoutEventSubscriber implements EventSubscriberInterface {

  /**
   * @param \Drupal\hook_event_dispatcher\Event\Form\FormAlterEvent $event
   */
  public function alterCheckoutForm(FormAlterEvent $event) {
    $form = $event->getForm();

    if (strpos($event->getFormId(), 'commerce_checkout_flow_') !== 0) {
      return;
    }

    if (isset($form['actions']['next'])) {
      $form['actions']['next']['#attributes']['class'][] = 'CheckoutButton-input';
      $form['actions']['next']['#prefix'] = '<span class="CheckoutButton">';
      $form['actions']['next']['#suffix'] = '</span>';
    }

    if (isset($form['shipping_information'])) {
      $form['shipping_information']['#weight'] = -10;
      $form['shipping_information']['recalculate_shipping']['#value'] = t('Show My Shipping Options');

      $form['#attached']['library'][] = 'commerce_customizations/shipping-form';
    }

    if (isset($form['payment_information'])) {
      $form['totals'] = $this->buildTotals($form);
      $form['#attached']['library'][] = 'commerce_customizations/payment-form';
    }

    if (isset($form['payment_information']['add_payment_method'])) {
      $form['payment_information']['add_payment_method']['#after_build'][] = [$this, 'processPaymentInformation'];
    }

    if (isset($form['contact_information'])) {
      $form['contact_information']['#title'] = t('Email Address');
      $form['contact_information']['#weight'] = -20;
    }

    if (isset($form['review']['contact_information'])) {
      $form['review']['contact_information']['#title'] = t('Email Address');
    }

    foreach (['shipping_information', 'billing_information'] as $fieldset) {
      if (isset($form['review'][$fieldset])) {
        $form['review'][$fieldset]['#title'] = str_replace(['(', ')'], '', $form['review'][$fieldset]['#title']);
      }
    }

    $event->setForm($form);
  }

  public function processPaymentInformation(array $element, FormStateInterface $form_state) {
    if (isset($element['payment_details'])) {
      $element['payment_details']['#weight'] = -15;
      $element['payment_details']['#sorted'] = FALSE;
      $element['payment_details']['number']['#prefix'] = $this->buildCreditCardFormIcons();
      $element['payment_details']['security_code']['#weight'] = 0.002;
      $element['payment_details']['expiration']['#weight'] = 0.003;
    }

    if (isset($element['billing_information']['address'])) {
      if ($element['billing_information']['address']['#access']) {
        // @todo Uncomment once I get it working again.
        /*$element['billing_information']['copy_from_shipping'] = [
          '#type' => 'checkbox',
          '#title' => t('My billing address is the same as my shipping address'),
          '#default_value' => TRUE,
          '#weight' => -10,
        ];

        $element['billing_information']['#states']['visible'] = [
          ':input[name="payment_information[billing_information][copy_from_shipping]"]' => [
            'checked' => FALSE,
          ],
        ];*/
      }
    }

    $form_state->setValue('copy_from_shipping', TRUE);

    return $element;
  }

  protected function buildTotals(array $form) {
    $element = [];

    if (isset($form['order_summary']['#arguments'][0])) {
      $orderId = $form['order_summary']['#arguments'][0];

      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($orderId);

      $viewBuilder = \Drupal::entityTypeManager()->getViewBuilder('commerce_order');

      if ($order) {
        $field = $viewBuilder->viewField($order->get('total_price'), [
          'label' => 'hidden',
          'type' => 'commerce_order_total_summary',
        ]);

        $element = [
          '#type' => 'container',
          '#attributes' => ['class' => ['order-total']],
          'label' => [
            '#markup' => '<div class="order-total__label">' . t('Order total') . ':</div>',
          ],
          'totals' => $field,
          '#weight' => -1,
          '#prefix' => $this->messageContent(),
        ];
      }
    }

    return $element;
  }

  protected function messageContent() {
    $template = '<div class="payment-message"><p>%s</p></div>';

    $output = [
      "All orders are prepay and add shipping. If you would like to use your shipping carrier or number, please call us. Shipping quotes are generated automatically when you enter your address and may be updated manually with the button below.",
      "<strong>Please Note:</strong> We only ship to USA addresses at this time. <strong>Orders placed AFTER 1:00 PM Eastern time are not guaranteed to ship same day and may be impacted by inventory levels.</strong> If you absolutely require faster ordering, <strong>please call us at 1-800-333-7467</strong>.",
      "<strong>We collect sales tax in the following states: CT, GA, IL and SC.</strong> If you are a tax exempt organization in these states, please call your order in otherwise you will be charged sales tax. Thank you.",
    ];

    return sprintf($template, implode('</p><p>', $output));
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
      HookEventDispatcherEvents::FORM_ALTER => [
        ['alterCheckoutForm'],
      ],
    ];
  }

}
