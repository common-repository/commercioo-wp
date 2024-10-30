<?php
/**
 * The class responsible for the powered by label
 *
 * @link       http://example.com
 * @since      0.2.3
 *
 * @package    Commercioo
 * @subpackage Commercioo/public
 */

/**
 * class Commercioo_Powered_By_Label
 *
 * @package    Commercioo
 * @subpackage Commercioo/public
 * @author     Your Name <email@example.com>
 */
class Commercioo_Powered_By_Label {

	/**
     * The url of the label.
     *
     * @since    0.2.3
     * @var      string
     */
    public $url;
	
	/**
     * The url of the logo.
     *
     * @since    0.2.3
     * @var      string
     */
    public $logo_url;

	/**
     * The powered by caption.
     *
     * @since    0.2.3
     * @var      string
     */
    public $powered_by;

	/**
     * Whenever the label is visible or not
     *
     * @since    0.2.3
     * @var      string
     */
    public $is_visible;
	
	/**
     * Initialize the class and set its properties.
     *
     * @since    0.2.3
     */
    public function __construct( $render_on_construct = false ) {
		$this->url            = apply_filters( 'commercioo_powered_by_url', 'https://commercioo.com/' );
		$this->logo_url       = apply_filters( 'commercioo_powered_by_logo_url', COMMERCIOO_URL . 'img/icon_commercioo.png' );
		$this->admin_logo_url = apply_filters( 'commercioo_powered_by_logo_url', COMMERCIOO_URL . 'img/commercioo-logo.svg' );
		$this->vendor         = apply_filters( 'commercioo_powered_by_vendor', 'Commercioo' );
		$this->is_visible     = apply_filters( 'commercioo_powered_by_is_visible', true );

		// prevent empty values
		$this->url            = ! empty( $this->url ) ? $this->url : 'https://commercioo.com/';
		$this->logo_url       = ! empty( $this->logo_url ) ? $this->logo_url : COMMERCIOO_URL . 'img/icon_commercioo.png';
		$this->admin_logo_url = ! empty( $this->admin_logo_url ) ? $this->admin_logo_url : COMMERCIOO_URL . 'img/commercioo-logo.svg';
		$this->vendor         = ! empty( $this->vendor ) ? $this->vendor : 'Commercioo';

		// render_on_construct
		if ( $render_on_construct ) {
			$this->render();
		}
	}

	/**
	 * Render the label
	 * 
	 * @since    0.2.3
	 */
	public function render() {
		// Whether the label is hidden or not
		if ( $this->is_visible ) {
			// enqueue style
			wp_enqueue_style( 'commercioo-powered-by-label' );
			
			// display the html
			include COMMERCIOO_PATH . 'public/partials/commercioo-powered-by-label-display.php';
		}
	}

	/**
	 * Render text-only label
	 * 
	 * @since    0.2.4
	 */
	public function render_text_only() {
		// Whether the label is hidden or not
		if ( $this->is_visible ) {
			printf( 
				'%s <a href="%s">%s</a>',
				esc_html( 'Built with', 'commercioo' ),
				esc_url( $this->url ),
				esc_html( $this->vendor )
			);			
		}
	}

	/**
	 * Render admin footer
	 * 
	 * @since    0.2.4
	 */
	public function render_admin_footer() {
		if ( 'Commercioo' === $this->vendor || empty( $this->vendor ) ) {
			$footer_text = sprintf(__( 'Copyright © %s Commercioo. All Rights Reserved.', 'commercioo' ),date("Y"));
		}
		else {
			$footer_text = sprintf( 
				__( 'Built with %s. Set up and maintained by %s', 'commercioo' ), 
				sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( 'https://commercioo.com/' ), esc_html( 'Commercioo' ) ),
				sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $this->url ), esc_html( $this->vendor ) )
			);
		}

		echo apply_filters( 'commercioo_admin_footer_text', $footer_text );
	}
	
}