<?php

namespace Drupal\commerce_customizations\EventSubscriber;

use Drupal\commerce_shipping\OrderShipmentSummaryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hook_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherEvents;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CheckoutEventSubscriber
 *
 * @package Drupal\commerce_customizations\EventSubscriber
 */
class CheckoutEventSubscriber implements EventSubscriberInterface {
  /**
   * Debug mode.
   *
   * @var boolean
   */
  protected $debug = FALSE;

  /**
   * @param \Drupal\hook_event_dispatcher\Event\Form\FormAlterEvent $event
   */
  public function alterCheckoutForm(FormAlterEvent $event) {
    $form = $event->getForm();

    if (strpos($event->getFormId(), 'commerce_checkout_flow_') !== 0) {
      return;
    }

    if (isset($form['actions']['next'])) {
      $old_suffix = isset($form['actions']['next']['#suffix']) ? $form['actions']['next']['#suffix'] : '';
      $form['actions']['next']['#attributes']['class'][] = 'CheckoutButton-input';
      $form['actions']['next']['#prefix'] = '<span class="CheckoutButton">';
      $form['actions']['next']['#suffix'] = '</span>' . $old_suffix;
    }

    if (isset($form['custom_text_order_information'])) {
      $form['custom_text_order_information']['#weight'] = -30;
    }

    if (isset($form['shipping_information'])) {
      $form['shipping_information']['#weight'] = -10;
      $form['totals'] = $this->buildShippingMessage();
      $form['shipping_information']['recalculate_shipping']['#value'] = t('Show My Shipping Options');

      if (isset($form['shipping_information']['shipping_profile'])) {
        $form['shipping_information']['shipping_profile']['#after_build'][] = [$this, 'processShippingInformation'];
      }
    }

    if (isset($form['payment_information'])) {
      $form['#attached']['library'][] = 'commerce_customizations/payment-form';
    }

    if (isset($form['payment_information']['add_payment_method'])) {
      $form['payment_information']['add_payment_method']['#after_build'][] = [$this, 'processPaymentInformation'];
    }

    if (isset($form['contact_information'])) {
      $form['contact_information']['#title'] = t('Email Address');
      $form['contact_information']['#weight'] = -20;
    }

    // @todo Remove this code once we're sure it's no longer needed
    //    if (!isset($form['review']) && isset($form['sidebar']['coupon_redemption']['coupons'])) {
    //      $form['sidebar']['coupon_redemption']['coupons'] = [
    //        '#type' => 'markup',
    //        '#markup' => '<p class="CouponMessage">' . t('Got a coupon code? You can enter it on the Review page.') . '</p>',
    //      ];
    //    }

    if (isset($form['review']['contact_information'])) {
      $form['review']['contact_information']['#title'] = t('Email Address');
    }

    if (isset($form['sidebar']['coupon_redemption'])) {
      $form['sidebar']['coupon_redemption']['#type'] = 'fieldset';
    }

    foreach (['shipping_information', 'payment_information'] as $fieldset) {
      if (isset($form['review'][$fieldset])) {
        $form['review'][$fieldset]['#title'] = str_replace(['(', ')'], '', $form['review'][$fieldset]['#title']);
      }
    }

    if ($event->getFormId() === 'commerce_checkout_flow_multistep_quote') {
      if (isset($form['review']['shipping_information']['summary'][0]['shipment'])) {
        $form['review']['shipping_information']['summary'][0]['shipment']['#access'] = FALSE;
      }
      $form['#attached']['library'][] = 'commerce_customizations/profile-form';
    }

    $form['#attached']['library'][] = 'commerce_customizations/profile-form';

    $event->setForm($form);
  }

  public function processShippingInformation(array $element, FormStateInterface $form_state) {
    // @todo Force default country here if needed

    return $element;
  }

  public function processPaymentInformation(array $element, FormStateInterface $form_state) {
    $element['#sorted'] = FALSE;

    if (isset($element['billing_information'])) {
      $element['billing_information']['#weight'] = -15;
    }

    if (isset($element['payment_details'])) {
      $element['payment_details']['#weight'] = -10;
      $element['payment_details']['#sorted'] = FALSE;
      $element['payment_details']['number']['#prefix'] = $this->buildCreditCardFormIcons();
      $element['payment_details']['security_code']['#weight'] = 0.002;
      $element['payment_details']['expiration']['#weight'] = 0.003;

      // Hide sensitive fields from Inspectlet
      foreach (['number', 'security_code', 'expiration'] as $field) {
        if (isset($element['payment_details'][$field])) {
          $element['payment_details'][$field]['#attributes']['class'][] = 'inspectletIgnore';
        }
      }
    }

    if (!empty($element['billing_information']['reuse_profile']['#value'])) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = \Drupal::routeMatch()->getParameter('commerce_order');

      /** @var OrderShipmentSummaryInterface $summary */
      $summary = \Drupal::service('commerce_shipping.order_shipment_summary');

      $element['billing_information']['shipping_profile'] = $summary->build($order);
      $element['billing_information']['shipping_profile']['#weight'] = 20;
    }

    return $element;
  }

  protected function buildShippingMessage() {
    return [
      '#type' => 'markup',
      '#markup' => $this->messageContent(),
      '#weight' => -1,
    ];
  }

  /**
   * @param array $form
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function buildTotals(array $form) {
    $element = [];

    if (isset($form['sidebar']['order_summary']['view']['#arguments'][0])) {
      $orderId = $form['sidebar']['order_summary']['view']['#arguments'][0];

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
      t('All orders are prepay and add shipping using ground service. For expedited shipping or collect service, please call us. Please Note: We only ship to US & Canada addresses at this time. Orders placed AFTER 1:00 PM Eastern time are not guaranteed to ship same day For more information please call us.'),
      '<strong>' . t('We collect sales tax in the following states: CT, GA, IL and SC.') . '</strong> ' . t('If you are a tax exempt organization in these states, please call your order in otherwise you will be charged sales tax.'),
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
      'commerce_order.place.post_transition' => [
         ['checkStockLevel'],
      ]
    ];
  }

  /**
   *
   */
  public function checkStockLevel(WorkflowTransitionEvent $event) {
    $order = $event->getEntity();
    $items = $order->getItems(); // @var \Drupal\commerce_order\Entity\OrderInterface $order

    foreach ($items as $item) {
      // @var \Drupal\commerce_product\Entity\ProductVariationInterface $product
      $product = $item->getPurchasedEntity();
      $quantity = $item->getQuantity();

      $alwaysInStockField = 'commerce_stock_always_in_stock';
      $alwaysInStock = FALSE;
      if ($product->hasField($alwaysInStockField) && !$product->get($alwaysInStockField)
          ->isEmpty()) {
        $alwaysInStock = (bool) $product->get($alwaysInStockField)->value;
      }

      if ($product->hasField('field_stock_level') && !$product->get('field_stock_level')->isEmpty()) {
        $stockServiceManager = \Drupal::service('commerce_stock.service_manager');
        $stock = intval($stockServiceManager->getStockLevel($product));
        $purchasable = $product->get('field_available_for_purchase')->value;

        if ($this->debug) {
          \Drupal::logger('Commerce Customizations')->notice('Product ' . $product->id() . ' has a stock level of ' . $stock . '.');
        }

        if ($purchasable && ($stock - $quantity) <= 0) {
          if (floatval(explode(" ", $item->getTotalPrice())[0]) != 0 && !$alwaysInStock) {
            if ($this->debug) {
              \Drupal::logger('Commerce Customizations')->notice('Product ' . $product->id() . ' is out-of-stock. Sending email.');
            }
            $this->stockEmailNotification($product);
          }
        }
      } else {
        if ($this->debug) {
          \Drupal::logger('Commerce Customizations')->notice('Product ' . $product->id() . ' does not contain field_stock_level.');
        }
      }
    }
  }

  /**
   *
   */
  private function stockEmailNotification(ProductVariationInterface $variation) {
	\Drupal::logger('commerce_customization')->notice('Sending out of stock email.');

	$sku = $variation->getSku();
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'commerce_customizations';
    $key = 'out_of_stock_alert';
	$params = [];
    $to = \Drupal::config('system.site')->get('mail');
    $params['message'] = t('Item @sku (@variation_name) is out of stock.', ['@sku' => $sku, '@variation_name' => $variation->getTitle()]);
    $params['sku'] = $sku;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    if ($result['result'] !== TRUE) {
      \Drupal::logger('commerce_customizations')
        ->error($this->t('Stock alert email failed to send for sku: @sku', ['@sku' => $sku]));
    }
  }

}
