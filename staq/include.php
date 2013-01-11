<?php

/* This file is part of the Staq project, which is under MIT license */

namespace Staq {
	const VERSION = '0.4';

	if ( ! defined( 'HTML_EOL' ) ) {
		define( 'HTML_EOL', '<br>' . PHP_EOL );
	}
	if ( ! defined( 'Staq\\ROOT_PATH' ) ) {
		define( 'Staq\\ROOT_PATH', dirname( __DIR__ ) . '/' );
	}

	require_once( __DIR__ . '/util/functions.php' );
	require_once( __DIR__ . '/../vendor/pixel418/ubiq/Ubiq/Ubiq.php' );
	require_once( __DIR__ . '/core/Server.php' );
	require_once( __DIR__ . '/core/Application.php' );
	require_once( __DIR__ . '/core/Autoloader.php' );
}
