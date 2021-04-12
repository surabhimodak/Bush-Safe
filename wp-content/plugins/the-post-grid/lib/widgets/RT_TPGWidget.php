<?php

if(!class_exists('RT_TPGWidget')):


    /**
    *
    */
    class RT_TPGWidget extends WP_Widget
    {

        function __construct() {

            $widget_ops = array( 'classname' => 'widget_tpg_post_grid', 'description' => __('Display the post grid.', 'the-post-grid') );
            parent::__construct( 'widget_tpg_post_grid', __('The Post Grid', 'the-post-grid'), $widget_ops);

        }

        /**
         * display the widgets on the screen.
         */
        function widget( $args, $instance ) {
            extract( $args );
            $id = ( ! empty( $instance['id'] ) ? absint( $instance['id'] ) : null );

            echo $before_widget;
            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . apply_filters( 'widget_title',
                        ( isset( $instance['title'] ) ? $instance['title'] : "The Post Grid Pro" ) ) . $args['after_title'];
            }
            if(!empty($id)){
                echo do_shortcode("[the-post-grid id='{$id}' ]");
            }
            echo $after_widget;
        }
        function form( $instance ) {

            global $rtTPG;
            $scList   = $rtTPG->getAllTPGShortCodeList();
            $defaults = array(
                'title' => "The Post Grid",
                'id' => null
            );
            $instance = wp_parse_args( (array) $instance, $defaults ); ?>

            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:',
                        'the-post-grid' ); ?></label>
                <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>"
                       style="width:100%;"/></p>

            <p><label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'Select post grid',
                        'the-post-grid' ); ?></label>
                <select id="<?php echo $this->get_field_id( 'id' ); ?>"
                        name="<?php echo $this->get_field_name( 'id' ); ?>">
                    <option value="">Select one</option>
                    <?php
                    if ( ! empty( $scList ) ) {
                        foreach ( $scList as $scId => $sc ) {
                            $selected = ($scId == $instance['id'] ? "selected" : null);
                            echo "<option value='{$scId}' {$selected}>{$sc}</option>";
                        }
                    }
                    ?>
                </select></p>
            <?php
        }
        public function update( $new_instance, $old_instance ) {

            $instance          = array();
            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            $instance['id']    = ( ! empty( $new_instance['id'] ) ) ? absint( $new_instance['id'] ) : '';

            return $instance;
        }


    }


endif;
