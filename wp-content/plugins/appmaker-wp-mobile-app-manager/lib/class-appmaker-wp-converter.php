<?php

/**
 * Class APPMAKER_WP_Converter
 */
class APPMAKER_WP_Converter {
	static $key = 1;

	/**
	 * @param $data_obj
	 * @param $key
	 *
	 * @return array
	 */
	public static function convert_inAppPage_data( $data_obj, $key ) {
		if ( empty( $data_obj ) ) {
			return array();
		}

		if ( isset( $data_obj->type ) && 'TABBED' === $data_obj->type ) {
			return self::convertTabbedInAppPage( $data_obj, $key );
		}

		$app_value            = array();
		$app_value['id']      = $key;
		$app_value['title']   = html_entity_decode( $data_obj->title );
		$app_value['widgets'] = array();
		if ( class_exists( 'APPMAKER_WC' ) && ! isset( APPMAKER_WC::$api->APPMAKER_WC_REST_Products_Controller ) ) {
			$product_controller = new APPMAKER_WC_REST_Products_Controller();
		} elseif ( class_exists( 'APPMAKER_WC' ) ) {
			$product_controller = APPMAKER_WC::$api->APPMAKER_WC_REST_Products_Controller;
		}

		foreach ( $data_obj->widgets as $value_obj ) {
			$skip         = false;
			$skip_shuffle = false;

			$value       = clone( $value_obj );
			$value->skip = false;
			$widget      = array(
				'type'  => $value->type,
				'title' => $value->title,
				'data'  => array(),
			);

			if ( 'category_list' === $widget['type'] ) {
				$data        = $value->data[0]->data;
				$value->skip = true;
				$categories  = get_categories(
					array(
						'type'    => 'post',
						'orderby' => 'name',
						'parent'  => ! empty( $data->parent->value ) ? $data->parent->value : 0,
					)
				);
				switch ( $data->type ) {
					case 'MENU':
					default:
						$widget['type'] = 'menu';
						$count          = 1;
						foreach ( $categories as $category ) {
							$widget['data'][] = array(
								'title'  => wp_specialchars_decode( $category->name ),
								'action' => array(
									'type'   => 'OPEN_IN_APP_PAGE',
									'params' => array( 'id' => 'wp/posts?categories=' . $category->term_id ),
								),
							);
							if ( $count ++ >= $data->limit ) {
								break;
							}
						}
						break;
				}
			}

			if ( 'pre_build_post_list' === $widget['type'] ) {
				$app_value['dynamic'] = true;
				$widget['dynamic']    = true;

			}

			if ( 'pre_build_product_scroller' === $widget['type'] ) {
				switch ( $value->data[0]->data->action_value->value ) {
					case 'FEATURED':
						$value->data = APPMAKER_WC_Helper::wc_get_featured_product_ids();
						break;
					case 'SALE':
						$value->data = APPMAKER_WC_Helper::wc_get_product_ids_on_sale();
						break;
					case 'RECENT':
						$value->data  = APPMAKER_WC_Helper::get_recent_products();
						$skip_shuffle = true;
						break;
					case 'BEST_SELLING':
						$value->data = APPMAKER_WC_Helper::get_best_selling_products();
						break;
					case 'TOP_RATED':
						$value->data = APPMAKER_WC_Helper::get_top_rated_products();
						break;
					case 'CATEGORY':
						$value->data = APPMAKER_WC_Helper::get_products_by_tax( 'product_cat', $value->data[0]->data->id->value );
						break;
					case 'TAG':
						$value->data = APPMAKER_WC_Helper::get_products_by_tax( 'product_tag', $value->data[0]->data->id->value );
						break;
					default:
						$value->data = array();
				}
				if ( empty( $value->data ) ) {
					$skip = true;
				} else {
					if ( ! $skip_shuffle ) {
						shuffle( $value->data );
					}
					$value->data = array_slice( $value->data, 0, 15 );
				}
			}

			foreach ( $value->data as $value_data ) {
				$data = false;
				if ( 'post' === $widget['type'] ) {
					if ( ! isset( $value_data->data->post_id->value ) && ( is_string( $value_data ) || is_numeric( $value_data ) ) ) {
						$post = get_post( $value_data );
					} else {
						$post = get_post( $value_data->data->post_id->value );
					}
					if ( ! empty( $post ) ) {
						$data               = APPMAKER_WP_Helper::get_post_widget( $post, $value_data->data->template->value );
						$widget['title']    = $post->post_title;
						$widget['template'] = $value_data->data->template->value;
					}
				} elseif ( 'product_scroller' === $widget['type'] ) {
					if ( ! isset( $value_data->data->action_value->value ) && ( is_string( $value_data ) || is_numeric( $value_data ) ) ) {
						$product = APPMAKER_WC_Helper::get_product( $value_data );
					} else {
						$product = APPMAKER_WC_Helper::get_product( $value_data->data->action_value->value );

					}
					if ( ! empty( $product ) ) {
						$data = $product_controller->get_product_data( $product );
					}
				} elseif ( 'pre_build_product_scroller' === $widget['type'] ) {
					$product = APPMAKER_WC_Helper::get_product( $value_data );
					if ( ! empty( $product ) ) {
						$data = $product_controller->get_product_data( $product );
					}
				} elseif ( 'html' === $widget['type'] ) {
					$data['html'] = wpautop( do_shortcode( $value_data->data->html->value ) );
				} elseif ( true !== $value->skip ) {
					$data = array();
					foreach ( $value_data->data as $data_key => $item_data ) {
						switch ( $data_key ) {
							case 'image':
								$data['image']      = self::getImageUrl(
									$value_data->data->image
								);
								$data['dimensions'] = self::getImageDimensions(
									$value_data->data->image
								);
								break;
							case 'action':
							case 'action_value':
								if ( empty( $data->action ) ) {
									$data['action'] = array(
										'type'   => self::get_type(
											self::getValue( $value_data->data->action )
										),
										'params' => self::get_params(
											self::getValue( $value_data->data->action ),
											self::getValue( $value_data->data->action_value )
										),
									);
								}
								break;
							default:
								$data[ $data_key ] = self::getValue( $item_data );
						}
					}
				}
				if ( false !== $data ) {
					$widget['data'][] = $data;
				}
			}
			if ( ! empty( $value->data_main ) ) {
				$widget['meta'] = self::parseMainData( $value->data_main );
				$widget['meta'] = array();
				foreach ( $value->data_main as $value_key => $value_data ) {
					$value = self::getValue( $value_data );
					if ( preg_match( '/(.*)_action$/i', $value_key, $matches ) ) {
						if ( ! isset( $widget['meta'][ $value_key ] ) ) {
							$widget['meta'][ $value_key ]['type'] = array(
								'type'   => '',
								'params' => '',
							);
						}
						if ( is_string( $value ) ) {
							$widget['meta'][ $value_key ]['original_type'] = $value;
						} else {
							$widget['meta'][ $value_key ]['original_type'] = isset( $value->action->value ) ? $value->action->value : $value->action->id;
						}
						$widget['meta'][ $value_key ]['type'] = self::get_type( $value );
						if ( isset( $widget['meta'][ $value_key ]['params'] ) ) {
							$widget['meta'][ $value_key ]['params'] = self::get_params( $widget['meta'][ $value_key ]['original_type'], $widget['meta'][ $value_key ]['params'] );
						}
					} elseif ( preg_match( '/(.*)_action_value$/i', $value_key, $matches ) ) {
						$action_key = $matches[1] . '_action';
						if ( ! isset( $widget['meta'][ $action_key ]['type'] ) ) {
							$widget['meta'][ $action_key ]['params'] = $value;
						} else {
							$widget['meta'][ $action_key ]['params'] = self::get_params( $widget['meta'][ $action_key ]['original_type'], $value );
						}
					} else {
						$widget['meta'][ $value_key ] = $value;
					}
				}
			}
			if ( 'pre_build_product_scroller' === $widget['type'] ) {
				$widget['type'] = 'product_scroller';
			}

			if ( 'pre_build_post_list' === $widget['type'] ) {
				$widget['type'] = 'post_list';
			}
			if ( true !== $skip && ! empty( $widget['data'] ) ) {
				$app_value['widgets'][] = $widget;
			}
		}
		$app_value['hash'] = md5( serialize( $app_value ) );

		return ( $app_value );
	}

	/**
	 * @param $widget
	 *
	 * @return array
	 */
	public static function convert_dynamic_widget( $widget ) {
		$rendered_widget = array(
			'title' => $widget['title'],
			'type'  => $widget['type'],
			'data'  => array(),
		);
		if ( $widget['type'] === 'post_list' ) {
			$limit = isset($widget['meta']['limit']) ? $widget['meta']['limit'] : 10;
			$offset = isset($widget['meta']['offset']) ? $widget['meta']['offset'] : 0;
			switch ( $widget['data'][0]['list_type'] ) {
				case 'RECENT':
					$posts = APPMAKER_WP_Helper::get_recent_posts($limit, $offset);
					break;
				case 'CATEGORY':
					$posts = APPMAKER_WP_Helper::get_posts_by_tax( 'category', $widget['data'][0]['id'], $limit, $offset );
					break;
				case 'TAG':
					$posts = APPMAKER_WP_Helper::get_posts_by_tax( 'post_tag', $widget['data'][0]['id'], $limit, $offset );
					break;
			}
			if ( empty( $posts ) ) {
				$rendered_widget['skip'] = true;
			} else {
				$rendered_widget['template'] = $widget['meta']['template'];
				$list_template = isset( $widget['meta']['item_template'] ) ? $widget['meta']['item_template'] : 'template-1';				
				$posts                       = array_slice( $posts, 0, 15 );
				foreach ( $posts as $post_id ) {
					$post = get_post( $post_id );
					if ( $rendered_widget['template'] === 'post-slider-1' && ! has_post_thumbnail( $post ) ) {
						continue;
					}
					if ( ! empty( $post ) ) {
						$rendered_widget['data'][] = APPMAKER_WP_Helper::get_post_widget( $post, $list_template );
					}
				}
				if ( empty( $rendered_widget['data'] ) ) {
					$rendered_widget['skip'] = true;
				}
			}
		}

		return $rendered_widget;
	}

	protected static function parseMainData( $data_main ) {
		$meta = array();
		foreach ( $data_main as $value_key => $value_data ) {
			$value = self::getValue( $value_data );
			if ( preg_match( '/(.*)_action$/i', $value_key, $matches ) ) {
				if ( ! isset( $meta[ $value_key ] ) ) {
					$meta[ $value_key ]['type'] = array(
						'type'   => '',
						'params' => '',
					);
				}
				if ( is_string( $value ) ) {
					$meta[ $value_key ]['original_type'] = $value;
				} else {
					$meta[ $value_key ]['original_type'] = isset( $value->action->value ) ? $value->action->value : $value->action->id;
				}
				$meta[ $value_key ]['type'] = self::get_type( $value );
				if ( isset( $meta[ $value_key ]['params'] ) ) {
					$meta[ $value_key ]['params'] = self::get_params( $meta[ $value_key ]['original_type'], $meta[ $value_key ]['params'] );
				}
			} elseif ( preg_match( '/(.*)_action_value$/i', $value_key, $matches ) ) {
				$action_key = $matches[1] . '_action';
				if ( ! isset( $meta[ $action_key ]['type'] ) ) {
					$meta[ $action_key ]['params'] = $value;
				} else {
					$meta[ $action_key ]['params'] = self::get_params( $meta[ $action_key ]['original_type'], $value );
				}
			} else {
				$meta[ $value_key ] = $value;
			}
		}

		return $meta;
	}

	protected static function convertTabbedInAppPage( $data_obj, $key ) {
		$app_value                  = array();
		$app_value['id']            = $key;
		$app_value['type']          = 'tab';
		$app_value['title']         = $data_obj->title;
		$app_value['tabs']          = array();
		$widget                     = $data_obj->widgets[0];
		$app_value['tabBarOptions'] = self::parseMainData(
			$widget->data_main
		);
		$app_value['tabBarOptions'] = array_merge($app_value['tabBarOptions'], array(
			"labelStyle" =>  array(
			  "fontSize" =>  16,
			  //"fontFamily" =>  'AppFont',
			),
			"upperCaseLabel" =>  false,
			"tabStyle" => array(
			  "alignItems" =>  'flex-start',
			  "width" =>  'auto',
			),
			"inactiveTintColor" =>  '#BBBBBB',
			"activeTintColor" =>  '#FFFFFF',
			"style" => array(
			  "backgroundColor" =>  '#000000',
			),
			"indicatorStyle" =>  array(
			  "backgroundColor" =>  'rgba(0,0,0,0)',
			),
			"scrollEnabled" =>  true,
		));
		foreach ( $widget->data as $tab_key => $tab ) {
			$app_value['tabs'][] = array(
				'id'     => $tab_key,
				'title'  => self::getValue( $tab->data->title ),
				'source' => isset( $tab->data->action ) ? self::get_params( $tab->data->action->value, $tab->data->action_value->value ) : array( 'id' => 'home' ),
				'icon'   => array(
					'focused' => isset( $tab->data->icon ) ? self::getImageUrl( $tab->data->icon ) : '',
					'normal'  => isset( $tab->data->icon ) ? self::getImageUrl( $tab->data->icon ) : '',
				),
			);
		}

		$date = new DateTime();

		$app_value['hash'] = $date->getTimestamp();

		return $app_value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected static function getValue( $value ) {
		if ( is_string( $value ) ) {
			return $value;
		} else {
			return $value->value;
		}
	}

	protected static function getImageUrl( $image ) {
		if ( is_string( $image ) ) {
			return $image;
		} elseif ( is_array( $image->value ) && isset( $image->value['url'] ) ) {
			return $image->value['url'];
		} elseif ( isset( $image->value->url ) ) {
			return $image->value->url;
		} else {
			return $image->value;
		}
	}

	protected static function getImageDimensions( $image ) {
		if ( empty( $image ) ) {
			return false;
		} elseif ( is_array( $image->value ) && isset( $image->value['meta'] ) ) {
			return $image->value['meta'];
		} elseif ( isset( $image->value->meta ) ) {
			return $image->value->meta;
		} else {
			return false;
		}
	}

	public static function get_params( $action, $action_value ) {
		switch ( $action ) {
			case 'OPEN_URL': {
				$params = array( 'url' => $action_value );
				break;
			}
			case 'OPEN_IN_WEB_VIEW' : {
				$params = array( 'url' => $action_value );
				break;
			}
			case 'LIST_POST' : {
				$params = array( 'id' => 'wp/posts?categories=' . $action_value );
				break;

			}
			case 'LIST_POST_TAG' : {
				$params = array( 'id' => 'wp/posts?tags=' . $action_value );
				break;
			}
			case 'LIST_PRODUCT' : {
				$params = array( 'category' => $action_value );
				break;
			}
			case 'LIST_PRODUCT_TAG' : {
				$params = array( 'tag' => $action_value );
				break;
			}
			case 'OPEN_IN_APP_PAGE' : {
				$params = array( 'id' => $action_value );
				break;

			}
			case 'OPEN_POST' : {
				$params = array( 'id' => 'wp/posts/' . $action_value );
				break;
			}
			case 'OPEN_PRODUCT' : {
				$params = array( 'id' => $action_value );
				break;
			}
			default : {
				$params = array();
				break;

			}
		}

		return $params;
	}

	public static function convert_navMenu_data( $data ) {
		$app_value         = array();
		$app_value['type'] = 'menu';
		$app_value['data'] = self::convert_nav_menu_data_item_nested_menu( $data );
		$app_value['hash'] = md5( serialize( $app_value ) );
		return array(
			'id'      => 'menu',
			'title'   => 'Menu',
			'widgets' => array( $app_value ),
		);
	}

	public static function convert_nav_menu_data_item_nested_menu( $data ) {
		$return = array();
		foreach ( $data as $value ) {
			if ( isset( $value->children ) ) {
				$nodes = $value->children;
			} elseif ( isset( $value->nodes ) ) {
				$nodes = $value->nodes;
			} else {
				$nodes = array();
			}

			$menu = array(
				'id'         => self::$key ++,
				'title'      => html_entity_decode( $value->title ),
				'image'      => self::getImageUrl( $value->icon ),
				'dimensions' => self::getImageDimensions( $value->icon ),
				'type'       => isset( $value->type->value ) ? $value->type->value : $value->type->id,
				'action'     => array(),
				'data'       => self::convert_nav_menu_data_item_nested_menu( $nodes ),
			);
			if ( empty( $menu['data'] ) ) {
				unset( $menu['data'] );
			}
			if ( 'title' !== $menu['type'] && empty( $menu['data'] ) ) {
				if ( is_string( $value->action_value ) ) {
					$val = $value->action_value;
				} else {
					$val = isset( $value->action_value->value ) ? $value->action_value->value : $value->action_value->id;
				}
				$menu['action'] = array(
					'type'   => self::get_type( $value ),
					'params' => self::get_params( isset( $value->action->value ) ? $value->action->value : $value->action->id, $val ),
				);
			} else {
				unset( $menu['action'] );
			}
			$return[] = $menu;
		}

			return $return;
	}


	public static function convert_navMenu_data_item( $data ) {
		$return = array();
		foreach ( $data as $value ) {
			if ( isset( $value->children ) ) {
				$nodes = $value->children;
			} elseif ( isset( $value->nodes ) ) {
				$nodes = $value->nodes;
			} else {
				$nodes = array();
			}

			$menu = array(
				'id'         => self::$key ++,
				'title'      => html_entity_decode( $value->title ),
				'icon'       => self::getImageUrl( $value->icon ),
				'dimensions' => self::getImageDimensions( $value->icon ),
				'type'       => isset( $value->type->value ) ? $value->type->value : $value->type->id,
				'action'     => array(),
				'nodes'      => self::convert_navMenu_data_item( $nodes ),
			);
			if ( 'title' !== $menu['type'] ) {
				if ( is_string( $value->action_value ) ) {
					$val = $value->action_value;
				} else {
					$val = isset( $value->action_value->value ) ? $value->action_value->value : $value->action_value->id;
				}
				$menu['action'] = array(
					'type'   => self::get_type( $value ),
					'params' => self::get_params( isset( $value->action->value ) ? $value->action->value : $value->action->id, $val ),
				);
			}
			$return[] = $menu;
		}

		return $return;
	}

	public static function get_type( $value ) {
		if ( is_string( $value ) ) {
			$type = $value;
		} else {
			$type = isset( $value->action->value ) ? $value->action->value : $value->action->id;
		}
		switch ( $type ) {
			case 'LIST_PRODUCT_TAG':
				return 'LIST_PRODUCT';
			case 'LIST_POST':
			case 'LIST_POST_TAG':
				return 'OPEN_IN_APP_PAGE';
			case 'OPEN_POST':
				return 'OPEN_IN_APP_PAGE';
			case 'OPEN_IN_WEB_VIEW':
				return 'OPEN_WEBVIEW';
			default:
				return $type;
		}
	}

}
