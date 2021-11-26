<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(

	'config' => array(
		'base_url' => 'http://192.168.0.29/salas/',
		'log_threshold' => 1,
		'index_page' => '',
		'uri_protocol' => 'REQUEST_URI',
	),

	'database' => array(
		'dsn' => 'mysql:host=localhost;dbname=salas',
		'hostname' => '',
		'username' => 'ubuntu',
		'password' => 'ubuntu',
		'database' => 'salas',
		'dbdriver' => 'mysqli',
	),

);
