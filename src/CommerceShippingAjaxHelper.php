<?php

namespace Drupal\commerce_customizations;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class CommerceShippingAjaxHelper {

  /**
   * The #element_validate callback.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function elementValidate(array $element, FormStateInterface $form_state) {
    /** @var OrderInterface $order */
    $order = \Drupal::routeMatch()->getParameter('commerce_order');
    if (!$order) {
      return;
    }

    $form = $form_state->getCompleteForm();

    if (self::shippingMethodHasChanged($order, $form, $form_state)) {
      self::validateAndSubmitShippingInformation($order, $form, $form_state);

      /** @var \Drupal\commerce_order\OrderRefreshInterface $order_refresh */
      $order_refresh = \Drupal::service('commerce_order.order_refresh');
      $order_refresh->refresh($order);
      $order->save();

      self::updateFormValuesFromOrder($order, $form, $form_state);
    }
  }

  public static function elementCallback(&$form, FormStateInterface $form_state) {
    return $form;
  }

  public static function updateFormValuesFromOrder(OrderInterface $order, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\profile\Entity\ProfileInterface $profile */
    $profile = NULL;

    if (!empty($order->shipments)) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      $shipment = $order->shipments[0]->entity;
      $profile = $shipment->getShippingProfile();
    }

    $form['shipping_information']['shipping_profile']['#profile'] = $profile;
  }

  public static function shippingMethodHasChanged(OrderInterface $order, array &$form, FormStateInterface $form_state) {
    $pane_form = $form['shipping_information'];

    // Save the modified shipments.
    $shipping_methods = [];

    foreach (Element::children($pane_form['shipments']) as $index) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      $shipment = $pane_form['shipments'][$index]['#shipment'];
      $shipping_methods[] = $shipment->getShippingMethodId();
    }

    $storage = $form_state->getStorage();

    $match = TRUE;

    if (isset($storage['selected_shipping_methods'])) {
      foreach ($storage['selected_shipping_methods'] as $index => $shipping_method) {
        if ($shipping_methods[$index] != $shipping_method) {
          $match = FALSE;
          break;
        }
      }
    }

    if (!$match) {
      $storage['selected_shipping_methods'] = $shipping_methods;
      $form_state->setStorage($storage);
    }

    return $match;
  }

  public static function validateAndSubmitShippingInformation(OrderInterface $order, array &$form, FormStateInterface $form_state) {
    $pane_form = $form['shipping_information'];

    /** @var \Drupal\profile\Entity\ProfileInterface $profile */
    $profile = $pane_form['shipping_profile']['#profile'];
    //$profile = $profile->createDuplicate();

//    if (!empty($order->shipments)) {
//      $removed_shipments = (isset($form['shipping_information']['removed_shipments']['#value']))
//        ? $form['shipping_information']['removed_shipments']['#value']
//        : [];
//
//      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
//      foreach ($order->shipments->referencedEntities() as $shipment) {
//        $removed_shipments[] = $shipment->id();
//      }
//
//      $form['shipping_information']['removed_shipments']['#value'] = $removed_shipments;
//    }

    // Save the modified shipments.
    $shipments = [];
    foreach (Element::children($pane_form['shipments']) as $index) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      $shipment = $pane_form['shipments'][$index]['#shipment'];
      $shipment = $shipment->createDuplicate();

      EntityFormDisplay::collectRenderDisplay($shipment, 'default')
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
