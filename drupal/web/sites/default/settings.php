<?php

/**
 * General settings.php for all environments.
 * You could use this to add general settings to be used for all environments.
 */

/**
 * Database settings (overridden per environment)
 */
$databases = [];
$databases['default']['default'] = [
  'database' => getenv('DB_NAME_DRUPAL'),
  'username' => getenv('DB_USER_DRUPAL'),
  'password' => getenv('DB_PASS_DRUPAL'),
  'prefix' => '',
  'host' => getenv('DB_HOST_DRUPAL'),
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

// CHANGE THIS.
$settings['hash_salt'] = 'some-hash-salt-please-change-this';

if ((isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on")
  || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https")
  || (isset($_SERVER["HTTP_HTTPS"]) && $_SERVER["HTTP_HTTPS"] == "on")
) {
  $_SERVER["HTTPS"] = "on";

  // Tell Drupal we're using HTTPS (url() for one depends on this).
  $settings['https'] = TRUE;
}

// @codingStandardsIgnoreStart
if (isset($_SERVER['REMOTE_ADDR'])) {
  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR']];
}
// @codingStandardsIgnoreEnd

if (!empty($_SERVER['SERVER_ADDR'])) {
  // This should return last section of IP, such as "198". (dont want/need to expose more info).
  //drupal_add_http_header('X-Webserver', end(explode('.', $_SERVER['SERVER_ADDR'])));
  $pcs = explode('.', $_SERVER['SERVER_ADDR']);
  header('X-Webserver: ' . end($pcs));
}

$settings['memcache']['servers'] = ['127.0.0.1:11211' => 'default'];

$env = getenv('WKV_SITE_ENV');
switch ($env) {
  case 'prod':
    $settings['simple_environment_indicator'] = '#d4000f Production';
    $settings['memcache']['servers'] = [
      '[front1_internal_ip]:11211' => 'default',
      '[front2_internal_ip]:11211' => 'default',
    ];
    break;

  case 'dev':
    $settings['simple_environment_indicator'] = '#004984 Development';
    break;

  case 'stage':
    $settings['simple_environment_indicator'] = '#e56716 Stage';
    break;

  case 'local':
  case 'lando':
    $settings['simple_environment_indicator'] = '#88b700 Local';
    break;
}
/**
 * Location of the site configuration files.
 */
$config_directories = [
  CONFIG_SYNC_DIRECTORY => '../sync',
];

/**
 * Memcache configuration.
 */
if (!drupal_installation_attempted() && extension_loaded('memcached') && class_exists('Memcached')) {
  // Define memcache settings only if memcache module enabled.
  if (strpos(file_get_contents(getcwd().'/../sync/core.extension.yml'), 'memcache: 0') !== FALSE) {
    $settings['memcache']['extension'] = 'Memcached';
    $settings['memcache']['bins'] = ['default' => 'default'];
    $settings['memcache']['key_prefix'] = '';
    #$settings['cache']['default'] = 'cache.backend.memcache';
    $settings['cache']['bins']['render'] = 'cache.backend.memcache';
    $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.memcache';
    $settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
    $settings['cache']['bins']['config'] = 'cache.backend.memcache';
    $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';

    // Enable stampede protection.
    $settings['memcache']['stampede_protection'] = TRUE;

    // High performance - no hook_boot(), no hook_exit(), ignores Drupal IP
    // blacklists.
    $conf['page_cache_invoke_hooks'] = FALSE;
    $conf['page_cache_without_database'] = TRUE;

    // Memcached PECL Extension Support.
    // Adds Memcache binary protocol and no-delay features (experimental).
    $settings['memcache']['options'] = [
      \Memcached::OPT_COMPRESSION => FALSE,
      \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
      \Memcached::OPT_BINARY_PROTOCOL => TRUE,
      \Memcached::OPT_TCP_NODELAY => TRUE,
    ];
  }
}

/**
 * Access control for update.php script.
 */
$settings['update_free_access'] = FALSE;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * The default list of directories that will be ignored by Drupal's file API.
 *
 * By default ignore node_modules and bower_components folders to avoid issues
 * with common frontend tools and recursive scanning of directories looking for
 * extensions.
 *
 * @see file_scan_directory()
 * @see \Drupal\Core\Extension\ExtensionDiscovery::scanDirectory()
 */
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

/**
 * Environment specific override configuration, if available.
 */
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}

/**
 * Lando configuration overrides.
 */
if (getenv('LANDO_INFO') && file_exists($app_root . '/' . $site_path . '/settings.lando.php')) {
  include $app_root . '/' . $site_path . '/settings.lando.php';
}

/**
 * Silta cluster configuration overrides.
 */
if (getenv('SILTA_CLUSTER') && file_exists($app_root . '/' . $site_path . '/settings.silta.php')) {
  include $app_root . '/' . $site_path . '/settings.silta.php';
}
