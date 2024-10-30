<?php
if ( ! class_exists( 'WP_Customize_Control' ) ) return NULL;

/**
 * Class to create a custom tags control
 */
Class Text_Editor_Custom_Control extends WP_Customize_Control {
	public function enqueue() {
		if ( defined( 'COMMERCIOO_VERSION' ) ) {
			$version = COMMERCIOO_VERSION;
		} else {
			$version = '1.0.0';
		}

		wp_enqueue_editor();
		wp_enqueue_script( 'text-editor-custom-control', COMMERCIOO_URL . 'admin/js/text-editor-custom-control.js', array( 'jquery', 'customize-controls' ), $version, true );
	}

	/**
	 * Render the content on the theme customizer page
	 */
	public function render_content() {
		$input_id         = '_customize-input-' . $this->id;
        $description_id   = '_customize-description-' . $this->id;
        $describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
		?>
		
		<?php if ( ! empty( $this->label ) ) : ?>
			<label 
				for="<?php echo esc_attr( $input_id ); ?>" 
				class="customize-control-title"
				style="padding-bottom:5px;"
			><?php echo esc_html( $this->label ); ?></label>
		<?php endif; ?>

		<textarea
			id="<?php echo esc_attr( $input_id ); ?>"
			style="display:none;"
			<?php echo esc_attr($describedby_attr); ?>
			<?php $this->link(); ?>
		><?php echo esc_textarea( $this->value() ); ?></textarea>

		<textarea 
			class="comm-customizer-wpeditor"
			id="<?php echo esc_attr( uniqid() ) ?>"
			original-id="<?php echo esc_attr( $input_id ); ?>"
			rows=8
		><?php echo esc_textarea( $this->value() ); ?></textarea>

		<?php if ( ! empty( $this->description ) ) : ?>
			<span 
				id="<?php echo esc_attr( $description_id ); ?>" 
				class="description customize-control-description"
				style="padding-top:10px;"
			><?php echo esc_attr($this->description); ?></span>
		<?php endif; ?>

		<?php
	}
}