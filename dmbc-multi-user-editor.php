<?php
/**
 * Plugin Name: dmbc-multi-user-editor
 * Plugin URI: https://github.com/zagrev/dmbc-multi-user-editor
 * Description: Foundation for a WordPress editor that supports multiple simultaneous users.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Steve Betts
 * Author URI: https://github.com/zagrev
 * License: Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: dmbc-multi-user-editor
 * Update URI: https://github.com/zagrev/dmbc-multi-user-editor
 *
 * @package DmbcMultiUserEditor
 */

declare(strict_types=1);

namespace Dmbc\MultiUserEditor;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
use YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api;

if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! \is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	\add_action(
		'admin_notices',
		static function (): void {
			if ( ! \current_user_can( 'activate_plugins' ) ) {
				return;
			}

			echo '<div class="notice notice-error"><p>';
			echo \esc_html__(
				'dmbc-multi-user-editor is not initialized: Composer dependencies are missing. Run composer install in the plugin directory or install a release ZIP that includes vendor/.',
				'dmbc-multi-user-editor'
			);
			echo '</p></div>';
		}
	);
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

\add_action(
	'plugins_loaded',
	static function (): void {
		$update_checker = PucFactory::buildUpdateChecker(
			'https://github.com/zagrev/dmbc-multi-user-editor',
			__FILE__,
			'dmbc-multi-user-editor'
		);
		$update_checker->setBranch( 'main' );
		$update_checker->getVcsApi()->enableReleaseAssets(
			'/^dmbc-multi-user-editor.*\.zip$/',
			Api::REQUIRE_RELEASE_ASSETS
		);
		// Tags and branches use source archives that omit Composer dependencies.
		$update_checker->addFilter(
			'vcs_update_detection_strategies',
			static function ( array $strategies ): array {
			return \array_intersect_key(
				$strategies,
				array( Api::STRATEGY_LATEST_RELEASE => true )
			);
		}
		);
	}
);
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DMBC_Collaborative_Editor {

	public function __construct() {
		add_filter( 'use_block_editor_for_post', array( $this, 'disable_gutenberg_editor' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'remove_classic_editor_support' ) );
		add_action( 'edit_form_after_title', array( $this, 'render_tiptap_canvas' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_editor_assets' ) );
	}

	public function disable_gutenberg_editor( $use_block_editor, $post ) {
		return false;
	}

	public function remove_classic_editor_support() {
		remove_post_type_support( 'post', 'editor' );
		remove_post_type_support( 'page', 'editor' );
	}

	/**
	 * Locate and render the HTML template view module.
	 */
	public function render_tiptap_canvas( $post ) {
		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		// Define path to our custom editor template view file
		$template_path = plugin_dir_path( __FILE__ ) . 'templates/editor-workspace.php';

		// Optional: Allow active themes or child-themes to override this layout if they contain a matching file path
		$theme_override = locate_template( 'dmbc-editor/editor-workspace.php' );
		if ( $theme_override ) {
			$template_path = $theme_override;
		}

		// Safely include the file context if it is readable on the filesystem
		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Collaborative editor template workspace file not found.', 'dmbc-editor' ) . '</p></div>';
		}
	}

	public function enqueue_editor_assets( $hook ) {
		global $post;

		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'dmbc-editor-css', plugin_dir_url( __FILE__ ) . 'assets/editor-style.css', array(), '1.1.0' );
		wp_enqueue_script( 'dmbc-editor-js', plugin_dir_url( __FILE__ ) . 'assets/editor-bundle.min.js', array( 'jquery' ), '1.1.0', true );

		$current_user = wp_get_current_user();
		$session_token = wp_create_nonce( 'dmbc_editor_auth_' . $post->ID );

		$colors = array( '#3724e0', '#9B1C1C', '#d06901', '#B45309', '#047481', '#647a05', '#1C64F2', '#6B21A8' );
		$assigned_color = $colors[ $current_user->ID % count( $colors ) ];

		wp_localize_script( 'dmbc-editor-js', 'wpEditorConfig', array(
			'postId' => $post->ID,
			'userToken' => $session_token,
			'currentUserName' => ! empty( $current_user->display_name ) ? $current_user->display_name : $current_user->user_login,
			'currentUserColor' => $assigned_color,
		) );
	}
}

new DMBC_Collaborative_Editor();