<?php
	$promoLink = 'http://supsystic.com/plugins/google-maps-plugin/?utm_source=plugin&utm_medium='. $this->tabCode;
?>
<h3><?php _e($this->tabTitle, GMP_LANG_CODE)?></h3>
<p style="overflow:none; white-space: initial; margin-bottom:15px;"><?php _e($this->tabDescription)?></p>
<a style="margin-bottom:15px;" target="_blank" class="button button-primary" href="<?php echo $promoLink?>">
<?php _e('Get it now!')?>
</a>
<a target="_blank" href="<?php echo $promoLink?>">
	<img style="max-width:800px; width:100%;" src="<?php echo frameGmp::_()->getModule('templates')->getCdnUrl() . '_assets/google-maps-easy/img/supsystic_promo/'. $this->tabCode. '.gif'?>" />
</a>
