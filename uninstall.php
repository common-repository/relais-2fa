<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS rl_codes" );
$wpdb->query( "DROP TABLE IF EXISTS rl_nonces" );

$meta_type  = 'user';
$user_id    = 0; // This will be ignored, since we are deleting for all users.
$meta_key   = 'rl_uuid';
$meta_value = ''; // Also ignored. The meta will be deleted regardless of value.
$delete_all = true;

delete_metadata( $meta_type, $user_id, $meta_key, $meta_value, $delete_all );