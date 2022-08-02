<?php

namespace Drupal\commerce_customizations\Plugin\Field\FieldFormatter;

use Drupal\commerce_price\Plugin\Field\FieldFormatter\PriceDefaultFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'commerce_price_custom' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_price_custom",
 *   label = @Translation("Custom"),
 *   field_types = {
 *     "commerce_price"
 *   }
 * )
 */
class PriceCustomFormatter extends PriceDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'use_grouping' => TRUE,
    ] + parent::defaultSettings();


  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['use_grouping'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include thousands separator.'),
      '#default_value' => $this->getSetting('use_grouping'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('use_grouping')) {
      $summary[] = $this->t('Use a thousands separator.');
    } else {
      $summary[] = $this->t('Do not use a thousands separator.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormattingOptions() {
    $options = parent::getFormattingOptions();
    $options['use_grouping'] = (bool) $this->getSetting('use_grouping');
    return $options;
  }

}
