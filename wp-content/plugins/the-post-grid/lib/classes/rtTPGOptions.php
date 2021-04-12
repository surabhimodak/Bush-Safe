<?php

if (!class_exists('rtTPGOptions')):

    class rtTPGOptions
    {

        function rtPostTypes() {
            $post_types = get_post_types(
                array(
                    '_builtin' => true
                )
            );
            $exclude = array('attachment', 'revision', 'nav_menu_item');
            foreach ($exclude as $ex) {
                unset($post_types[$ex]);
            }

            return $post_types;
        }

        function rtPostOrders() {
            return array(
                "ASC"  => "Ascending",
                "DESC" => "Descending",
            );
        }

        function rtTermOperators() {
            return array(
                'IN'     => "IN — show posts which associate with one or more of selected terms",
                'NOT IN' => "NOT IN — show posts which do not associate with any of selected terms",
                'AND'    => "AND — show posts which associate with all of selected terms",
            );
        }

        function rtTermRelations() {
            return array(
                'AND' => "AND — show posts which match all settings",
                'OR'  => "OR — show posts which match one or more settings",
            );
        }

        function rtPostOrderBy() {
            return array(
                "ID"         => "ID",
                "title"      => "Title",
                "date"       => "Created date",
                "modified"   => "Modified date",
                "menu_order" => "Menu Order"
            );
        }

        function rtTPGSettingFields() {
            global $rtTPG;
            $settings = get_option($rtTPG->options['settings']);

            return array(
                'custom_css'              => array(
                    'type'        => 'textarea',
                    'name'        => 'custom_css',
                    'label'       => 'Custom Css',
                    'holderClass' => 'rt-script-wrapper full',
                    'id'          => 'custom-css',
                    'value'       => isset($settings['custom_css']) ? stripslashes($settings['custom_css']) : null,
                ),
                "script_before_item_load" => array(
                    'name'        => 'script_before_item_load',
                    "label"       => __("Script before item load", 'the-post-grid'),
                    'type'        => 'textarea',
                    'holderClass' => 'rt-script-wrapper full',
                    'id'          => 'script-before-item-load',
                    'value'       => isset($settings['script_before_item_load']) ? stripslashes($settings['script_before_item_load']) : null
                ),
                "script_after_item_load"  => array(
                    'name'        => 'script_after_item_load',
                    "label"       => __("Script After item load", 'the-post-grid'),
                    'type'        => 'textarea',
                    'holderClass' => 'rt-script-wrapper full',
                    'id'          => 'script-after-item-load',
                    'value'       => isset($settings['script_after_item_load']) ? stripslashes($settings['script_after_item_load']) : null
                ),
                "script_loaded"           => array(
                    'name'        => 'script_loaded',
                    "label"       => __("After Loaded Script", 'the-post-grid'),
                    'type'        => 'textarea',
                    'holderClass' => 'rt-script-wrapper full',
                    'id'          => 'script-loaded',
                    'value'       => isset($settings['script_loaded']) ? stripslashes($settings['script_loaded']) : null
                )
            );
        }

        function rtTPGCommonFilterFields() {
            return array(
                'post__in'     => array(
                    "name"        => "post__in",
                    "label"       => "Include only",
                    "type"        => "text",
                    "class"       => "full",
                    "description" => __('List of post IDs to show (comma-separated values, for example: 1,2,3)',
                        'the-post-grid')
                ),
                'post__not_in' => array(
                    "name"        => "post__not_in",
                    "label"       => "Exclude",
                    "type"        => "text",
                    "class"       => "full",
                    "description" => __('List of post IDs to hide (comma-separated values, for example: 1,2,3)',
                        'the-post-grid')
                ),
                'limit'        => array(
                    "name"        => "limit",
                    "label"       => "Limit",
                    "type"        => "number",
                    "class"       => "full",
                    "description" => __('The number of posts to show. Set empty to show all found posts.',
                        'the-post-grid')
                )
            );
        }

        function rtTPGPostType() {
            return array(
                "name"    => "tpg_post_type",
                "label"   => "Post Type",
                "type"    => "select",
                "id"      => "rc-sc-post-type",
                "class"   => "rt-select2",
                "options" => $this->rtPostTypes()
            );
        }

        function rtTPAdvanceFilters() {
            return array(
                'type'      => "checkbox",
                'name'      => "post_filter",
                'label'     => "Advanced filters",
                'id'        => "post_filter",
                "alignment" => "vertical",
                "multiple"  => true,
                "options"   => array(
                    'tpg_taxonomy'    => "Taxonomy",
                    'order'           => "Order",
                    'author'          => "Author",
                    'tpg_post_status' => "Status",
                    's'               => "Search"
                ),
            );
        }

        function rtTPGPostStatus() {
            return array(
                'publish'    => 'Publish',
                'pending'    => 'Pending',
                'draft'      => 'Draft',
                'auto-draft' => 'Auto draft',
                'future'     => 'Future',
                'private'    => 'Private',
                'inherit'    => 'Inherit',
                'trash'      => 'Trash',
            );
        }

        function rtTPGLayoutSettingFields() {
            global $rtTPG;

            return array(
                'layout'                   => array(
                    "type"    => "select",
                    "name"    => "layout",
                    "label"   => "Layout",
                    "id"      => "rt-tpg-sc-layout",
                    "class"   => "rt-select2",
                    "options" => $this->rtTPGLayouts()
                ),
                'isotope-filtering'        => array(
                    "type"        => "select",
                    "name"        => "isotope_filter",
                    "label"       => "Isotope Filter",
                    'holderClass' => "sc-isotope-filter tpg-hidden",
                    "id"          => "rt-tpg-sc-isotope-filter",
                    "class"       => "rt-select2",
                    "options"     => $rtTPG->rt_get_taxonomy_for_isotope_filter()
                ),
                'tpg_show_all_text'        => array(
                    "type"        => "text",
                    "name"        => "tpg_show_all_text",
                    'holderClass' => "sc-isotope-filter tpg-hidden",
                    "label"       => esc_html__("Show all text", 'the-post-grid'),
                    "default"     => esc_html__("Show all", 'the-post-grid')
                ),
                'tgp_layout2_image_column' => array(
                    'type'        => 'select',
                    "name"        => "tgp_layout2_image_column",
                    "id"          => "tgp_layout2_image_column",
                    'label'       => __('Image column', 'the-post-grid'),
                    'class'       => 'rt-select2',
                    'holderClass' => "holder-layout2-image-column tpg-hidden",
                    'default'     => 4,
                    'options'     => $this->rtTPGColumns(),
                    "description" => "Content column will calculate automatically"
                ),
                'column'                   => array(
                    "type"    => "select",
                    "name"    => "column",
                    "label"   => "Column",
                    "id"      => "rt-column",
                    "class"   => "rt-select2",
                    "default" => 4,
                    "options" => $this->rtTPGColumns()
                ),
                'pagination'               => array(
                    "type"        => "checkbox",
                    "name"        => "pagination",
                    "label"       => "Pagination",
                    'holderClass' => "pagination",
                    "id"          => "rt-tpg-pagination",
                    "option"      => 'Enable'
                ),
                'posts_per_page'           => array(
                    "type"        => "number",
                    "name"        => "posts_per_page",
                    "label"       => "Display per page",
                    'holderClass' => "posts-per-page tpg-hidden",
                    "id"          => "posts-per-page",
                    "default"     => 5,
                    "description" => __("If value of Limit setting is not blank (empty), this value should be smaller than Limit value.",
                        'the-post-grid')
                ),
                'featured_image'           => array(
                    "type"   => "checkbox",
                    "name"   => "featured_image",
                    "label"  => "Feature Image",
                    "id"     => "rt-feature-image",
                    "option" => 'Disable'
                ),
                'featured_image_size'      => array(
                    "type"        => "select",
                    "name"        => "featured_image_size",
                    "label"       => "Feature Image Size",
                    "id"          => "featured-image-size",
                    'holderClass' => "feature-image-options tpg-hidden",
                    "class"       => "rt-select2",
                    "options"     => $rtTPG->get_image_sizes()
                ),
                'media_source'             => array(
                    "type"        => "radio",
                    "name"        => "media_source",
                    "label"       => "Media Source",
                    "id"          => "media-source",
                    'holderClass' => "feature-image-options tpg-hidden",
                    "default"     => 'feature_image',
                    "alignment"   => "vertical",
                    "options"     => $this->rtMediaSource()
                ),
                'tpg_title_limit'          => array(
                    "name"        => "tpg_title_limit",
                    "id"          => "tpg-title-limit",
                    "type"        => "number",
                    "label"       => esc_html__("Title limit", 'the-post-grid'),
                    "description" => esc_html__("Title limit only integer number is allowed, Leave it blank for full title.", 'the-post-grid')
                ),
                'tpg_title_limit_type'     => array(
                    "name"      => "tpg_title_limit_type",
                    "id"        => "tpg-title-limit-type",
                    "type"      => "radio",
                    "label"     => esc_html__("Title limit type", 'the-post-grid'),
                    "alignment" => "vertical",
                    "default"   => 'character',
                    "options"   => $this->get_limit_type(),
                ),
                'excerpt_limit'            => array(
                    "type"        => "number",
                    "name"        => "excerpt_limit",
                    "label"       => esc_html__("Excerpt limit", 'the-post-grid'),
                    "id"          => "excerpt-limit",
                    "description" => __("Excerpt limit only integer number is allowed, Leave it blank for full excerpt. Note: This will remove all html tag",
                        'the-post-grid')
                ),
                'tgp_excerpt_type'         => array(
                    "type"      => "radio",
                    "label"     => esc_html__("Excerpt Type", 'the-post-grid'),
                    "name"      => "tgp_excerpt_type",
                    "id"        => "tgp_excerpt_type",
                    "alignment" => "vertical",
                    "default"   => 'character',
                    "options"   => $this->get_limit_type('content'),
                ),
                'tgp_excerpt_more_text'    => array(
                    "type"    => "text",
                    "label"   => esc_html__("Excerpt more text", 'the-post-grid'),
                    "name"    => "tgp_excerpt_more_text",
                    "id"      => "tgp_excerpt_more_text",
                    "default" => "..."
                ),
                'tgp_read_more_text'       => array(
                    "type"  => "text",
                    "label" => esc_html__("Read more text", 'the-post-grid'),
                    "name"  => "tgp_read_more_text",
                    "id"    => "tgp_read_more_text",
                ),
                'tpg_overlay'              => array(
                    "type"   => "checkbox",
                    "name"   => "tpg_overlay",
                    "label"  => esc_html__("Overlay", 'the-post-grid'),
                    "id"     => "tpg_overlay",
                    "option" => 'Enable'
                ),
                'title_tag'                => array(
                    'type'    => 'select',
                    'name'    => 'title_tag',
                    'label'   => esc_html__('Title tag', 'the-post-grid'),
                    'class'   => 'rt-select2',
                    'id'      => 'title-tag',
                    'options' => $this->getTitleTags(),
                    'default' => 'h2'
                ),
                'link_to_detail_page'      => array(
                    "type"      => "radio",
                    'name'      => 'link_to_detail_page',
                    "label"     => esc_html__("Link To Detail Page", 'the-post-grid'),
                    "alignment" => "vertical",
                    "default"   => 'yes',
                    "options"   => array(
                        'yes' => 'Yes',
                        'no'  => 'No'
                    )
                ),
                'link_target'              => array(
                    "type"        => "radio",
                    "name"        => "link_target",
                    "label"       => esc_html__("Link Target", 'the-post-grid'),
                    'holderClass' => "tpg-link-target tpg-hidden",
                    "alignment"   => 'vertical',
                    "options"     => array(
                        ''       => 'Same Window',
                        '_blank' => 'New Window'
                    )
                ),
            );
        }

        function get_limit_type($content = null) {
            $types = array(
                'character' => __("Character", "the-post-grid"),
                'word'      => __("Word", "the-post-grid")
            );
            if ($content === 'content') {
                $types['full'] = __("Full Content", "the-post-grid");
            }

            return apply_filters('tpg_limit_type', $types, $content);
        }

        function rtTPGStyleFields() {

            return array(
                'parent_class'                       => array(
                    "name"        => "parent_class",
                    "type"        => "text",
                    "label"       => "Parent class",
                    "class"       => "medium-text",
                    "description" => "Parent class for adding custom css"
                ),
                'primary_color'                      => array(
                    "type"    => "text",
                    "name"    => "primary_color",
                    "label"   => "Primary Color",
                    "id"      => "primary-color",
                    "class"   => "rt-color",
                    "default" => "#0367bf"
                ),
                'button_bg_color'                    => array(
                    "type"  => "text",
                    "name"  => "button_bg_color",
                    "label" => "Button background color",
                    "id"    => "button-bg-color",
                    "class" => "rt-color"
                ),
                'button_hover_bg_color'              => array(
                    "type"  => "text",
                    "name"  => "button_hover_bg_color",
                    "label" => "Button hover background color",
                    "id"    => "button-hover-bg-color",
                    "class" => "rt-color"
                ),
                'button_active_bg_color'             => array(
                    "type"  => "text",
                    "name"  => "button_active_bg_color",
                    "label" => "Button active background color",
                    "id"    => "button-active-bg-color",
                    "class" => "rt-color"
                ),
                'button_text_bg_color'               => array(
                    "type"  => "text",
                    "name"  => "button_text_color",
                    "label" => "Button text color",
                    "id"    => "button-text-color",
                    "class" => "rt-color"
                ),
                'title_color'                        => array(
                    "type"  => "text",
                    "name"  => "title_color",
                    "label" => esc_html__("Title color", "the-post-grid"),
                    "id"    => "title_color",
                    "class" => "rt-color"
                ),
                'title_hover_color'                  => array(
                    "type"  => "text",
                    "name"  => "title_hover_color",
                    "label" => esc_html__("Title hover color", "the-post-grid"),
                    "id"    => "title_hover_color",
                    "class" => "rt-color"
                ),
                'tpg_read_more_button_border_radius' => array(
                    "type"        => "number",
                    "name"        => "tpg_read_more_button_border_radius",
                    "id"          => "tpg-read-more-button-border-radius",
                    "class"       => "small-text",
                    "label"       => esc_html__("Read more button border radius", "the-post-grid"),
                    "description" => esc_html__("Leave it blank for default", 'the-post-grid')
                )
            );

        }

        function getTitleTags() {
            return array(
                'h2' => "H2",
                'h3' => "H3",
                'h4' => "H4"
            );
        }

        function itemFields() {
            return array(
                "type"      => "checkbox",
                "name"      => "item_fields",
                "label"     => "Field selection",
                "id"        => "item-fields",
                "multiple"  => true,
                "alignment" => "vertical",
                "default"   => array_keys($this->rtTPGItemFields()),
                "options"   => $this->rtTPGItemFields()
            );
        }

        function rtMediaSource() {
            return array(
                "feature_image" => __("Feature Image", 'the-post-grid'),
                "first_image"   => __("First Image from content", 'the-post-grid')
            );
        }

        function rtTPGColumns() {
            return array(
                1 => "Column 1",
                2 => "Column 2",
                3 => "Column 3",
                4 => "Column 4",
                5 => "Column 5",
                6 => "Column 6"
            );
        }

        function rtTPGLayouts() {
            $layouts = array(
                'layout1'  => "Layout 1",
                'layout2'  => "Layout 2",
                'layout3'  => "Layout 3",
                'isotope1' => "Isotope Layout"
            );

            return apply_filters('tpg_layouts', $layouts);
        }

        function rtTPGItemFields() {
            return array(
                'title'         => __("Title", 'the-post-grid'),
                'excerpt'       => __("Excerpt", 'the-post-grid'),
                'read_more'     => __("Read More", 'the-post-grid'),
                'post_date'     => __("Post Date", 'the-post-grid'),
                'author'        => __("Author", 'the-post-grid'),
                'categories'    => __("Categories", 'the-post-grid'),
                'tags'          => __("Tags", 'the-post-grid'),
                'comment_count' => __("Comment Count", 'the-post-grid')
            );
        }

        function get_pro_feature_list() {
            return '<ol>
                        <li>Fully responsive and mobile friendly.</li>
                        <li>55 Different Layouts</li>
                        <li>Even and Masonry Grid.</li>
                        <li>WooCommerce supported.</li>
                        <li>Custom Post Type Supported</li>
                        <li>Display posts by any Taxonomy like category(s), tag(s), author(s), keyword(s)</li>
                        <li>Order by Id, Title, Created date, Modified date and Menu order.</li>
                        <li>Display image size (thumbnail, medium, large, full)</li>
                        <li>Isotope filter for any taxonomy ie. categories, tags...</li>
                        <li>Query Post with Relation.</li>
                        <li>Fields Selection.</li>
                        <li>All Text and Color control.</li>
                        <li>Enable/Disable Pagination.</li>
                        <li>AJAX Pagination (Load more and Load on Scrolling)</li>
                    </ol>
                <a href="https://www.radiustheme.com/the-post-grid-pro-for-wordpress/" class="rt-admin-btn" target="_blank">' . __("Get Pro Version", "the-post-grid") . '</a>';
        }

    }

endif;