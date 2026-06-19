<?php


namespace {
  if (!defined('ABSPATH')) // wp-config.php defines an ABSPATH variable, this trap ensures that only WP can use this code.
  exit;
}

namespace gs_migration_settings {

  function gs_add_migration_settings_page() {
    add_menu_page(
      'GS Migration Settings',
      'GS Migration',
      'manage_options',
      'gs-council-migration-settings',
      'gs_migration_settings\gs_render_migration_settings_page',
      'dashicons-update',
      100
    );
  }

  function gs_render_migration_settings_page() {
    if (!is_super_admin()) {
      echo '<div class="wrap">';
      echo '<h1>This page is only for Multisite Network Administrators</h1>';
      echo '</div>';
      return;
    }

    echo '<div class="wrap">';
    echo '<h1>GS Migration Tool</h1>';
    echo '<p>This tool will migrate post meta fields (<code>year</code>, <code>committee</code>, <code>document-type</code>) to taxonomy terms for custom post types <code>gs_meetings</code>, <code>gs_file</code>, and <code>gs_member</code>.</p>';

    if (isset($_POST['gs_migrate_meta_to_taxonomies_test']) && check_admin_referer('gs_migration_nonce')) {
      // gs_migrate_meta_to_taxonomies();
      gs_migrate_meta_to_taxonomies_test();
    }

    if (get_option('gs_migration_completed')) {
      echo '<p><strong>Migration has already been completed.</strong></p>';
    } else {
      echo '<form method="post">';
      wp_nonce_field('gs_migration_nonce');
      echo '<input type="submit" name="gs_migrate_meta_to_taxonomies_test" class="button button-primary" value="Run Test Migration">';
      $disabled = ''; //get_option('gs_migration_affected_records') ? '' : ' disabled';
      echo '<input type="checkbox" id="gs_migrate_meta_to_taxonomies" name="gs_migrate_meta_to_taxonomies" value=1
        style="display:block; margin:5px; "' . $disabled . '>';
      echo '<label for="gs_migrate_meta_to_taxonomies">Check here to enable one-time migration execution</label>';
      echo '</form>';
    }

    echo '</div>';

    $summary = get_transient('gs_migration_summary');
    if (!empty($summary)) {
      echo '<h2>Migration Summary</h2>';
      echo '<table class="widefat fixed striped">';
      echo '<thead><tr><th>Post ID</th><th>Title</th><th>Post Type</th><th>Migrated Terms</th></tr></thead><tbody>';

      foreach ($summary as $row) {
        echo '<tr>';
        echo '<td>' . esc_html($row['ID']) . '</td>';
        echo '<td>' . esc_html($row['title']) . '</td>';
        echo '<td>' . esc_html($row['type']) . '</td>';
        echo '<td>' . $row['migrated'] . '</td>';
        echo '</tr>';
      }

      echo '</tbody></table>';
    }
  }

  function gs_migrate_meta_to_taxonomies_test() {
    if ($_POST['gs_migrate_meta_to_taxonomies'])
      echo '<div class="notice notice-success"><p>Commit box was checked!</p></div>';
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
  }

  function gs_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
      error_log('[GS Migration] ' . $message);
    }
  }

  function gs_migrate_meta_to_taxonomies() {
    if (!is_super_admin()) {
      gs_log('GS Migration: Attempt made by non-Network Admin.');
      return;
    }
    if (get_option('gs_migration_completed')) {
      gs_log('GS Migration: Already completed.');
      return;
    }

    $post_types = ['gs_meetings', 'gs_file', 'gs_member'];
    $meta_to_tax = [
      'year' => 'committee-year',
      'committee' => 'committee',
      'document-type' => 'document-type'
    ];
    $search_replace_map = [
      'year' => ['needle' => '', 'replace' => ''],
      'committee' => ['needle' => '_serving_years', 'replace' => '-committee'],
      'document-type' => ['needle' => '', 'replace' => '']
    ];

    $summary = [];

    gs_log('GS Migration: Starting migration.');

    foreach ($post_types as $post_type) {
      $posts = get_posts([
          'post_type' => $post_type,
          'posts_per_page' => -1,
          'post_status' => 'any'
      ]);

      foreach ($posts as $post) {
        $migrated = [];

        foreach ($meta_to_tax as $meta_key => $taxonomy) {
          $meta_value = get_post_meta($post->ID, $meta_key, true);
          if (!empty($meta_value)) {
            $mapped_meta_value = $search_replace_map[$meta_key]['needle'] ?
              str_replace($search_replace_map[$meta_key]['needle'], $search_replace_map[$meta_key]['replace'], $meta_value) : $meta_value;
            wp_set_object_terms($post->ID, $mapped_meta_value, $taxonomy, true);
            $migrated[] = "$meta_key → $taxonomy: $mapped_meta_value";
            gs_log("GS Migration: Post {$post->ID} - $meta_key → $taxonomy: $mapped_meta_value");
          }
        }

        if (!empty($migrated)) {
          $summary[] = [
            'ID' => $post->ID,
            'title' => $post->post_title,
            'type' => $post_type,
            'migrated' => implode('<br>', $migrated)
          ];
        }
      }
    }

    update_option('gs_migration_completed', true);
    set_transient('gs_migration_summary', $summary, 3600); // Store for 1 hour

    echo '<div class="notice notice-success"><p>Migration completed successfully.</p></div>';
  }

  function gs_migrate_options_to_taxonomies_init() {
    if (add_option('gs_migration_completed', false)) {
      gs_log("GS Migration: Option gs_migration_completed created and initialized to false.");
    }
    if (add_option('gs_migration_affected_records', '')) {
      gs_log("GS Migration: Option gs_migration_affected_records created and initialized to ''.");
    }
  }

  // Hooks to register options and add admin menu
  add_action('admin_init', 'gs_migration_settings\gs_migrate_options_to_taxonomies_init');
  add_action('admin_menu', 'gs_migration_settings\gs_add_migration_settings_page');
}