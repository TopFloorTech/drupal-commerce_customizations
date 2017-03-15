<?php

namespace Drupal\commerce_customizations;

use Drupal\account_modal\AjaxCommand\RefreshPageCommand;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;

class CommerceShippingAjaxHelper {
  public static function getOrderSummaryCommand(OrderInterface $order) {
    /** @var \Drupal\commerce_order\OrderTotalSummary $summary */
    $summary = \Drupal::service('commerce_order.order_total_summary');

    return new ReplaceCommand(
      '.OrderTotalSummary',
      [
        '#theme' => 'commerce_order_total_summary',
        '#totals' => $summary->buildTotals($order),
      ]
    );
  }

  public static function elementCallback(&$form, FormStateInterface $form_state) {
    /** @var OrderInterface $order */
    $order = \Drupal::routeMatch()->getParameter('commerce_order');
    if (!$order) {
      return;
    }

    /** @var \Drupal\commerce_checkout\CheckoutOrderManagerInterface $checkoutOrderManager */
    $checkoutOrderManager = \Drupal::service('commerce_checkout.checkout_order_manager');

    /** @var \Drupal\commerce_checkout\CheckoutPaneManager $checkoutPaneManager */
    $checkoutPaneManager = \Drupal::service('plugin.manager.commerce_checkout_pane');

    /** @var \Drupal\commerce_shipping\Plugin\Commerce\CheckoutPane\ShippingInformation $shippingInfo */
    $shippingInfo = $checkoutPaneManager->createInstance(
      'shipping_information',
      [],
      $checkoutOrderManager->getCheckoutFlow($order)->getPlugin()
    );

    $form_state->set('shipping_profile', $form['shipping_information']['shipping_profile']['#profile']);

    $shippingInfo->submitPaneForm($form['shipping_information'], $form_state, $form);

    $order->setRefreshState(TRUE);
    // Save the order to recalculate prices.
    $order->save();

    // Refresh the order totals
    $response = new AjaxResponse();
    $response->addCommand(self::getOrderSummaryCommand($order));
    $response->addCommand(new RefreshPageCommand());

    return $response;
  }
}
