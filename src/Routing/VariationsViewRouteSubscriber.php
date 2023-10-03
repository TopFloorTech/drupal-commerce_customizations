<?php

namespace Drupal\commerce_customizations\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Contains VariationsViewRouteSubscriber class.
 *
 * @package Drupal\commerce_customizations\Routing
 * Sets the Variation view display to an admin route.
 */
class VariationsViewRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $admin_routes = ['view.product_variations.page_product_variations'];
    foreach ($collection->all() as $name => $route) {
      if (in_array($name, $admin_routes)) {
        $route->setOption('_admin_route', TRUE);
      }
    }
  }
}
