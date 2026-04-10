<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * =============================================================================
 * WhatsApp CRM — Database Configuration (PostgreSQL)
 * =============================================================================
 *
 * This file reads connection parameters from environment variables (.env).
 * If env vars are not set, it falls back to sensible development defaults.
 *
 * Driver: postgre (CodeIgniter 3's native PostgreSQL driver)
 * =============================================================================
 */

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn' => '',
    'hostname' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'waha_user',
    'password' => getenv('DB_PASS') ?: 'waha_secret_2026',
    'database' => getenv('DB_NAME') ?: 'waha_crm',
    'port' => getenv('DB_PORT') ?: 5432,
    'dbdriver' => 'postgre',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
