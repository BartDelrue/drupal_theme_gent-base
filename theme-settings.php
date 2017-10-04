<?php

/**
 * @file
 * Theme settings of the Gent Base theme.
 */

 use Drupal\Core\Form\FormStateInterface;

 /**
  * Implements hook_form_system_theme_settings_alter().
  */
  function gent_base_form_system_theme_settings_alter(&$form, FormStateInterface $form_state, $form_id = NULL) {
    // Work-around for a core bug affecting admin themes. See issue #943212.
    if (isset($form_id)) {
      return;
    }

    $form['gent_base'] = [
      '#type' => 'details',
      '#title' => t('Gent Base settings'),
      '#description' => t('All settings defined by the Gent Base styleguide.'),
      '#open' => TRUE,
    ];
  }
