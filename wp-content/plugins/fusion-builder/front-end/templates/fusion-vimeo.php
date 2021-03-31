<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_vimeo-shortcode">
<# if ( ! values.id ) { #>
	<div class="fusion-builder-placeholder">{{{ fusionBuilderText.video_placeholder }}}</div>
<# } else { #>
	<div {{{ _.fusionGetAttributes( attr ) }}}>
		<div {{{ _.fusionGetAttributes( attrSrc ) }}}>
			<iframe src="https://player.vimeo.com/video/{{ values.id }}?autoplay=0{{ values.api_params }}" width="{{ values.width }}" height="{{ values.height }}" allowfullscreen title="vimeo{{ values.id }}"></iframe>
		</div>
	</div>
<# } #>
</script>
