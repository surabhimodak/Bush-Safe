<?php

if(!class_exists('rtTPGInitWidget')):

	/**
	 *
	 */
	class rtTPGInitWidget
	{

		function __construct()
		{
			add_action( 'widgets_init', array($this, 'initWidget'));
		}


		function initWidget(){
			global $rtTPG;
			$rtTPG->loadWidget( $rtTPG->widgetsPath );
		}
	}


endif;