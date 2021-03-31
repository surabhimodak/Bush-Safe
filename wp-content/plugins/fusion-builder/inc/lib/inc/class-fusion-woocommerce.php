<?php
/**
 * Modifications for WooCommerce.
 *
 * @author     ThemeFusion
 * @link       https://theme-fusion.com
 * @package Fusion-Library
 * @subpackage Core
 * @since 3.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Library class for shared WooCommerce functionality.
 *
 * @since 3.2
 */
class Fusion_WooCommerce {

	/**
	 * Constructor.
	 *
	 * @since 3.2
	 * @access public
	 */
	public function __construct() {

		// Runs after we know of layout section overrides.
		add_action( 'wp', [ $this, 'wp' ], 20 );

		add_action( 'woocommerce_before_single_product_summary', [ $this, 'before_single_product_summary_open' ], 5 );
		add_action( 'woocommerce_before_single_product_summary', [ $this, 'before_single_product_summary_close' ], 30 );

		add_filter( 'woocommerce_single_product_image_gallery_classes', [ $this, 'single_product_image_gallery_classes' ], 10 );
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [ $this, 'single_product_image_thumbnail_html' ], 10, 2 );

		add_filter( 'woocommerce_single_product_carousel_options', [ $this, 'single_product_carousel_options' ], 10 );
	}

	/**
	 * WP hook calls to delay.
	 *
	 * @access public
	 * @since 3.2
	 * @return void
	 */
	public function wp() {
		$layout_product = function_exists( 'Fusion_Builder_WooCommerce' ) && Fusion_Builder_WooCommerce()->is_layout_product();

		if ( $layout_product ) {
			wp_dequeue_style( 'photoswipe-default-skin' );
		}
	}

	/**
	 * Add wrapping container opening for single product image gallery.
	 *
	 * @since 5.1
	 * @access public
	 * @return void
	 */
	public function before_single_product_summary_open() {
		include FUSION_LIBRARY_PATH . '/inc/templates/wc-before-single-product-summary-open.php';
	}

	/**
	 * Add wrapping container closing for single product image gallery.
	 *
	 * @since 5.1
	 * @access public
	 * @return void
	 */
	public function before_single_product_summary_close() {
		include FUSION_LIBRARY_PATH . '/inc/templates/wc-before-single-product-summary-close.php';
	}

	/**
	 * Filters single product page image gallery classes.
	 *
	 * @since 3.2
	 * @access public
	 * @param array $classes Holds the single product image gallery classes.
	 * @return array The altered classes.
	 */
	public function single_product_image_gallery_classes( $classes ) {
		if ( 'avada' === apply_filters( 'avada_woocommerce_product_images_layout', 'avada' ) ) {
			$classes[] = 'avada-product-gallery';
		}

		return $classes;

	}

	/**
	 * Filters single product image thumbnail html.
	 *
	 * @since 3.2
	 * @access public
	 * @param string $html Holds the single product image thumbnail html.
	 * @param number $attachment_id The attachment id for single product image.
	 * @return array The altered html markup.
	 */
	public function single_product_image_thumbnail_html( $html, $attachment_id ) {
		global $post, $product;

		if ( 'avada' !== apply_filters( 'avada_woocommerce_product_images_layout', 'avada' ) ) {
			return $html;
		}

		// Early exit if attachment is missing.
		if ( ! $attachment_id ) {
			return $html;
		}

		$attachment_count = count( $product->get_gallery_image_ids() );
		$full_size_image  = wp_get_attachment_image_src( $attachment_id, 'full' );
		$attachment_data  = fusion_library()->images->get_attachment_data( $attachment_id, 'none' );

		$gallery = '[]';
		if ( $attachment_count > 0 ) {
			$gallery = '[product-gallery]';
		}

		$html = str_replace( '</div>', '<a class="avada-product-gallery-lightbox-trigger" href="' . esc_url( $full_size_image[0] ) . '" data-rel="iLightbox' . $gallery . '" alt="' . $attachment_data['alt'] . '" data-title="' . $attachment_data['title_attribute'] . '" data-caption="' . $attachment_data['caption_attribute'] . '"></a></div>', $html );

		return $html;
	}

	/**
	 * Filters single product page image flexslider options.
	 *
	 * @since 3.2
	 * @access public
	 * @param array $flexslider_options Holds the default options for setting up the flexslider object.
	 * @return array The altered flexslider options.
	 */
	public function single_product_carousel_options( $flexslider_options ) {
		global $post;

		$flexslider_options['directionNav'] = true;

		$product = wc_get_product( $post );

		if ( is_object( $product ) ) {

			$attachment_ids = $product->get_gallery_image_ids();

			if ( 'avada' === apply_filters( 'avada_woocommerce_product_images_layout', 'avada' ) && 0 < count( $attachment_ids ) ) {
				$flexslider_options['animationLoop'] = true;
				$flexslider_options['smoothHeight']  = true;
			}
		}

		return $flexslider_options;
	}

	/**
	 * Calculates products discount in %.
	 *
	 * @access public
	 * @since 3.2
	 * @param object $product The product object.
	 * @param string $discount_type Should be discount be calced as percentage or value.
	 * @return string
	 */
	public function calc_product_discount( $product, $discount_type = 'percent' ) {
		if ( $product->is_type( 'variable' ) ) {
			$temp_discount = 0;
			$discount      = 0;
			$prices        = $product->get_variation_prices();
			$discount_str  = '';

			foreach ( $prices['price'] as $key => $price ) {
				if ( $prices['regular_price'][ $key ] !== $price ) {

					if ( 'percent' === $discount_type ) {
						$temp_discount = round( 100 - ( $prices['sale_price'][ $key ] / $prices['regular_price'][ $key ] * 100 ) );
					} else {
						$temp_discount = $prices['regular_price'][ $key ] - $prices['sale_price'][ $key ];
					}

					if ( $temp_discount > $discount ) {
						$discount = $temp_discount;
					}
				}
			}
		} else {
			$regular_price = (float) $product->get_regular_price();
			$sale_price    = (float) $product->get_sale_price();

			if ( 'percent' === $discount_type ) {
				$discount = round( 100 - ( $sale_price / $regular_price * 100 ) );
			} else {
				$discount = $regular_price - $sale_price;
			}
		}

		// Percent.
		if ( 'percent' === $discount_type ) {
			$discount_str = $discount . '%';
		} elseif ( 'right' === get_option( 'woocommerce_currency_pos' ) ) { // Amount.
			$discount_str = $discount . get_woocommerce_currency_symbol();
		} elseif ( 'right_space' === get_option( 'woocommerce_currency_pos' ) ) {
			$discount_str = $discount . ' ' . get_woocommerce_currency_symbol();
		} elseif ( 'left' === get_option( 'woocommerce_currency_pos' ) ) {
			$discount_str = get_woocommerce_currency_symbol() . $discount;
		} elseif ( 'left_space' === get_option( 'woocommerce_currency_pos' ) ) {
			$discount_str = get_woocommerce_currency_symbol() . ' ' . $discount;
		}

		return $discount_str;
	}
}
