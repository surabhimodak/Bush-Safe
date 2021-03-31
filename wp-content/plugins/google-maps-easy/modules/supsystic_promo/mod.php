<?php
class supsystic_promoGmp extends moduleGmp {
	private $_mainLink = '';
	private $_cdnUrl = '';
	private $_specSymbols = array(
		'from'	=> array('?', '&'),
		'to'	=> array('%', '^'),
	);
	private $_minDataInStatToSend = 20;	// At least 20 points in table shuld be present before send stats
	public function __construct($d) {
		parent::__construct($d);
		$this->getMainLink();
	}
	public function init() {
		parent::init();
		add_action('admin_footer', array($this, 'displayAdminFooter'), 9);
		if(is_admin()) {
			$this->checkStatisticStatus();
		}
		//$this->weLoveYou();
		dispatcherGmp::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		dispatcherGmp::addAction('beforeSaveOpts', array($this, 'checkSaveOpts'));
		//dispatcherGmp::addAction('addMapBottomControls', array($this, 'checkWeLoveYou'), 99);
		dispatcherGmp::addAction('discountMsg', array($this, 'getDiscountMsg'));
		add_action('admin_notices', array($this, 'checkAdminPromoNotices'));
		add_action('admin_notices', array($this, 'showUserApiKeyAdminNotice'));
	}
	function showUserApiKeyAdminNotice() {
		$class = 'supsystic-admin-notice';
		$settingsLink = frameGmp::_()->getModule('options')->getTabUrl('settings');
		$notices = array(
			'user_api_key_msg' => array(
				'class' => 'updated notice is-dismissible ' . $class,
				'html' => sprintf(__("Please, set your own Google API key in Google Maps Easy plugin <a href='%s'>Settings</a>! More info about Maps and API keys you can find <a href='%s' target='_blank'>here</a>.", GMP_LANG_CODE), $settingsLink, '//supsystic.com/google-maps-api-key/'),
				'mod' => 'options'
			)
		);

		foreach($notices as $key => $notice) {
			if(frameGmp::_()->getModule($notice['mod'])->get(substr($key, 0, -4))) {
				unset($notices[$key]);
				continue;
			}
		}
		foreach($notices as $key => $notice) {
			printf('<div class="%1$s" data-code=""><p>%2$s</p></div>', $notice['class'], $notice['html']);
		}
	}
	public function checkAdminPromoNotices() {
		if(!frameGmp::_()->isAdminPlugOptsPage())	// Our notices - only for our plugin pages for now
			return;
		$notices = array();
		// Start usage
		$startUsage = (int) frameGmp::_()->getModule('options')->get('start_usage');
		$currTime = time();
		$day = 24 * 3600;
		if($startUsage) {	// Already saved
			$rateMsg = sprintf(__("<h3>Hey, I noticed you just use %s over a week - that's awesome!</h3><p>Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.</p>", GMP_LANG_CODE), GMP_WP_PLUGIN_NAME);
			$rateMsg .= '<p><a href="https://wordpress.org/support/view/plugin-reviews/google-maps-easy?rate=5#postform" target="_blank" class="button button-primary" data-statistic-code="done">'. __('Ok, you deserve it', GMP_LANG_CODE). '</a>
			<a href="#" class="button" data-statistic-code="later">'. __('Nope, maybe later', GMP_LANG_CODE). '</a>
			<a href="#" class="button" data-statistic-code="hide">'. __('I already did', GMP_LANG_CODE). '</a></p>';
			$enbPromoLinkMsg = sprintf(__("<h3>More than eleven days with our %s plugin - Congratulations!</h3>", GMP_LANG_CODE), GMP_WP_PLUGIN_NAME);
			$enbPromoLinkMsg .= __("<p>On behalf of the entire <a href='https://supsystic.com/' target='_blank'>supsystic.com</a> company I would like to thank you for been with us, and I really hope that our software helped you.</p>", GMP_LANG_CODE);
			$enbPromoLinkMsg .= __("<p>And today, if you want, - you can help us. This is really simple - you can just add small promo link to our site under your maps. This is small step for you, but a big help for us! Sure, if you don't want - just skip this and continue enjoy our software!</p>", GMP_LANG_CODE);
			$enbPromoLinkMsg .= '<p><a href="#" class="button button-primary" data-statistic-code="done">'. __('Ok, you deserve it', GMP_LANG_CODE). '</a>
			<a href="#" class="button" data-statistic-code="later">'. __('Nope, maybe later', GMP_LANG_CODE). '</a>
			<a href="#" class="button" data-statistic-code="hide">'. __('Skip', GMP_LANG_CODE). '</a></p>';
			$checkOtherPlugins = '<p>'
				. sprintf(__("Check out <a href='%s' target='_blank' class='button button-primary' data-statistic-code='hide'>our other Plugins</a>! Years of experience in WordPress plugins developers made that list unbreakable!", GMP_LANG_CODE), frameGmp::_()->getModule('options')->getTabUrl('featured-plugins'))
			. '</p>';
			$notices = array(
				'rate_msg' => array('html' => $rateMsg, 'show_after' => 7 * $day),
				'enb_promo_link_msg' => array('html' => $enbPromoLinkMsg, 'show_after' => 11 * $day),
				'check_other_plugs_msg' => array('html' => $checkOtherPlugins, 'show_after' => 1 * $day),
			);
			// Wait for next week - when icons will be ready
			if(!class_exists('frameUms')) {
				$ultimateMapsInstallUrl = admin_url('plugin-install.php?tab=search&type=term&s=Ultimate+Maps+by+Supsystic');
				$ultimateMapsMsg = '<p>'.
						sprintf(__("Tired from Google Maps and it's pricings? We developed <b>Free Maps alternative for You</b> - <a href='%s' target='_blank'>Ultimate Maps by Supsystic</a>! Just try it in <a href='%s' target='_blank'>few clicks</a>!", GMP_LANG_CODE), $ultimateMapsInstallUrl, $ultimateMapsInstallUrl)
						. '</p>';
				$notices['ultimate_maps_promo'] = array('html' => $ultimateMapsMsg, 'show_after' => 1 * $day);
			}
			foreach($notices as $nKey => $n) {
				if($currTime - $startUsage <= $n['show_after']) {
					unset($notices[ $nKey ]);
					continue;
				}
				$done = (int) frameGmp::_()->getModule('options')->get('done_'. $nKey);
				if($done) {
					unset($notices[ $nKey ]);
					continue;
				}
				$hide = (int) frameGmp::_()->getModule('options')->get('hide_'. $nKey);
				if($hide) {
					unset($notices[ $nKey ]);
					continue;
				}
				$later = (int) frameGmp::_()->getModule('options')->get('later_'. $nKey);
				if($later && ($currTime - $later) <= 2 * $day) {	// remember each 2 days
					unset($notices[ $nKey ]);
					continue;
				}
				if($nKey == 'enb_promo_link_msg' && (int)frameGmp::_()->getModule('options')->get('add_love_link')) {
					unset($notices[ $nKey ]);
					continue;
				}
			}
		} else {
			frameGmp::_()->getModule('options')->getModel()->save('start_usage', $currTime);
		}
		if(!empty($notices)) {
			if(isset($notices['rate_msg']) && isset($notices['enb_promo_link_msg']) && !empty($notices['enb_promo_link_msg'])) {
				unset($notices['rate_msg']);	// Show only one from those messages
			}
			$html = '';
			foreach($notices as $nKey => $n) {
				$this->getModel()->saveUsageStat($nKey. '.'. 'show', true);
				$html .= '<div class="updated notice is-dismissible supsystic-admin-notice" data-code="'. $nKey. '">'. $n['html']. '</div>';
			}
			echo $html;
		}
	}
	public function addAdminTab($tabs) {
		$tabs['overview'] = array(
			'label' => __('Overview', GMP_LANG_CODE), 'callback' => array($this, 'getOverviewTabContent'), 'fa_icon' => 'fa-info', 'sort_order' => 5,
		);
		$tabs['featured-plugins'] = array(
			'label' => __('Featured Plugins', GMP_LANG_CODE), 'callback' => array($this, 'showFeaturedPluginsPage'), 'fa_icon' => 'fa-heart', 'sort_order' => 99,
		);
		return $tabs;
	}
	public function getOverviewTabContent() {
		return $this->getView()->getOverviewTabContent();
	}
	// We used such methods - _encodeSlug() and _decodeSlug() - as in slug wp don't understand urlencode() functions
	private function _encodeSlug($slug) {
		return str_replace($this->_specSymbols['from'], $this->_specSymbols['to'], $slug);
	}
	private function _decodeSlug($slug) {
		return str_replace($this->_specSymbols['to'], $this->_specSymbols['from'], $slug);
	}
	public function decodeSlug($slug) {
		return $this->_decodeSlug($slug);
	}
	public function modifyMainAdminSlug($mainSlug) {
		$firstTimeLookedToPlugin = !installerGmp::isUsed();
		if($firstTimeLookedToPlugin) {
			$mainSlug = $this->_getNewAdminMenuSlug($mainSlug);
		}
		return $mainSlug;
	}
	private function _getWelcomMessageMenuData($option, $modifySlug = true) {
		return array_merge($option, array(
			'page_title'	=> __('Welcome to Supsystic Secure', GMP_LANG_CODE),
			'menu_slug'		=> ($modifySlug ? $this->_getNewAdminMenuSlug( $option['menu_slug'] ) : $option['menu_slug'] ),
			'function'		=> array($this, 'showWelcomePage'),
		));
	}
	public function addWelcomePageToMenus($options) {
		$firstTimeLookedToPlugin = !installerGmp::isUsed();
		if($firstTimeLookedToPlugin) {
			foreach($options as $i => $opt) {
				$options[$i] = $this->_getWelcomMessageMenuData( $options[$i] );
			}
		}
		return $options;
	}
	private function _getNewAdminMenuSlug($menuSlug) {
		// We can't use "&" symbol in slug - so we used "|" symbol
		$newSlug = $this->_encodeSlug(str_replace('admin.php?page=', '', $menuSlug));
		return 'welcome-to-'. frameGmp::_()->getModule('adminmenu')->getMainSlug(). '|return='. $newSlug;
	}
	public function addWelcomePageToMainMenu($option) {
		$firstTimeLookedToPlugin = !installerGmp::isUsed();
		if($firstTimeLookedToPlugin) {
			$option = $this->_getWelcomMessageMenuData($option, false);
		}
		return $option;
	}
	public function showWelcomePage() {
		$this->getView()->showWelcomePage();
	}
	public function displayAdminFooter() {
		if(frameGmp::_()->isAdminPlugPage()) {
			$this->getView()->displayAdminFooter();
		}
	}
	private function _preparePromoLink($link, $ref = '') {
		if(empty($ref))
			$ref = 'user';
		$link .= '?ref='. $ref;
		return $link;
	}
	public function weLoveYou() {
		if(!frameGmp::_()->getModule(implode('', array('l','ic','e','ns','e')))) {
			//
		}
	}
	/**
	 * Public shell for private method
	 */
	public function preparePromoLink($link, $ref = '') {
		return $this->_preparePromoLink($link, $ref);
	}
	public function checkStatisticStatus(){
		$canSend = (int) frameGmp::_()->getModule('options')->get('send_stats');
		if($canSend) {
			$this->getModel()->checkAndSend();
		}
	}
	public function getMinStatSend() {
		return $this->_minDataInStatToSend;
	}
	public function getMainLink() {
		if(empty($this->_mainLink)) {
			$affiliateQueryString = '';
			$this->_mainLink = 'http://supsystic.com/plugins/google-maps-plugin/' . $affiliateQueryString;
		}
		return $this->_mainLink ;
	}
	public function getContactFormFields() {
		$fields = array(
            'name' => array('label' => __('Name', GMP_LANG_CODE), 'valid' => 'notEmpty', 'html' => 'text'),
			'email' => array('label' => __('Email', GMP_LANG_CODE), 'html' => 'email', 'valid' => array('notEmpty', 'email'), 'placeholder' => 'example@mail.com', 'def' => get_bloginfo('admin_email')),
			'website' => array('label' => __('Website', GMP_LANG_CODE), 'html' => 'text', 'placeholder' => 'http://example.com', 'def' => get_bloginfo('url')),
			'subject' => array('label' => __('Subject', GMP_LANG_CODE), 'valid' => 'notEmpty', 'html' => 'text'),
            'category' => array('label' => __('Topic', GMP_LANG_CODE), 'valid' => 'notEmpty', 'html' => 'selectbox', 'options' => array(
				'plugins_options' => __('Plugin options', GMP_LANG_CODE),
				'bug' => __('Report a bug', GMP_LANG_CODE),
				'functionality_request' => __('Require a new functionality', GMP_LANG_CODE),
				'other' => __('Other', GMP_LANG_CODE),
			)),
			'message' => array('label' => __('Message', GMP_LANG_CODE), 'valid' => 'notEmpty', 'html' => 'textarea', 'placeholder' => __('Hello Supsystic Team!', GMP_LANG_CODE)),
        );
		foreach($fields as $k => $v) {
			if(isset($fields[ $k ]['valid']) && !is_array($fields[ $k ]['valid']))
				$fields[ $k ]['valid'] = array( $fields[ $k ]['valid'] );
		}
		return $fields;
	}
	public function isPro() {
		return frameGmp::_()->getModule('add_map_options') ? true : false;
	}
	public function generateMainLink($params = '') {
		$mainLink = $this->getMainLink();
		if(!empty($params)) {
			return $mainLink. (strpos($mainLink , '?') ? '&' : '?'). $params;
		}
		return $mainLink;
	}
	public function getLoveLink() {
		$title = 'WordPress Google Maps Plugin';
		return '<a title="'. $title. '" style="border: none; color: #26bfc1 !important; font-size: 9px; display: block; float: right;" href="'. $this->generateMainLink('utm_source=plugin&utm_medium=love_link&utm_campaign=googlemaps'). '" target="_blank">'
			. $title
			. '</a>';
	}
	public function checkSaveOpts($newValues) {
		$loveLinkEnb = (int) frameGmp::_()->getModule('options')->get('add_love_link');
		$loveLinkEnbNew = isset($newValues['opt_values']['add_love_link']) ? (int) $newValues['opt_values']['add_love_link'] : 0;
		if($loveLinkEnb != $loveLinkEnbNew) {
			$this->getModel()->saveUsageStat('love_link.'. ($loveLinkEnbNew ? 'enb' : 'dslb'));
		}
	}
	public function checkWeLoveYou() {
		if(frameGmp::_()->getModule('options')->get('add_love_link')) {
			echo $this->getLoveLink();
		}
	}
	public function addPromoMapTabs() {
		$tabs = array();
		$descirption['figures'] = 'With Figures Feature, you can create polygon, polyline and circle shapes on your map with an easy to use interface. Provide a Figure title and description, using text, photos, videos and links for the overlay.';
		$descirption['heatmap'] = 'Heatmap is an extremely common map visualization type, especially prevalent in weather, travel, fitness and social areas. Use the points to display the relative density of the points on the map as a smoothly varying set of colours, depend on low and high density.';
		if(!$this->isPro()) {
			$tabs['gmpShapeTab'] = array(
				'label' => __('Figures', GMP_LANG_CODE),
				'content' => $this->getView()->getPromoTabContent('shapes&utm_campaign=googlemaps', 'Figures', $descirption['figures']),
				'promo' => true,
			);
			$tabs['gmpHeatmapTab'] = array(
				'label' => __('Heatmap', GMP_LANG_CODE),
				'content' => $this->getView()->getPromoTabContent('heatmap&utm_campaign=googlemaps', 'Heatmap Layer', $descirption['heatmap']),
				'promo' => true,
			);
		}
		return $tabs;
	}
	public function showFeaturedPluginsPage() {
		return $this->getView()->showFeaturedPluginsPage();
	}
	public function getDiscountMsg() {
		if($this->isPro()
			&& frameGmp::_()->getModule('options')->getActiveTab() == 'license'
			&& frameGmp::_()->getModule('license')
			&& frameGmp::_()->getModule('license')->getModel()->isActive()
		) {
			$proPluginsList = array(
				'ultimate-maps-by-supsystic-pro', 'newsletters-by-supsystic-pro', 'contact-form-by-supsystic-pro', 'live-chat-pro',
				'digital-publications-supsystic-pro', 'coming-soon-supsystic-pro', 'price-table-supsystic-pro', 'tables-generator-pro',
				'social-share-pro', 'popup-by-supsystic-pro', 'supsystic_slider_pro', 'supsystic-gallery-pro', 'google-maps-easy-pro',
				'backup-supsystic-pro',
			);
			$activePluginsList = get_option('active_plugins', array());
			$activeProPluginsCount = 0;
			foreach($activePluginsList as $actPl) {
				foreach($proPluginsList as $proPl) {
					if(strpos($actPl, $proPl) !== false) {
						$activeProPluginsCount++;
					}
				}
			}
			if($activeProPluginsCount === 1) {
				$buyLink = $this->getDiscountBuyUrl();
				$this->getView()->getDiscountMsg($buyLink);
			}
		}
	}
	public function getDiscountBuyUrl() {
		$license = frameGmp::_()->getModule('license')->getModel()->getCredentials();
		$license['key'] = md5($license['key']);
		$license = urlencode(base64_encode(implode('|', $license)));
		$plugin_code = 'google_maps_easy_pro';
		return 'http://supsystic.com/?mod=manager&pl=lms&action=applyDiscountBuyUrl&plugin_code='. $plugin_code. '&lic='. $license;
	}
}
