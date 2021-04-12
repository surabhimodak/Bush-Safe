<?php
global $rtTPG;
echo $rtTPG->rtFieldGenerator($rtTPG->rtTPGPostType());
$sHtml = null;
$sHtml .= '<div class="rt-field-wrapper">';
$sHtml .= '<div class="rt-label"><label>Common filters</label></div>';
$sHtml .= '<div class="rt-field">';
$sHtml .= $rtTPG->rtFieldGenerator($rtTPG->rtTPGCommonFilterFields(), true);
$sHtml .= '</div>';
$sHtml .= '</div>';

echo $sHtml;

?>

<div class='rt-tpg-filter-container'>
    <?php echo $rtTPG->rtFieldGenerator($rtTPG->rtTPAdvanceFilters()); ?>
    <div class="rt-tpg-filter-holder">
        <h3><?php esc_html_e("Advance filter options", "the-post-grid"); ?></h3>
        <?php
        $html = null;
        $pt = get_post_meta($post->ID, 'tpg_post_type', true);
        $advFilters = $rtTPG->rtTPAdvanceFilters();
        foreach ($advFilters['options'] as $key => $filter) {
            if ($key == 'tpg_taxonomy') {
                $html .= "<div class='rt-tpg-filter taxonomy tpg_taxonomy hidden'>";
                if (isset($pt) && $pt) {
                    $taxonomies = $rtTPG->rt_get_all_taxonomy_by_post_type($pt);
                    $taxA = get_post_meta($post->ID, $key);
                    $post_filter = get_post_meta($post->ID, 'post_filter');
                    $html .= "<div class='taxonomy-field'>";
                    if (is_array($post_filter) && !empty($post_filter) && in_array($key, $post_filter) && !empty($taxonomies)) {
                        $html .= $rtTPG->rtFieldGenerator(
                            array(
                                'type'     => 'checkbox',
                                'name'     => $key,
                                'label'    => 'Taxonomy',
                                'id'       => 'post-taxonomy',
                                "multiple" => true,
                                'options'  => $taxonomies
                            )
                        );
                    } else {
                        $html .= '<div class="rt-field-wrapper">No Taxonomy found</div>';
                    }
                    $html .= "</div>";
                    $html .= "<div class='rt-tpg-filter-item term-filter-item hidden'>";
                    $html .= '<div class="rt-field-wrapper">';
                    $html .= '<div class="rt-label"></div>';
                    $html .= '<div class="rt-field term-filter-holder">';
                    if (is_array($taxA) && !empty($taxA)) {
                        foreach ($taxA as $tax) {

                            $html .= "<div class='term-filter-item-container {$tax}'>";
                            $html .= $rtTPG->rtFieldGenerator(
                                array(
                                    'type'        => 'select',
                                    'name'        => 'term_' . $tax,
                                    'label'       => ucfirst(str_replace('_', ' ', $tax)),
                                    'class'       => 'rt-select2 full',
                                    'holderClass' => "term-filter-item {$tax}",
                                    'value'       => get_post_meta($post->ID, 'term_' . $tax),
                                    "multiple"    => true,
                                    'options'     => $rtTPG->rt_get_all_term_by_taxonomy($tax)
                                )
                            );
                            $html .= $rtTPG->rtFieldGenerator(
                                array(
                                    'type'        => 'select',
                                    'name'        => 'term_operator_' . $tax,
                                    'label'       => 'Operator',
                                    'class'       => 'rt-select2 full',
                                    'holderClass' => "term-filter-item-operator {$tax}",
                                    'value'       => get_post_meta($post->ID, 'term_operator_' . $tax, true),
                                    'options'     => $rtTPG->rtTermOperators()
                                )
                            );
                            $html .= "</div>";
                        }
                    }
                    $html .= "</div>";
                    $html .= "</div>";

                    $html .= $rtTPG->rtFieldGenerator(
                        array(
                            'type'        => 'select',
                            'name'        => 'taxonomy_relation',
                            'label'       => 'Relation',
                            'class'       => 'rt-select2',
                            'holderClass' => "term-filter-item-relation " . (count($taxA) > 1 ? null : "hidden"),
                            'value'       => get_post_meta($post->ID, 'taxonomy_relation', true),
                            'options'     => $rtTPG->rtTermRelations()
                        )
                    );

                    $html .= "</div>";
                } else {

                    $html .= "<div class='taxonomy-field'>";
                    $html .= "</div>";
                    $html .= "<div class='rt-tpg-filter-item'>";
                    $html .= '<div class="rt-field-wrapper">';
                    $html .= '<div class="rt-label">' . __("Terms", 'the-post-grid') . '</div>';
                    $html .= '<div class="field term-filter-holder">';
                    $html .= "</div>";
                    $html .= "</div>";
                    $html .= "</div>";
                    $html .= $rtTPG->rtFieldGenerator(
                        array(
                            'type'        => 'select',
                            'name'        => 'taxonomy_relation',
                            'label'       => 'Relation',
                            'class'       => 'rt-select2',
                            'holderClass' => "term-filter-item-relation hidden",
                            'default'     => 'OR',
                            'options'     => $rtTPG->rtTermRelations()
                        )
                    );
                }
                $html .= "</div>";
            } else if ($key == 'order') {
                $html .= "<div class='rt-tpg-filter {$key} hidden'>";
                $html .= "<div class='rt-tpg-filter-item'>";
                $html .= "<div class='rt-field-wrapper'>";
                $html .= "<div class='rt-label'><label>" . __("Order Settings", 'the-post-grid') . "</label></div>";
                $html .= "<div class='rt-field'>";
                $html .= $rtTPG->rtFieldGenerator(
                    array(
                        'type'    => 'select',
                        'name'    => 'order_by',
                        'label'   => 'Order by',
                        'class'   => 'rt-select2 filter-item',
                        'value'   => get_post_meta($post->ID, 'order_by', true),
                        'options' => $rtTPG->rtPostOrderBy()
                    )
                );
                $html .= $rtTPG->rtFieldGenerator(
                    array(
                        'type'      => 'radio',
                        'name'      => 'order',
                        'label'     => 'Order',
                        'class'     => 'rt-select2 filter-item',
                        'alignment' => 'vertical',
                        'default'   => 'DESC',
                        'value'     => get_post_meta($post->ID, 'order', true),
                        'options'   => $rtTPG->rtPostOrders()
                    )
                );
                $html .= "</div>";
                $html .= "</div>";
                $html .= "</div>";
                $html .= "</div>";
            } else if ($key == 'author') {
                $html .= "<div class='rt-tpg-filter {$key} hidden'>";
                $html .= "<div class='rt-tpg-filter-item'>";
                $html .= $rtTPG->rtFieldGenerator(
                    array(
                        'type'     => 'select',
                        'name'     => $key,
                        'label'    => 'Author',
                        'class'    => 'rt-select2 filter-item full',
                        'value'    => get_post_meta($post->ID, $key),
                        "multiple" => true,
                        'options'  => $rtTPG->rt_get_users()
                    )
                );
                $html .= "</div>";
                $html .= "</div>";
            } else if ($key == 'tpg_post_status') {
                $html .= "<div class='rt-tpg-filter {$key} hidden'>";
                $html .= "<div class='rt-tpg-filter-item'>";
                $html .= $rtTPG->rtFieldGenerator(
                    array(
                        'type'     => 'select',
                        'name'     => $key,
                        'label'    => 'Status',
                        'class'    => 'rt-select2 filter-item full',
                        'default'  => array('publish'),
                        'value'    => get_post_meta($post->ID, $key),
                        "multiple" => true,
                        'options'  => $rtTPG->rtTPGPostStatus()
                    )
                );
                $html .= "</div>";
                $html .= "</div>";
            } else if ($key == 's') {
                $html .= "<div class='rt-tpg-filter {$key} hidden'>";
                $html .= "<div class='rt-tpg-filter-item'>";
                $html .= $rtTPG->rtFieldGenerator(
                    array(
                        'type'  => 'text',
                        'name'  => $key,
                        'label' => 'Search keyword',
                        'class' => 'filter-item full',
                        'value' => get_post_meta($post->ID, $key, true)
                    )
                );
                $html .= "</div>";
                $html .= "</div>";
            }

        }
        echo $html;
        ?>
    </div>
</div>
