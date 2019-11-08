<?php

namespace AMPS\BlockExtend;

/**
 * Plugin Router.
 */
class BlockExtendPlugin {

	/**
	 * Plugin interface.
	 *
	 * @var AMPS\BlockExtend\Plugin
	 */
	protected $plugin;

	/**
	 * The slug of the taxonomy to store AMP errors.
	 *
	 * @var string
	 */
	const TAXONOMY_SLUG = 'amp_validation_error';

	/**
	 * The slug of the post type to store URLs that have AMP errors.
	 *
	 * @var string
	 */
	const POST_TYPE_SLUG = 'amp_validated_url';

	/**
	 * Query var used when filtering by validation error status or passing updates.
	 *
	 * @var string
	 */
	const VALIDATION_ERROR_STATUS_QUERY_VAR = 'amp_validation_error_status';

	/**
	 * Term group for new validation_error terms which are rejected (not auto-accepted).
	 *
	 * @var int
	 */
	const VALIDATION_ERROR_NEW_REJECTED_STATUS = 0;

	/**
	 * Term group for new validation_error terms which are auto-accepted.
	 *
	 * @var int
	 */
	const VALIDATION_ERROR_NEW_ACCEPTED_STATUS = 1;

	/**
	 * Setup the plugin instance.
	 *
	 * @param AMPS\BlockExtend\Plugin $plugin Instance of the plugin abstraction.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Hook into WP.
	 *
	 * @return void
	 */
	public function init() {
		// Check if the AMP plugin is activated.
		if ( $this->plugin->amp_active() ) {
			add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );
			$this->register_amp_stats_block();
		} else {
			add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		}
	}

	/**
	 * Admin notices
	 *
	 * @return void
	 */
	public function admin_notices() {
		echo sprintf(
			'<div class="notice notice-error is-dismissible"><p>%s %s</p></div>',
			esc_html__( 'Block Extend', 'block-extend' ),
			esc_html__( 'plugin requires AMP plugin.', 'block-extend' )
		);
	}

	/**
	 * Load our block assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'amp-stats-block-js',
			$this->plugin->asset_url( 'js/dist/editor.js' ),
			[
				'lodash',
				'react',
				'wp-block-editor',
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-i18n',
			],
			$this->plugin->asset_version()
		);
	}

	/**
	 * Registers the AMP Statistics block
	 *
	 * @return void
	 */
	public function register_amp_stats_block() {
		register_block_type(
			'amp-stats-block/amp-statistics-block',
			[
				'editor_script' => 'amp-stats-block-js',
				'render_callback' => [ $this, 'load_amp_statistics' ],
			]
		);
	}

	/**
	 * Get total validation errors.
	 *
	 * @return int
	 */
	public function get_validation_errors_count() {
		return \wp_count_terms( self::TAXONOMY_SLUG );
	}

	/**
	 * Get total validated URLs.
	 *
	 * @return int
	 */
	public function get_validated_urls_count() {
		$query = new \WP_Query();
		$query->query(
			[
				'post_type' => self::POST_TYPE_SLUG,
				self::VALIDATION_ERROR_STATUS_QUERY_VAR => [
					self::VALIDATION_ERROR_NEW_REJECTED_STATUS,
					self::VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
				],
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]
		);
		return $query->found_posts;
	}

	/**
	 * Callback to fetch AMP statistics
	 *
	 * @param array  $attributes block attributes.
	 * @param string $content block content.
	 * @return string
	 */
	public function load_amp_statistics( $attributes, $content ) {
		return sprintf(
			'<p>%s</p><p>%s</p>',
			/* translators: '%d' will be number of validated URLs. */
			sprintf( esc_html__( 'There are %d validated URLs.', 'block-extend' ), $this->get_validated_urls_count() ),
			/* translators: '%d' will be number of validated errors. */
			sprintf( esc_html__( 'There are %d validation errors.', 'block-extend' ), $this->get_validation_errors_count() )
		);
	}
}
