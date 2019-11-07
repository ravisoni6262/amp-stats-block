<?php
/**
 * Plugin Name: Block Extend
 * Description: Extend Gutenberg editor blocks.
 * Version: 0.1.0
 * Author: Ravi Soni
 * Author URI: https://github.com/ravisoni6262
 * Text Domain: block-extend
 */

namespace AMPS\BlockExtend;

// Support for site-level autoloading.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

$block_extend_plugin = new BlockExtendPlugin( new Plugin( __FILE__ ) );

add_action( 'plugins_loaded', [ $block_extend_plugin, 'init' ] );
