<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a base class for custom text panes controlled via WYSIWYG field.
 */
abstract class TextPaneBase extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'text' => '',
        'format' => 'basic_html',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $text = $this->getText();

    $summary = $this->t('Text: @text', [
      '@text' => $renderer->render($text),
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['text'] = [
      '#type' => 'text_format',
      '#title' => $this->t(''),
      '#format' => $this->configuration['format'],
      '#default_value' => $this->configuration['text'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['text'] = $values['text']['value'];
      $this->configuration['format'] = $values['text']['format'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form[$this->getId()] = $this->getText();

    return $pane_form;
  }

  /**
   * Gets a render array for this pane's text.
   */
  protected function getText() {
    return [
      '#type' => 'processed_text',
      '#text' => $this->configuration['text'],
      '#format' => $this->configuration['format'],
    ];
  }
}
