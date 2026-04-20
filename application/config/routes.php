<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Webhook Route
$route['webhook/receive'] = 'webhook/index';

// Whatsapp AJAX routes
$route['whatsapp/ajax_get_sidebar']              = 'whatsapp/ajax_get_sidebar';
$route['whatsapp/ajax_get_chat_history/(:any)']  = 'whatsapp/ajax_get_chat_history/$1';
$route['whatsapp/ajax_get_contact_info/(:any)']  = 'whatsapp/ajax_get_contact_info/$1';  // BUG 4 FIX
$route['whatsapp/ajax_send_message']             = 'whatsapp/ajax_send_message';
$route['whatsapp/proxy_avatar']                  = 'whatsapp/proxy_avatar';               // BUG 3 FIX
