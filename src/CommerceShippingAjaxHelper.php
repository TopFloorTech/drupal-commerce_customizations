<?php

namespace Drupal\commerce_customizations;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

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

    $order = clone $order;
    self::validateAndSubmitShippingInformation($order, $form, $form_state);

    /** @var \Drupal\commerce_order\OrderRefreshInterface $order_refresh */
    $order_refresh = \Drupal::service('commerce_order.order_refresh');
    $order_refresh->refresh($order);

    $order->preSave(\Drupal::entityTypeManager()->getStorage('commerce_order'));

    // Refresh the order totals
    $response = new AjaxResponse();
    $response->addCommand(self::getOrderSummaryCommand($order));

    return $response;
  }

  public static function validateAndSubmitShippingInformation(OrderInterface $order, array &$form, FormStateInterface $form_state) {
    $pane_form = $form['shipping_information'];

    /** @var \Drupal\profile\Entity\ProfileInterface $profile */
    $profile = $pane_form['shipping_profile']['#profile'];
    $profile = $profile->createDuplicate();

    // Save the modified shipments.
    $shipments = [];
    foreach (Element::children($pane_form['shipments']) as $index) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      $shipment = $pane_form['shipments'][$index]['#shipment'];
      $shipment = $shipment->createDuplicate();
      $form_display = EntityFormDisplay::collectRenderDisplay($shipment, 'default');
      $form_display
        ->removeComponent('shipping_profile')
        ->removeComponent('title')
        ->extractFormValues($shipment, $pane_form['shipments'][$index], $form_state);

      $shipment
        ->setShippingProfile($profile);

      $shipments[] = $shipment;
    }
    $order->shipments = $shipments;
  }

}
