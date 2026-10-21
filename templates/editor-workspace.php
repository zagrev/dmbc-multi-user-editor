<?php
/**
 * Template for rendering the Collaborative Tiptap Editor Canvas.
 *
 * Available variables:
 * @var WP_Post $post The current WordPress Post object.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="dmbc-editor-workspace"
	style="margin-top: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
	<!-- Dynamic Collaboration Status Ribbon Tracker Bar -->
	<div id="dmbc-editor-toolbar"
		style="padding: 10px; border-bottom: 1px solid #ccd0d4; background: #f6f7f7; display: flex; justify-content: space-between; align-items: center;">
		<div class="tiptap-controls">
			<strong>Format Tools:</strong> <!-- Tiptap menu controls inject here -->
		</div>
		<div id="dmbc-active-users" style="font-size: 12px; color: #646970;">
			Connecting to collaboration room...
		</div>
	</div>

	<!-- 1. SEED CONTENT: Hidden container containing initial database HTML -->
	<div id="dmbc-editor-initial-content" style="display: none;">
		<?php echo wp_kses_post( $post->post_content ); ?>
	</div>

	<!-- Mounting Element Container for Tiptap Editor Core Instance -->
	<div id="dmbc-tiptap-container"
		style="padding: 20px; min-height: 400px; font-size: 16px; line-height: 1.6; outline: none;"></div>
</div>