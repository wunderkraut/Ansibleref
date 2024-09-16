<?php

/*
 * Load database credentials from Lando app environment.
 */
$lando_info = json_decode(getenv('LANDO_INFO'), TRUE);

if ($lando_info) {
  $service_info_from_lando_env = function ($service) use ($lando_info) {
    foreach ($lando_info as $service_info) {
      if (isset($service_info['service']) && $service_info['service'] === $service) {
        return $service_info;
      }
    }

    return NULL;
  };

  $db_info = $service_info_from_lando_env('database');

  if ($db_info && isset($db_info['creds']) && isset($db_info['internal_connection'])) {
    $databases['default']['default'] = [
      'driver' => 'mysql',
      'database' => $db_info['creds']['database'],
      'username' => $db_info['creds']['user'],
      'password' => $db_info['creds']['password'],
      'host' => $db_info['internal_connection']['host'],
      'port' => $db_info['internal_connection']['port'],
    ];
  }
}


// Use the hash_salt setting from Lando.
$settings['hash_salt'] = getenv('HASH_SALT') ? getenv('HASH_SALT') : 'random_hash_salt';

// Skip file system permissions hardening when using local development with Lando.
$settings['skip_permissions_hardening'] = TRUE;

// Skip trusted host pattern when using Lando.
$settings['trusted_host_patterns'] = ['.*'];
