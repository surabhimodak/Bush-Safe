<?php
/**
 * The template for displaying search forms
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<form method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'lalita' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php echo esc_attr_e( 'Search &hellip;', 'lalita' ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php esc_attr_e( 'Search for:', 'lalita' ); ?>">
	</label>
	<input type="submit" class="search-submit" value="<?php echo esc_attr( apply_filters( 'lalita_search_button', _x( 'Search', 'submit button', 'lalita' ) ) ); ?>">
</form>
