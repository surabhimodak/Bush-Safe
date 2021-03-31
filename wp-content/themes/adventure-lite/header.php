<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div class="container">
 *
 * @package Adventure Lite
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php do_action( 'wp_body_open' ); ?>
<?php $hideslide = get_theme_mod('hide_slides', 1); ?>
<div class="header <?php if (!is_home() && is_front_page() && $hideslide == '' ){ ?>hmheader<?php } ?>">
  <div class="container">
    <div class="logo <?php if ($hideslide != '' ){ ?>logonoslide<?php } ?>">
      <?php adventure_lite_the_custom_logo(); ?>
      <div class="clear"></div>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
      <h2>
        <?php bloginfo('name'); ?>
      </h2>
      <p>
        <?php bloginfo( 'description' ); ?>
      </p>
      </a> </div>
    <div class="toggle"><a class="toggleMenu" href="#" style="display:none;">
      <?php esc_attr_e('Menu','adventure-lite'); ?>
      </a></div>
    <div class="sitenav">
      <?php wp_nav_menu( array('theme_location' => 'primary') ); ?>
    </div>
    <!-- .sitenav-->
    <div class="clear"></div>
  </div>
  <!-- container --> 
</div>
<!--.header -->