<?php

if (!class_exists('rtTPGAjaxResponse')):

    class rtTPGAjaxResponse
    {
        function __construct() {
            add_action('wp_ajax_rtTPGSettings', array($this, 'rtTPGSaveSettings'));
            add_action('wp_ajax_rtTPGShortCodeList', array($this, 'shortCodeList'));
            add_action('wp_ajax_rtTPGTaxonomyListByPostType', array($this, 'rtTPGTaxonomyListByPostType'));
            add_action('wp_ajax_rtTPGIsotopeFilter', array($this, 'rtTPGIsotopeFilter'));
            add_action('wp_ajax_rtTPGTermListByTaxonomy', array($this, 'rtTPGTermListByTaxonomy'));
        }

        function rtTPGSaveSettings() {
            global $rtTPG;
            $rtTPG->rtTPGSettingFields();
            $error = true;
            $msg = null;

            if ($rtTPG->verifyNonce()) {
                unset($_REQUEST['action']);
                unset($_REQUEST[$rtTPG->nonceId()]);
                unset($_REQUEST['_wp_http_referer']);
                update_option($rtTPG->options['settings'], $_REQUEST);
                $error = false;
                $msg = __('Settings successfully updated', 'the-post-grid');
            } else {
                $msg = __('Security Error !!', 'the-post-grid');
            }
            wp_send_json(array(
                'error' => $error,
                'msg'   => $msg
            ));
            die();
        }

        function rtTPGTaxonomyListByPostType() {
            global $rtTPG;
            $error = true;
            $msg = $data = null;
            if ($rtTPG->verifyNonce()) {
                $error = false;
                $taxonomies = $rtTPG->rt_get_all_taxonomy_by_post_type(isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : null);
                if (is_array($taxonomies) && !empty($taxonomies)) {
                    $data .= $rtTPG->rtFieldGenerator(
                        array(
                            'type'     => 'checkbox',
                            'name'     => 'tpg_taxonomy',
                            'label'    => 'Taxonomy',
                            'id'       => 'post-taxonomy',
                            "multiple" => true,
                            'options'  => $taxonomies
                        )
                    );
                } else {
                    $data = '<div class="rt-field-wrapper">' . __('No Taxonomy found', 'the-post-grid') . '</div>';
                }

            } else {
                $msg = __('Security error', 'the-post-grid');
            }
            wp_send_json(array('error' => $error, 'msg' => $msg, 'data' => $data));
            die();
        }

        function rtTPGIsotopeFilter() {
            global $rtTPG;
            $error = true;
            $msg = $data = null;
            if ($rtTPG->verifyNonce()) {
                $error = false;
                $taxonomies = $rtTPG->rt_get_taxonomy_for_isotope_filter(isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : null);
                if (is_array($taxonomies) && !empty($taxonomies)) {
                    foreach ($taxonomies as $tKey => $tax) {
                        $data .= "<option value='{$tKey}'>{$tax}</option>";
                    }
                }
            } else {
                $msg = __('Security error', 'the-post-grid');
            }
            wp_send_json(array('error' => $error, 'msg' => $msg, 'data' => $data));
            die();
        }

        function rtTPGTermListByTaxonomy() {
            global $rtTPG;
            $error = true;
            $msg = $data = null;
            if ($rtTPG->verifyNonce()) {
                $error = false;
                $taxonomy = isset($_REQUEST['taxonomy']) ? $_REQUEST['taxonomy'] : null;
                $data .= "<div class='term-filter-item-container {$taxonomy}'>";
                $data .= $rtTPG->rtFieldGenerator(
                    array(
                        'type'        => 'select',
                        'name'        => 'term_' . $taxonomy,
                        'label'       => ucfirst(str_replace('_', ' ', $taxonomy)),
                        'class'       => 'rt-select2 full',
                        'id'          => 'term-' . mt_rand(),
                        'holderClass' => "term-filter-item {$taxonomy}",
                        'value'       => null,
                        "multiple"    => true,
                        'options'     => $rtTPG->rt_get_all_term_by_taxonomy($taxonomy)
                    )
                );
                $data .= $rtTPG->rtFieldGenerator(
                    array(
                        'type'        => 'select',
                        'name'        => 'term_operator_' . $taxonomy,
                        'label'       => 'Operator',
                        'class'       => 'rt-select2 full',
                        'holderClass' => "term-filter-item-operator {$taxonomy}",
                        'options'     => $rtTPG->rtTermOperators()
                    )
                );
                $data .= "</div>";
            } else {
                $msg = __('Security error', 'the-post-grid');
            }
            wp_send_json(array('error' => $error, 'msg' => $msg, 'data' => $data));
            die();
        }

        function shortCodeList() {
            global $rtTPG;
            $html = null;
            $scQ = new WP_Query(array('post_type'      => $rtTPG->post_type,
                                      'order_by'       => 'title',
                                      'order'          => 'DESC',
                                      'post_status'    => 'publish',
                                      'posts_per_page' => -1
            ));
            if ($scQ->have_posts()) {

                $html .= "<div class='mce-container mce-form'>";
                $html .= "<div class='mce-container-body'>";
                $html .= '<label class="mce-widget mce-label" style="padding: 20px;font-weight: bold;" for="scid">' . __('Select Short code', 'the-post-grid') . '</label>';
                $html .= "<select name='id' id='scid' style='width: 150px;margin: 15px;'>";
                $html .= "<option value=''>" . __('Default', 'the-post-grid') . "</option>";
                while ($scQ->have_posts()) {
                    $scQ->the_post();
                    $html .= "<option value='" . get_the_ID() . "'>" . get_the_title() . "</option>";
                }
                $html .= "</select>";
                $html .= "</div>";
                $html .= "</div>";
            } else {
                $html .= "<div>" . __('No shortCode found.', 'the-post-grid') . "</div>";
            }
            echo $html;
            die();
        }

    }

endif;