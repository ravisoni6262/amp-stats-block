<?php
/**
 * Tests for class BlockExtendPlugin.
 */

namespace AMPS\BlockExtendTest;

use WP_Mock;
use Mockery;
use AMPS\BlockExtend\BlockExtendPlugin;
use AMPS\BlockExtend\Plugin;

/**
 * Tests for class BlockExtendPlugin.
 */
class BlockExtendPluginTest extends BlockExtendTestCase {

	/**
	 * Test init when AMP plugin is active.
	 *
	 * @covers AMPS\BlockExtend\BlockExtendPlugin::init()
	 */
	public function test_init_amp_active() {
		$plugin_frame = Mockery::mock( Plugin::class );
		$plugin = new BlockExtendPlugin( $plugin_frame );

		$plugin_frame->shouldReceive( 'amp_active' )->once()->andReturn( true );

		WP_Mock::expectActionAdded( 'enqueue_block_editor_assets', [ $plugin, 'enqueue_editor_assets' ], 10, 1 );

		WP_Mock::userFunction( 'register_block_type' )
			->once()
			->with(
				'amp-stats-block/amp-statistics-block',
				Mockery::type( 'array' )
			);

		$plugin->init();
	}

	/**
	 * Test init when AMP plugin is inactive.
	 *
	 * @covers AMPS\BlockExtend\BlockExtendPlugin::init()
	 */
	public function test_init_amp_inactive() {
		$plugin_frame = Mockery::mock( Plugin::class );
		$plugin = new BlockExtendPlugin( $plugin_frame );

		$plugin_frame->shouldReceive( 'amp_active' )->once()->andReturn( false );

		WP_Mock::expectActionAdded( 'admin_notices', [ $plugin, 'admin_notices' ], 10, 1 );

		$plugin->init();
	}

	/**
	 * Test admin notices.
	 *
	 * @covers AMPS\BlockExtend\BlockExtendPlugin::admin_notices()
	 */
	public function test_admin_notices() {
		$plugin_frame = Mockery::mock( Plugin::class );
		$plugin = new BlockExtendPlugin( $plugin_frame );

		WP_Mock::userFunction(
			'esc_html__',
			[
				'times' => 1,
				'args' => [ 'Block Extend', 'block-extend' ],
				'return' => 'Block Extend',
			]
		);

		WP_Mock::userFunction(
			'esc_html__',
			[
				'times' => 1,
				'args' => [ 'plugin requires AMP plugin.', 'block-extend' ],
				'return' => 'plugin requires AMP plugin.',
			]
		);

		$this->expectOutputString(
			'<div class="notice notice-error is-dismissible"><p>Block Extend plugin requires AMP plugin.</p></div>',
			$plugin->admin_notices()
		);
	}

	/**
	 * Test validation error count.
	 *
	 * @covers AMPS\BlockExtend\BlockExtendPlugin::get_validation_errors_count()
	 */
	public function test_get_validation_errors_count() {
		$plugin_frame = Mockery::mock( Plugin::class );
		$plugin = new BlockExtendPlugin( $plugin_frame );

		WP_Mock::userFunction( 'wp_count_terms' )
			->once()
			->with( BlockExtendPlugin::TAXONOMY_SLUG )
			->andReturn( rand() );

		$this->assertIsInt( $plugin->get_validation_errors_count() );
	}

	/**
	 * Test total validated URLs.
	 *
	 * @covers AMPS\BlockExtend\BlockExtendPlugin::enqueue_editor_assets()
	 */
	public function test_get_validated_urls_count() {
		$plugin_frame = Mockery::mock( Plugin::class );
		$plugin = new BlockExtendPlugin( $plugin_frame );

		$wp_query = Mockery::mock( 'overload:WP_Query' );

		$wp_query->shouldReceive( 'query' )
			->once()
			->with(
				[
					'post_type' => BlockExtendPlugin::POST_TYPE_SLUG,
					BlockExtendPlugin::VALIDATION_ERROR_STATUS_QUERY_VAR => [
						BlockExtendPlugin::VALIDATION_ERROR_NEW_REJECTED_STATUS,
						BlockExtendPlugin::VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
					],
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				]
			)
			->andSet( 'found_posts', rand() );

		$this->assertIsInt( $plugin->get_validated_urls_count() );
	}

	/**
	 * Test stattstics loader function
	 *
	 * @covers AMPS\BlockExtend\BlockExtendPlugin::load_amp_statistics()
	 */
	public function test_load_amp_statistics() {
		$plugin_frame = Mockery::mock( Plugin::class );
		$plugin = new BlockExtendPlugin( $plugin_frame );

		WP_Mock::userFunction( 'wp_count_terms' )
			->once()
			->with( BlockExtendPlugin::TAXONOMY_SLUG )
			->andReturn( 2 );

		WP_Mock::userFunction(
			'esc_html__',
			[
				'times' => 1,
				'args' => [ 'There are %d validation errors.', 'block-extend' ],
				'return' => 'There are %d validation errors.',
			]
		);

		// Mocking WP_Query to validated URLs.
		$wp_query = Mockery::mock( 'overload:WP_Query' );

		$wp_query->shouldReceive( 'query' )
			->once()
			->with(
				[
					'post_type' => BlockExtendPlugin::POST_TYPE_SLUG,
					BlockExtendPlugin::VALIDATION_ERROR_STATUS_QUERY_VAR => [
						BlockExtendPlugin::VALIDATION_ERROR_NEW_REJECTED_STATUS,
						BlockExtendPlugin::VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
					],
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				]
			)
			->andSet( 'found_posts', 4 );

		WP_Mock::userFunction(
			'esc_html__',
			[
				'times' => 1,
				'args' => [ 'There are %d validated URLs.', 'block-extend' ],
				'return' => 'There are %d validated URLs.',
			]
		);

		$this->assertEquals(
			'<p>There are 4 validated URLs.</p><p>There are 2 validation errors.</p>',
			$plugin->load_amp_statistics( Mockery::type( 'array' ), Mockery::type( 'string' ) )
		);
	}

	/**
	 * Test enqueue_editor_assets.
	 *
	 * @covers AMPS\BlockExtend\BlockExtendPlugin::enqueue_editor_assets()
	 */
	public function test_enqueue_editor_assets() {
		$plugin = Mockery::mock( Plugin::class );

		$plugin->shouldReceive( 'asset_url' )
			->once()
			->with( 'js/dist/editor.js' )
			->andReturn( 'http://example.com/js/dist/editor.js' );

		$plugin->shouldReceive( 'asset_version' )
			->once()
			->andReturn( '1.2.3' );

		WP_Mock::userFunction( 'wp_enqueue_script' )
			->once()
			->with(
				'amp-stats-block-js',
				'http://example.com/js/dist/editor.js',
				Mockery::type( 'array' ),
				'1.2.3'
			);

		$block_extend = new BlockExtendPlugin( $plugin );
		$block_extend->enqueue_editor_assets();
	}
}
