<?php
/**
 * @file
 * Template file for the gent_base base theme.
 */

define('GENT_BASE_TOP_MENU_RENDER_METHOD_EMPTY', '');
define('GENT_BASE_TOP_MENU_RENDER_METHOD_REGION', 'region');
define('GENT_BASE_TOP_MENU_RENDER_METHOD_USER_LINKS', 'user_links');

// @TODO store this in the theme registry like omega does with OmegaThemeRegistryHandler
include_once 'preprocess/block.preprocess.inc';
include_once 'preprocess/field.preprocess.inc';
include_once 'preprocess/html.preprocess.inc';
include_once 'preprocess/page.preprocess.inc';
include_once 'preprocess/maintenance-page.preprocess.inc';
include_once 'preprocess/views.preprocess.inc';
include_once 'preprocess/region.preprocess.inc';
include_once 'preprocess/entity-property.preprocess.inc';
include_once 'preprocess/gent-auth-bar.preprocess.inc';

/**
 * Implements hook_css_alter().
 */
function gent_base_css_alter(&$css) {

  // Whitelist core CSS here. The rest can be allowed via the alter hook
  $whitelist = array(
    'modules/contextual/contextual.css',
  );
  $whitelist = array_combine($whitelist, $whitelist);

  drupal_alter('gent_base_css_whitelist', $whitelist);

  foreach ($css as $key => $data) {
    if ($data['type'] == 'file') {
      // Skip css files in whitelist.
      if (in_array($data['data'], $whitelist)) {
        continue;
      }
      // Remove css files from core modules.
      $is_core = (strpos($data['data'], 'modules/') === 0);
      if ($is_core) {
        unset($css[$key]);
      }
    }
  }

  // The base theme overrides css of module 'gent_readspeaker'.
  if (module_exists('gent_readspeaker')) {
    $path = drupal_get_path('module', 'gent_readspeaker') . '/css/readspeaker.css';
    if (isset($css[$path])) {
      unset($css[$path]);
    }
  }

  // The base theme overrides css of module 'digipolis_openlayers'.
  if (module_exists('digipolis_openlayers')) {
    $files = array(
      'css/map.css',
      'plugins/behaviors/openlayers_behavior_layerswitcher_plus.css',
    );
    foreach ($files as $file) {
      $path = drupal_get_path('module', 'digipolis_openlayers') . '/' . $file;
      if (isset($css[$path])) {
        unset($css[$path]);
      }
    }
  }
}

/**
 * Implements theme_menu_local_tasks().
 *
 * @ingroup themeable
 */
function gent_base_menu_local_tasks(&$variables) {
  $output = '';
  if (!empty($variables['primary'])) {
    if (!isset($variables['primary']['#prefix'])) {
      $variables['primary']['#prefix'] = '';
    }
    $variables['primary']['#prefix'] .= '<ul class="tabs primary tabs--primary links--inline clearfix">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    if (!isset($variables['secondary']['#prefix'])) {
      $variables['secondary']['#prefix'] = '';
    }
    $variables['secondary']['#prefix'] .= '<ul class="tabs secondary tabs--secondary links--inline clearfix">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }
  return $output;
}

/**
 * Implements theme_file_formatter_table().
 *
 * Use a list instead of table to display links.
 * @ingroup themeable
 */
function gent_base_file_formatter_table($variables) {
  $rows = array();
  foreach ($variables['items'] as $delta => $item) {
    $rows[] = array(
      theme('file_link', array('file' => (object) $item)),
    );
  }
  return empty($rows) ? '' : theme('item_list', array('items' => $rows, 'hide_wrapper' => TRUE, 'attributes' => array('class' => array('link-list'))));
}

/**
 * Implements theme_file_link().
 *
 * @ingroup themeable
 */
function gent_base_file_link($variables) {
  $file = $variables['file'];

  $url = file_create_url($file->uri);
  $pathinfo = pathinfo($url);
  $extension = $pathinfo['extension'];

  // Set options as per anchor format described at
  // http://microformats.org/wiki/file-format-examples
  $options = array(
    'attributes' => array(
      'type' => $file->filemime . '; length=' . $file->filesize,
    ),
  );

  // Use the description as the link text if available.
  if (empty($file->description)) {
    $link_text = $file->filename;
  }
  else {
    $link_text = $file->description;
    $options['attributes']['title'] = check_plain($file->filename);
  }

  if (empty($extension)) {
    $extension = $file->filemime;
  }
  return l($link_text, $url, $options) . '<span class="filetype">' . format_size($file->filesize) . '</span> <span class="filetype">' . strtoupper($extension) . '</span>';
}

/**
 * Implements theme_breadcrumb().
 */
function gent_base_breadcrumb($variables) {

  $breadcrumb = $variables['breadcrumb'];

  // Provide a navigational heading to give context for breadcrumb links to
  // screen-reader users. Make the heading invisible with .element-invisible.
  $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';
  $nr_parts = count($breadcrumb);
  if ($nr_parts > 1) {
    $breadcrumb[$nr_parts - 1] = '<span>' . $breadcrumb[$nr_parts - 1] . '</span>';
    $output .= '<ul class="nav nav--breadcrumb"><li>' . implode('</li><li>', $breadcrumb) . '</li></ul>';
    return $output;
  }
  return FALSE;
}

/**
 * Implements theme_pager().
 *
 * @ingroup themeable
 */
function gent_base_pager($variables) {
  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.

  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« first')), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('last »')), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
        'class' => array('pager-first'),
        'data' => $li_first,
      );
    }
    if ($li_previous) {
      $items[] = array(
        'class' => array('pager-previous'),
        'data' => $li_previous,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
          'class' => array('pager-ellipsis'),
          'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
            'class' => array('pager-item'),
            'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
            'class' => array('pager-current'),
            'data' => '<span>' . $i . '</span>',
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
            'class' => array('pager-item'),
            'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $pager_current), 'parameters' => $parameters)),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
          'class' => array('pager-ellipsis'),
          'data' => '…',
        );
      }
    }
    // End generation.
    if ($li_next) {
      $items[] = array(
        'class' => array('pager-next'),
        'data' => $li_next,
      );
    }
    if ($li_last) {
      $items[] = array(
        'class' => array('pager-last'),
        'data' => $li_last,
      );
    }
    return '<h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list', array(
      'items' => $items,
      'attributes' => array('class' => array('pager')),
      'hide_wrapper' => TRUE,
    ));
  }
}

/**
 * Implements theme_item_list().
 *
 * @ingroup themeable
 */
function gent_base_item_list($variables) {
  $items = $variables['items'];
  $title = $variables['title'];
  $type = $variables['type'];
  $attributes = $variables['attributes'];
  $hide_wrapper = !empty($variables['hide_wrapper']);
  $output = '';

  // Only output the list container and title, if there are any list items.
  // Check to see whether the block title exists before adding a header.
  // Empty headers are not semantic and present accessibility challenges.
  if (!$hide_wrapper) {
    $output = '<div class="item-list">';
  }
  if (isset($title) && $title !== '') {
    $output .= '<h3>' . $title . '</h3>';
  }

  if (!empty($items)) {
    $output .= "<$type" . drupal_attributes($attributes) . '>';
    $num_items = count($items);
    $i = 0;
    foreach ($items as $item) {
      $attributes = array();
      $children = array();
      $data = '';
      $i++;
      if (is_array($item)) {
        foreach ($item as $key => $value) {
          if ($key == 'data') {
            $data = $value;
          }
          elseif ($key == 'children') {
            $children = $value;
          }
          else {
            $attributes[$key] = $value;
          }
        }
      }
      else {
        $data = $item;
      }
      if (count($children) > 0) {
        // Render nested list.
        $data .= theme_item_list(array('items' => $children, 'title' => NULL, 'type' => $type, 'attributes' => $attributes));
      }
      if ($i == 1) {
        $attributes['class'][] = 'first';
      }
      if ($i == $num_items) {
        $attributes['class'][] = 'last';
      }
      $output .= '<li' . drupal_attributes($attributes) . '>' . $data . "</li>\n";
    }
    $output .= "</$type>";
  }
  if (!$hide_wrapper) {
    $output .= '</div>';
  }
  return $output;
}

/**
 * Implements hook_form_FORM_ID_ater().
 */
function gent_base_form_gent_newsletter_form_alter(&$form) {
  $form['email']['#attributes']['class'][] = 'prefix--email';
  $form['submit']['#attributes']['class'] = array('btn', 'btn--medium', 'btn--alpha', 'postfix--email-submit');
}

/**
 * Implement hook_form_FORM_ID_alter();
 *
 * Add base theme specific styling.
 */
function gent_base_form_gent_wijksites_select_home_form_alter(&$form, &$form_state) {
  $form['wijksite']['#attributes']['class'][] = 'prefix--large';
  $form['wijksite']['#title_display'] = 'invisible';

  $form['submit']['#attributes']['class'] = array(
    'btn', 'btn--medium', 'btn--epsilon', 'postfix--small'
  );
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function gent_base_form_digipolis_openlayers_layerswitcher_plus_behavior_form_alter(&$form, &$form_state) {
  if (!empty($form['default_layers']['layers'])) {
    $form['default_layers']['layers'][] = array('#markup' => '<div class="line--vertical"></div>');
  }

  if (!empty($form['extra_layers']['layers'])) {
    $form['extra_layers']['layers'][] = array('#markup' => '<div class="line--vertical"></div>');
  }
}

/**
 * Implements theme_status_messages().
 *
 * @ingroup themeable
 *
 * Override the classes to be more SMACCS-like.
 */
function gent_base_status_messages($variables) {
  $display = $variables['display'];
  $output = '';

  $status_heading = array(
    'status' => t('Status message'),
    'error' => t('Error message'),
    'warning' => t('Warning message'),
  );
  foreach (drupal_get_messages($display) as $type => $messages) {
    $output .= "<div class=\"messages messages--$type\">\n";
    if (!empty($status_heading[$type])) {
      $output .= '<h2 class="element-invisible">' . $status_heading[$type] . "</h2>\n";
    }
    if (count($messages) > 1) {
      $output .= " <ul>\n";
      foreach ($messages as $message) {
        $output .= '  <li>' . $message . "</li>\n";
      }
      $output .= " </ul>\n";
    }
    else {
      $output .= $messages[0];
    }
    $output .= "</div>\n";
  }
  return $output;
}

/**
 * Implements hook_cta_email().
 *
 * @ingroup themeable
 *
 * Override the 'email' call-to-action.
 */
function gent_base_cta_email($variables) {
  $output = '<p><span>';
  $output .= '<a rel="email" href="mailto:' . $variables['email'] . '" class="article-content-action">';
  $output .= '<span class="icon icon icon-email"></span>';
  $output .= '<span class="article-content-action-title">' . $variables['text'] . '</span>';
  $output .= '<span class="article-content-action-document">' . $variables['email'] . '</span>';
  $output .= '</a>';
  $output .= '</span></p>';
  return $output;
}

/**
 * Implements hook_cta_link().
 *
 * @ingroup themeable
 *
 * Override the 'link' call-to-action.
 */
function gent_base_cta_link($variables) {
  $output = '<p><span>';
  $output .= '<a href="' . $variables['url'] . '" class="article-content-action">';
  $output .= '<span class="icon icon icon-email"></span>';
  $output .= '<span class="article-content-action-title">' . $variables['text'] . '</span>';
  $output .= '<span class="article-content-action-document">' . $variables['url'] . '</span>';
  $output .= '</a>';
  $output .= '</span></p>';
  return $output;
}

/**
 * Implements hook_theme().
 */
function gent_base_theme() {
  return array(
    'entity_property__sheet__gpdc__form_url' => array(
      'base hook' => 'entity_property',
      'file' => 'theme/entity-property--sheet--gpdc--form-url.theme.inc',
    ),
    'entity_property__sheet__gpdc__references' => array(
      'base hook' => 'entity_property',
      'file' => 'theme/entity-property--sheet--gpdc--references.theme.inc',
    ),
    'entity_property__sheet__gpdc__organisations' => array(
      'base hook' => 'entity_property',
      'file' => 'theme/entity-property--sheet--gpdc--organisations.theme.inc',
    ),
    /*'entity_property__sheet__gpdc__related' => array(
     'base hook' => 'entity_property',
      'file' => 'theme/entity-property--sheet--gpdc--related.theme.inc',
    ),*/
    'entity_property__sheet__gpdc__attachments' => array(
      'base hook' => 'entity_property',
      'file' => 'theme/entity-property--sheet--gpdc--attachments.theme.inc',
    ),
    'entity_property__sheet__gpdc__forms' => array(
      'base hook' => 'entity_property',
      'file' => 'theme/entity-property--sheet--gpdc--forms.theme.inc',
    ),
  );
}

/**
 * Overrides theme_entity_property().
 */
function gent_base_entity_property(&$variables) {

  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<h2' . $variables['title_attributes'] . '>' . $variables['label'] . '</h2>';
  }

  // Render the content.
  $content_suffix = '';
  if (!$variables['label_hidden'] || $variables['content_attributes']) {
    $output .= '<div' . $variables['content_attributes'] . '>';
    $content_suffix = '</div>';
  }
  $output .= $variables['content'] . $content_suffix;
  if (empty($variables['content'])) {
    return FALSE;
  }
  // Render the top-level DIV.
  return '<div' . $variables['attributes'] . '>' . $output . '</div>';
}

/**
 * Overrides theme_fieldset().
 */
function gent_base_fieldset(&$variables) {
  $element = $variables['element'];
  element_set_attributes($element, array('id'));
  _form_set_class($element, array('form-wrapper'));

  $output = '<fieldset' . drupal_attributes($element['#attributes']) . '>';
  if (!empty($element['#title'])) {
    // Always wrap fieldset legends in a SPAN for CSS positioning.
    $output .= '<legend><span class="fieldset-legend">';
    $output .= $element['#title'];
    if (!empty($variables['element']['#collapsible'])) {
      if (!empty($variables['element']['#collapsed'])) {
        $output .= '<span class="icon-collapsed"></span>';
      }
      else {
        $output .= '<span class="icon-collapsible"></span>';
      }
    }
    $output .= '</span></legend>';
  }
  $output .= '<div class="fieldset-wrapper">';
  if (!empty($element['#description'])) {
    $output .= '<div class="fieldset-description">' . $element['#description'] . '</div>';
  }
  $output .= $element['#children'];
  if (isset($element['#value'])) {
    $output .= $element['#value'];
  }
  $output .= '</div>';
  $output .= "</fieldset>\n";
  return $output;
}

/**
 * Overrides theme_digipolis_openlayers_invisible_layer_icon().
 */
function gent_base_digipolis_openlayers_invisible_layer_icon(&$variables) {
  return '<span class="icon-invisible"></span>';
}

/**
 * Same as drupal_is_front_page, but added 404 & 403 pages.
 */
function gent_base_use_large_header() {
  if (drupal_is_front_page()) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Implements hook_webform_component_render_alter().
 *
 * Allow modules to modify a webform component that is going to be rendered in a form.
 *
 * @param array $element
 *   The display element as returned by _webform_render_component().
 * @param array $component
 *   A Webform component array.
 *
 * @see _webform_render_component()
 */
function gent_base_webform_component_render_alter(&$element, &$component) {
  if (!empty($element['#field_prefix'])) {
    $element['#wrapper_attributes']['class'][] = 'with-prefix';
  }
  if (!empty($element['#field_suffix'])) {
    $element['#wrapper_attributes']['class'][] = 'with-suffix';
  }
}

/**
 * Replacement for theme_webform_element().
 */
function gent_base_webform_element($variables) {
  $element = $variables['element'];

  $output = '<div ' . drupal_attributes($element['#wrapper_attributes']) . '>' . "\n";
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . webform_filter_xss($element['#field_prefix']) . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . webform_filter_xss($element['#field_suffix']) . '</span>' : '';

  // managed_file uses a different id, make sure the label points to the correct id.
  if (isset($element['#type']) && $element['#type'] === 'managed_file') {
    if (!empty($variables['element']['#id'])) {
      $variables['element']['#id'] .= '-upload';
    }
  }

  switch ($element['#title_display']) {
    case 'inline':
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= ' ' . $prefix . $suffix . '<span class="children">' . $element['#children'] . '</span>' . "\n";
      break;

    case 'after':
      $output .= ' ' . $prefix . $element['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  if (!empty($element['#description'])) {
    $output .= ' <div class="description">' . $element['#description'] . "</div>\n";
  }

  $output .= "</div>\n";

  return $output;
}
