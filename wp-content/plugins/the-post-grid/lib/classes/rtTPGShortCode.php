<?php

if (!class_exists('rtTPGShortCode')):

    class rtTPGShortCode
    {

        private $scA = array();

        function __construct() {
            add_shortcode('the-post-grid', array($this, 'the_post_grid_short_code'));
            add_action('wp_ajax_tpgPreviewAjaxCall', array($this, 'the_post_grid_short_code'));
        }

        function register_sc_scripts() {
            $iso = false;
            foreach ($this->scA as $sc) {
                if (isset($sc) && is_array($sc)) {
                    if ($sc['isIsotope']) {
                        $iso = true;
                    }
                }
            }
            if (count($this->scA)) {
                if ($iso) {
                    wp_enqueue_script('rt-isotope-js');
                }
                wp_enqueue_style('rt-fontawsome');
                wp_enqueue_script('rt-actual-height-js');
                wp_enqueue_script('rt-tpg');
                $nonce = wp_create_nonce(rtTPG()->nonceText());
                wp_localize_script('rt-tpg', 'rttpg',
                    array(
                        'nonceID' => rtTPG()->nonceId(),
                        'nonce'   => $nonce,
                        'ajaxurl' => admin_url('admin-ajax.php')
                    ));
            }
        }

        function the_post_grid_short_code($atts = array(), $content = null) {
            $error = true;
            $html = $msg = null;
            $preview = isset($_REQUEST['sc_id']) ? absint($_REQUEST['sc_id']) : 0;
            $arg = array();
            $atts = shortcode_atts(array(
                'id' => null
            ), $atts, 'the-post-grid');
            $scID = $atts['id'];
            if ((!$preview && $scID && !is_null(get_post($scID))) || ($preview && rtTPG()->verifyNonce())) {
                $rand = mt_rand();
                $layoutID = "rt-tpg-container-" . $rand;
                $pagination = false;

                if ($preview) {
                    $error = false;
                    $scMeta = $_REQUEST;
                    $layout = isset($scMeta['layout']) ? $scMeta['layout'] : 'layout1';
                    $col = isset($scMeta['column']) ? intval($scMeta['column']) : 4;

                    $fImg = isset($scMeta['featured_image']) ? true : false;
                    $fImgSize = isset($scMeta['featured_image_size']) ? $scMeta['featured_image_size'] : "medium";
                    $mediaSource = isset($scMeta['media_source']) ? $scMeta['media_source'] : "feature_image";
                    $arg['excerpt_type'] = isset($scMeta['tgp_excerpt_type']) ? $scMeta['tgp_excerpt_type'] : 'character';
                    $arg['excerpt_limit'] = isset($scMeta['excerpt_limit']) ? absint($scMeta['excerpt_limit']) : 0;
                    $arg['excerpt_more_text'] = isset($scMeta['tgp_excerpt_more_text']) ? $scMeta['tgp_excerpt_more_text'] : null;
                    $arg['title_limit_type'] = isset($scMeta['tpg_title_limit_type']) ? $scMeta['tpg_title_limit_type'] : 'character';
                    $arg['title_limit'] = isset($scMeta['tpg_title_limit']) ? absint($scMeta['tpg_title_limit']) : 0;
                    $arg['read_more_text'] = isset($scMeta['tgp_read_more_text']) && !empty($scMeta['tgp_read_more_text']) ? $scMeta['tgp_read_more_text'] : __('Read More',
                        'the-post-grid');
                    $arg['show_all_text'] = isset($scMeta['tpg_show_all_text']) && !empty($scMeta['tpg_show_all_text']) ? $scMeta['tpg_show_all_text'] : __('Show all',
                        'the-post-grid-pro');
                    $postType = isset($scMeta['tpg_post_type']) ? $scMeta['tpg_post_type'] : null;
                    $post__in = isset($scMeta['post__in']) ? $scMeta['post__in'] : null;
                    $post__not_in = isset($scMeta['post__not_in']) ? $scMeta['post__not_in'] : null;
                    $limit = isset($scMeta['limit']) && !empty($scMeta['limit']) ? ($scMeta['limit'] == -1 ? 10000000 : (int)$scMeta['limit']) : 10000000;
                    $pagination = isset($scMeta['pagination']) ? $scMeta['pagination'] : false;
                    $posts_per_page = isset($scMeta['posts_per_page']) ? intval($scMeta['posts_per_page']) : $limit;
                    $order_by = isset($scMeta['order_by']) ? $scMeta['order_by'] : null;
                    $order = isset($scMeta['order']) ? $scMeta['order'] : null;
                    $s = isset($scMeta['s']) ? $scMeta['s'] : null;
                    $isotope_filter = isset($scMeta['isotope_filter']) ? $scMeta['isotope_filter'] : null;
                    $link = empty($scMeta['link_to_detail_page']) || (!empty($scMeta['link_to_detail_page']) && $scMeta['link_to_detail_page'] === "yes") ? true : false;
                    $link_target = !empty($scMeta['link_target']) ? $scMeta['link_target'] : null;
                } else {
                    $scMeta = get_post_meta($scID);
                    $scMeta['sc_id'] = $scID;
                    $layout = isset($scMeta['layout'][0]) ? $scMeta['layout'][0] : 'layout1';
                    $col = isset($scMeta['column'][0]) ? intval($scMeta['column'][0]) : 4;

                    $fImg = isset($scMeta['featured_image'][0]) ? true : false;
                    $fImgSize = isset($scMeta['featured_image_size'][0]) ? $scMeta['featured_image_size'][0] : "medium";
                    $mediaSource = isset($scMeta['media_source'][0]) ? $scMeta['media_source'][0] : "feature_image";
                    $arg['excerpt_type'] = isset($scMeta['tgp_excerpt_type'][0]) ? $scMeta['tgp_excerpt_type'][0] : 'character';
                    $arg['excerpt_limit'] = isset($scMeta['excerpt_limit'][0]) ? absint($scMeta['excerpt_limit'][0]) : 0;
                    $arg['excerpt_more_text'] = isset($scMeta['tgp_excerpt_more_text'][0]) ? $scMeta['tgp_excerpt_more_text'][0] : null;
                    $arg['title_limit_type'] = isset($scMeta['tpg_title_limit_type'][0]) ? $scMeta['tpg_title_limit_type'][0] : 'character';
                    $arg['title_limit'] = isset($scMeta['tpg_title_limit'][0]) ? absint($scMeta['tpg_title_limit'][0]) : 0;
                    $arg['read_more_text'] = isset($scMeta['tgp_read_more_text'][0]) && !empty($scMeta['tgp_read_more_text'][0]) ? $scMeta['tgp_read_more_text'][0] : __('Read More',
                        'the-post-grid');
                    $arg['show_all_text'] = (!empty($scMeta['tpg_show_all_text'][0]) ? $scMeta['tpg_show_all_text'][0] : __('Show all',
                        'the-post-grid-pro'));

                    $postType = isset($scMeta['tpg_post_type'][0]) ? $scMeta['tpg_post_type'][0] : null;
                    $post__in = isset($scMeta['post__in'][0]) ? $scMeta['post__in'][0] : null;
                    $post__not_in = isset($scMeta['post__not_in'][0]) ? $scMeta['post__not_in'][0] : null;
                    $limit = isset($scMeta['limit'][0]) && !empty($scMeta['limit'][0]) ? ($scMeta['limit'][0] == -1 ? 10000000 : (int)$scMeta['limit'][0]) : 10000000;
                    $pagination = isset($scMeta['pagination'][0]) ? $scMeta['pagination'][0] : false;
                    $posts_per_page = isset($scMeta['posts_per_page'][0]) ? intval($scMeta['posts_per_page'][0]) : $limit;
                    $order_by = isset($scMeta['order_by'][0]) ? $scMeta['order_by'][0] : null;
                    $order = isset($scMeta['order'][0]) ? $scMeta['order'][0] : null;
                    $s = isset($scMeta['s'][0]) ? $scMeta['s'][0] : null;
                    $isotope_filter = isset($scMeta['isotope_filter'][0]) ? $scMeta['isotope_filter'][0] : null;
                    $link = empty($scMeta['link_to_detail_page'][0]) || (!empty($scMeta['link_to_detail_page'][0]) && $scMeta['link_to_detail_page'][0] === "yes") ? true : false;
                    $link_target = !empty($scMeta['link_target'][0]) ? $scMeta['link_target'][0] : null;

                }
                if (!in_array($layout, array_keys(rtTPG()->rtTPGLayouts()))) {
                    $layout = 'layout1';
                }
                if (!in_array($col, array_keys(rtTPG()->rtTPGColumns()))) {
                    $col = 4;
                }

                $isIsotope = preg_match('/isotope/', $layout);

                /* Argument create */
                $args = array();
                $itemIdsArgs = array();
                if ($postType) {
                    $args['post_type'] = $itemIdsArgs['post_type'] = $postType;
                }

                // Common filter
                /* post__in */
                if ($post__in) {
                    $post__in = explode(',', $post__in);
                    $args['post__in'] = $itemIdsArgs['post__in'] = $post__in;
                }
                /* post__not_in */
                if ($post__not_in) {
                    $post__not_in = explode(',', $post__not_in);
                    $args['post__not_in'] = $itemIdsArgs['post__not_in'] = $post__not_in;
                }

                /* LIMIT */
                $args['posts_per_page'] = $itemIdsArgs['posts_per_page'] = $limit;
                if (!$isIsotope && $pagination) {
                    if ($posts_per_page > $limit) {
                        $posts_per_page = $limit;
                    }
                    // Set 'posts_per_page' parameter
                    $args['posts_per_page'] = $posts_per_page;
                    if (get_query_var('paged')) {
                        $paged = get_query_var('paged');
                    } elseif (get_query_var('page')) {
                        $paged = get_query_var('page');
                    } else {
                        $paged = 1;
                    }
                    $offset = $posts_per_page * ((int)$paged - 1);
                    $args['paged'] = $paged;

                    // Update posts_per_page
                    if (intval($args['posts_per_page']) > $limit - $offset) {
                        $args['posts_per_page'] = $limit - $offset;
                    }


                }

                // Advance Filter
                $adv_filter = isset($scMeta['post_filter']) ? $scMeta['post_filter'] : array();

                // Taxonomy
                $taxQ = array();
                if (in_array('tpg_taxonomy', $adv_filter) && isset($scMeta['tpg_taxonomy'])) {

                    if (is_array($scMeta['tpg_taxonomy']) && !empty($scMeta['tpg_taxonomy'])) {
                        foreach ($scMeta['tpg_taxonomy'] as $taxonomy) {
                            $terms = (isset($scMeta['term_' . $taxonomy]) ? $scMeta['term_' . $taxonomy] : array());
                            if (is_array($terms) && !empty($terms)) {
                                $operator = isset($scMeta['term_operator_' . $taxonomy][0]) ? $scMeta['term_operator_' . $taxonomy][0] : "IN";
                                if ($preview) {
                                    $operator = isset($scMeta['term_operator_' . $taxonomy]) ? $scMeta['term_operator_' . $taxonomy] : "IN";
                                }
                                $taxQ[] = array(
                                    'taxonomy' => $taxonomy,
                                    'field'    => 'term_id',
                                    'terms'    => $terms,
                                    'operator' => $operator,
                                );
                            }
                        }
                    }
                    if (count($taxQ) >= 2) {
                        $relation = isset($scMeta['taxonomy_relation'][0]) ? $scMeta['taxonomy_relation'][0] : "AND";
                        if ($preview) {
                            $relation = isset($scMeta['taxonomy_relation']) ? $scMeta['taxonomy_relation'] : "AND";
                        }
                        $taxQ['relation'] = $relation;
                    }
                }

                if (!empty($taxQ)) {
                    $args['tax_query'] = $itemIdsArgs['tax_query'] = $taxQ;
                }

                // Order
                if (in_array('order', $adv_filter)) {
                    if ($order) {
                        $args['order'] = $itemIdsArgs['order'] = $order;
                    }
                    if ($order_by) {
                        $args['orderby'] = $itemIdsArgs['orderby'] = $order_by;
                    }
                }
                // Status
                if (in_array('tpg_post_status', $adv_filter)) {
                    $post_status = (isset($scMeta['tpg_post_status']) ? $scMeta['tpg_post_status'] : array());
                    if (!empty($post_status)) {
                        $args['post_status'] = $itemIdsArgs['post_status'] = $post_status;
                    } else {
                        $args['post_status'] = $itemIdsArgs['post_status'] = 'publish';
                    }
                }
                // Author
                $author = (isset($scMeta['author']) ? $scMeta['author'] : array());
                if (in_array('author', $adv_filter) && !empty($author)) {
                    $args['author__in'] = $itemIdsArgs['author__in'] = $author;
                }
                // Search
                if (in_array('s', $adv_filter) && $s) {
                    $args['s'] = $itemIdsArgs['s'] = $s;
                }

                // Validation

                if (($layout == 'layout2') || ($layout == 'layout3')) {
                    $iCol = isset($scMeta['tgp_layout2_image_column'][0]) ? absint($scMeta['tgp_layout2_image_column'][0]) : 4;
                    if ($preview) {
                        $iCol = isset($scMeta['tgp_layout2_image_column']) ? absint($scMeta['tgp_layout2_image_column']) : 4;
                    }
                    $iCol = $iCol > 12 ? 4 : $iCol;
                    $cCol = 12 - $iCol;
                    $arg['image_area'] = "rt-col-sm-{$iCol} rt-col-xs-12 ";
                    $arg['content_area'] = "rt-col-sm-{$cCol} rt-col-xs-12 ";
                }
                $col = $col == 5 ? "24" : round(12 / $col);
                if (($layout == 'layout2') || ($layout == 'layout3')) {
                    $arg['grid'] = "rt-col-lg-{$col} rt-col-md-{$col} rt-col-sm-12 rt-col-xs-12";
                } else {
                    $arg['grid'] = "rt-col-lg-{$col} rt-col-md-{$col} rt-col-sm-6 rt-col-xs-12";
                }

                $arg_class = [];
                $arg_class[] = 'rt-equal-height';
                if ($isIsotope) {
                    $arg_class[] = 'isotope-item';
                }
                $arg['class'] = implode(" ", $arg_class);
                $arg['link'] = $link;
                $arg['link_target'] = $link_target ? " target='{$link_target}'" : null;

                $arg['overlay'] = empty($scMeta['tpg_overlay'][0]) ? false : true;
                $parentClass = (!empty($scMeta['parent_class'][0]) ? trim($scMeta['parent_class'][0]) : null);
                $arg['items'] = isset($scMeta['item_fields']) ? ($scMeta['item_fields'] ? $scMeta['item_fields'] : array()) : array();
                $arg['title_tag'] = (!empty($scMeta['title_tag'][0]) && in_array($scMeta['title_tag'][0], array_keys(rtTPG()->getTitleTags()))) ? esc_attr($scMeta['title_tag'][0]) : 'h2';
                $postQuery = new WP_Query(apply_filters('tpg_sc_query_args', $args, $scMeta));
                // Start layout
                $html .= "<div class='container-fluid rt-tpg-container rt-tpg-container-{$scMeta['sc_id']} {$parentClass}' data-sc-id='{$scMeta['sc_id']}' id='{$layoutID}'>";
                $extClass = null;
                if ($isIsotope) {
                    $extClass = ' tpg-isotope';
                }
                // Set readmore false if excerpt type = full content
                if (isset($arg['excerpt_type']) && $arg['excerpt_type'] === 'full' && ($key = array_search('read_more', $arg['items'])) !== false) {
                    unset($arg['items'][$key]);
                }

                $html .= "<div class='rt-row rt-content-loader {$layout}{$extClass}'>";
                if ($postQuery->have_posts()) {
                    $html .= $this->layoutStyle($layoutID, $scMeta, $preview);

                    if ($isIsotope) {
                        $selectedTerms = (isset($scMeta['term_' . $isotope_filter]) ? $scMeta['term_' . $isotope_filter] : array());
                        $terms = get_terms($isotope_filter, array(
                            'orderby'    => 'name',
                            'order'      => 'ASC',
                            'hide_empty' => false,
                            'include'    => $selectedTerms
                        ));

                        $html .= '<div id="iso-button-' . $rand . '" class="iso-button-' . $scMeta['sc_id'] . ' rt-tpg-isotope-buttons filter-button-group option-set">
											<button data-filter="*" class="selected">' . $arg['show_all_text'] . '</button>';
                        if (!empty($terms) && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                $html .= sprintf('<button class="rt-iso-btn-%s" data-filter=".iso_%d">%s</button>',
                                    esc_attr($term->slug),
                                    $term->term_id,
                                    $term->name
                                );
                            }
                        }
                        $html .= '</div>';

                        $html .= '<div class="rt-tpg-isotope iso-tpg-' . $scMeta['sc_id'] . '" id="iso-tpg-' . $rand . '">';
                    }


                    while ($postQuery->have_posts()) {
                        $postQuery->the_post();
                        $pID = get_the_ID();
                        $arg['pID'] = $pID;
                        $arg['title'] = rtTPG()->get_the_title($pID, $arg);
                        $arg['pLink'] = get_permalink();
                        $arg['author'] = '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '">' . get_the_author() . '</a>';
                        $cc = wp_count_comments($pID);
                        $arg['date'] = get_the_date();
                        $arg['excerpt'] = rtTPG()->get_the_excerpt($pID, $arg);
                        $arg['categories'] = get_the_term_list($pID, 'category', null, ', ');
                        $arg['tags'] = get_the_term_list($pID, 'post_tag', null, ', ');
                        if ($isIsotope) {
                            $termAs = wp_get_post_terms($pID, $isotope_filter, array("fields" => "all"));
                            $isoFilter = [];
                            if (!empty($termAs)) {
                                foreach ($termAs as $term) {
                                    $isoFilter[] = "iso_" . $term->term_id;
                                    $isoFilter[] = "rt-item-" . esc_attr($term->slug);
                                }
                            }
                            $arg['isoFilter'] = !empty($isoFilter) ? implode(" ", $isoFilter) : '';
                        }
                        $deptClass = null;
                        if (!empty($deptAs)) {
                            foreach ($deptAs as $dept) {
                                $deptClass .= " " . $dept->slug;
                            }
                        }
                        if (comments_open()) {
                            $arg['comment'] = "<a href='" . get_comments_link($pID) . "'>{$cc->total_comments} </a>";
                        } else {
                            $arg['comment'] = "{$cc->total_comments}";
                        }
                        $imgSrc = null;

                        $arg['imgSrc'] = !$fImg ? rtTPG()->getFeatureImageSrc($pID, $fImgSize, $mediaSource) : null;

                        $html .= rtTPG()->render('layouts/' . $layout, $arg, true);
                    }

                    if ($isIsotope) {
                        $html .= '</div>'; // End isotope item holder
                    }

                } else {
                    $html .= sprintf('<p>%s</p>',
                        apply_filters('tpg_not_found_text', __('No post found', 'the-post-grid'), $args, $scMeta)
                    );
                }
                $html .= "</div>"; // End row
                if ($pagination && !$isIsotope) {
                    $found = 0;
                    if ($postQuery->found_posts > $limit) {
                        $found = $limit;
                    } else {
                        $found = $postQuery->found_posts;
                    }
                    $max_num_pages = ceil($found / $posts_per_page);
                    $html .= rtTPG()->rt_pagination($max_num_pages, $args['posts_per_page']);
                }
                $html .= "</div>"; // container rt-tpg

                wp_reset_postdata();
                if (!$preview) {
                    $scriptGenerator = array();
                    $scriptGenerator['layout'] = $layoutID;
                    $scriptGenerator['rand'] = $rand;
                    $scriptGenerator['scMeta'] = $scMeta;
                    $scriptGenerator['isIsotope'] = $isIsotope;
                    $this->scA[] = $scriptGenerator;
                    add_action('wp_footer', array($this, 'register_sc_scripts'));
                }
            } else {
                if ($preview) {
                    $msg = __('Session Error !!', 'the-post-grid');
                } else {
                    $html .= "<p>" . __("No shortCode found", 'the-post-grid') . "</p>";
                }
            }
            if ($preview) {
                wp_send_json(array(
                    'error' => $error,
                    'msg'   => $msg,
                    'data'  => $html
                ));
                die();
            }

            return $html;
        }

        function layoutStyle($layout, $scMeta, $preview = false) {
            if ($preview) {
                $primaryColor = (!empty($scMeta['primary_color']) ? $scMeta['primary_color'] : null);
                $button_bg_color = (!empty($scMeta['button_bg_color']) ? $scMeta['button_bg_color'] : null);
                $button_hover_bg_color = (!empty($scMeta['button_hover_bg_color']) ? $scMeta['button_hover_bg_color'] : null);
                $button_active_bg_color = (!empty($scMeta['button_active_bg_color']) ? $scMeta['button_active_bg_color'] : null);
                $button_text_color = (!empty($scMeta['button_text_color']) ? $scMeta['button_text_color'] : null);
                $title_color = isset($scMeta['title_color']) && !empty($scMeta['title_color']) ? $scMeta['title_color'] : null;
                $title_hover_color = isset($scMeta['title_hover_color']) && !empty($scMeta['title_hover_color']) ? $scMeta['title_hover_color'] : null;
                $read_more_button_border_radius = isset($scMeta['tpg_read_more_button_border_radius']) ? $scMeta['tpg_read_more_button_border_radius'] : '';

            } else {

                $primaryColor = (!empty($scMeta['primary_color'][0]) ? $scMeta['primary_color'][0] : null);
                $button_bg_color = (!empty($scMeta['button_bg_color'][0]) ? $scMeta['button_bg_color'][0] : null);
                $button_hover_bg_color = (!empty($scMeta['button_hover_bg_color'][0]) ? $scMeta['button_hover_bg_color'][0] : null);
                $button_active_bg_color = (!empty($scMeta['button_active_bg_color'][0]) ? $scMeta['button_active_bg_color'][0] : null);
                $button_text_color = (!empty($scMeta['button_text_color'][0]) ? $scMeta['button_text_color'][0] : null);
                $title_color = isset($scMeta['title_color'][0]) && !empty($scMeta['title_color'][0]) ? $scMeta['title_color'][0] : null;
                $title_hover_color = isset($scMeta['title_hover_color'][0]) && !empty($scMeta['title_hover_color'][0]) ? $scMeta['title_hover_color'][0] : null;
                $read_more_button_border_radius = isset($scMeta['tpg_read_more_button_border_radius'][0]) ? $scMeta['tpg_read_more_button_border_radius'][0] : '';

            }
            $css = null;
            $css .= "<style type='text/css' media='all'>";
            // Variable
            if ($primaryColor) {
                $css .= "#{$layout} .rt-detail i,
                        #{$layout} .rt-detail .post-meta-user a,
                        #{$layout} .rt-detail .post-meta-category a{";
                $css .= "color:" . $primaryColor . ";";
                $css .= "}";
                $css .= "body .rt-tpg-container .rt-tpg-isotope-buttons .selected{";
                $css .= "background-color:" . $primaryColor . ";";
                $css .= "}";
            }
            if ($button_bg_color) {
                $css .= "#{$layout} .pagination li a,
                #{$layout} .rt-tpg-isotope-buttons button,
                #{$layout} .rt-detail .read-more a{";
                $css .= "background-color:" . $button_bg_color . ";";
                $css .= "}";
            }
            if ($button_hover_bg_color) {
                $css .= "#{$layout} .pagination li a:hover,
                        #{$layout} .rt-tpg-isotope-buttons button:hover,
                        #{$layout} .rt-detail .read-more a:hover{";
                $css .= "background-color:" . $button_hover_bg_color . ";";
                $css .= "}";
            }
            if ($button_active_bg_color) {
                $css .= "#{$layout} .pagination li.active span, 
                            #{$layout} .rt-tpg-isotope-buttons button.selected{";
                $css .= "background-color:" . $button_active_bg_color . ";";
                $css .= "}";
            }
            if ($button_text_color) {
                $css .= "#{$layout} .pagination li a,
                        #{$layout} .rt-tpg-isotope-buttons button,
                        #{$layout} .rt-detail .read-more a{";
                $css .= "color:" . $button_text_color . ";";
                $css .= "}";
            }
            if ($title_color) {
                $css .= "#{$layout} .rt-detail h2.entry-title a,
                         #{$layout} .rt-detail h3.entry-title a,
                         #{$layout} .rt-detail h4.entry-title a{";
                $css .= "color:" . $title_color . ";";
                $css .= "}";
            }
            if ($title_hover_color) {
                $css .= "#{$layout} .rt-detail h2.entry-title a:hover,
                         #{$layout} .rt-detail h3.entry-title a:hover,
                         #{$layout} .rt-detail h4.entry-title a:hover{";
                $css .= "color:" . $title_hover_color . ";";
                $css .= "}";
            }

            // Read more button Position
            if (!empty($read_more_button_border_radius)) {
                $css .= "#{$layout} .read-more a{";
                $css .= "border-radius:" . $read_more_button_border_radius . "px;";
                $css .= "}";
            }

            $css .= "</style>";

            return $css;
        }
    }
endif;
