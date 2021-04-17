<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Simple_Form_Item
	 *
	 * Simple forms consist of items. Items correspond with table columns. Basically an item is generated for every
	 * table column in the base table. Class WPDA_Simple_Form_Item handles a database column as a standard text item.
	 *
	 * It's possible to add dummy columns. Values for dummy columns however are lost when data is saved.
	 *
	 * Check out {@see WPDA_Simple_Form} to see how to use simple form items.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Simple_Form_Item {

		/**
		 * Database column name
		 *
		 * @var string
		 */
		protected $item_name;

		/**
		 * MySQL data type
		 *
		 * @var string
		 */
		protected $data_type;

		/**
		 * Item label
		 *
		 * @var string
		 */
		protected $item_label;

		/**
		 * Current column value in the database
		 *
		 * @var mixed
		 */
		protected $item_value;

		/**
		 * Default value
		 *
		 * @var mixed
		 */
		protected $item_default_value;

		/**
		 * Database column specific info
		 *
		 * Like auto_increment, on update, etc
		 *
		 * @var string
		 */
		protected $item_extra;

		/**
		 * Enum values for column or empty
		 *
		 * @var array
		 */
		protected $item_enum;

		/**
		 * Enum options for column or empty
		 *
		 * @var array
		 */
		protected $item_enum_options;

		/**
		 * Database column type
		 *
		 * Column type offers more info than data type, like column length or values for enum types.
		 *
		 * @var string
		 */
		protected $column_type;

		/**
		 * Icon type
		 *
		 * @var string
		 */
		protected $item_icon_type;

		/**
		 * Array of events
		 *
		 * Add event to item for example: ["onclick" => "check_item_value()"]
		 *
		 * @var array
		 */
		protected $item_event;

		/**
		 * Item specific Javascript code
		 *
		 * Code is added to the end of the form.
		 *
		 * @var string
		 */
		protected $item_js;

		/**
		 * Show item icon (data type)
		 *
		 * TRUE = icon is shown after item, FALSE = hide icon (default FALSE)
		 *
		 * @var boolean
		 */
		protected $item_hide_icon;

		/**
		 * Item CSS class
		 *
		 * @var string
		 */
		protected $item_class;

		/**
		 * TRUE = item not shown, FALSE = item shown
		 *
		 * @var boolean
		 */
		protected $hide_item;

		/**
		 * TRUE = item not shown on init, FALSE = item shown on init
		 *
		 * @var boolean
		 */
		protected $hide_item_init = false;

		/**
		 * TRUE = null values are allowed, FALSE = no null values allowed
		 *
		 * @var boolean
		 */
		protected $is_nullable;

		/**
		 * TRUE = is auto_increment column
		 *
		 * @var boolean
		 */
		protected $is_auto_increment = false;

		/**
		 * TRUE = column is part of primary key, FALSE = column is not part of primary key
		 * @var boolean
		 */
		protected $is_key_column;

		/**
		 * TRUE = column is part of foreign key, FALSE = column is not part of foreign  key
		 * @var boolean
		 */
		protected $is_foreign_key_column = false;

		/**
		 * Context variable to keep logic for showing items maintainable
		 *
		 * @var string
		 */
		protected $show_context_action;

		/**
		 * Context variable to keep logic for showing items maintainable
		 *
		 * @var string
		 */
		protected $show_context_update_keys_allowed;

		/**
		 * Context variable to keep logic for showing items maintainable
		 *
		 * @var string
		 */
		protected $show_context_column_value;

		/**
		 * Context variable to keep logic for showing items maintainable
		 *
		 * @var string
		 */
		protected $show_context_class_primary_key;

		/**
		 * Context variable to keep logic for showing items maintainable
		 *
		 * @var string
		 */
		protected $show_context_item_events;

		/**
		 * Item placeholder
		 *
		 * @var string
		 */
		protected $item_placeholder = '';

		/**
		 * WPDA_Simple_Form_Item constructor
		 *
		 * Declare item with all its properties.
		 *
		 * @param array $args [
		 *
		 * 'item_name'          => item name
		 *
		 * 'data_type'          => data type
		 *
		 * 'item_label'         => label
		 *
		 * 'item_value'         => value (in database)
		 *
		 * 'item_default_value' => default value
		 *
		 * 'item_extra'         => check column extra in information_schema.columns
		 *
		 * 'item_enum'          => enum (if applicable)
		 *
		 * 'item_enum_options'  => enum options (if applicable)
		 *
		 * 'column_type'        => type
		 *
		 * 'item_event'         => JS event(s)
		 *
		 * 'item_js'            => JS code (global)
		 *
		 * 'item_hide_icon'     => icon (showing data type)
		 *
		 * 'item_class'         => css class
		 *
		 * 'hide_item'          => item visibility
		 *
		 * 'is_nullable'        => allow null values?
		 *
		 * 'is_key_column'      => is key column?
		 *
		 * ].
		 *
		 * @since   1.0.0
		 *
		 */
		public function __construct( $args = [] ) {
			if ( is_array( $args ) ) {
				$args = wp_parse_args(
					$args, [
						'item_name'          => '',
						'data_type'          => '',
						'item_label'         => '',
						'item_value'         => null,
						'item_default_value' => null,
						'item_extra'         => '',
						'item_enum'          => '',
						'item_enum_options'  => '',
						'column_type'        => '',
						'item_event'         => '',
						'item_js'            => '',
						'item_hide_icon'     => false,
						'item_class'         => '',
						'hide_item'          => false,
						'is_nullable'        => null,
						'is_key_column'      => null,
					]
				);

				if ( '' === $args['item_name'] ) {
					// Without an item name it makes no sense to continue
					wp_die( __( 'ERROR: Wrong arguments [missing item name]', 'wp-data-access' ) );
				}

				if ( '' === $args['data_type'] ) {
					// Without a data type it makes no sense to continue
					wp_die( __( 'ERROR: Wrong arguments [missing data type]', 'wp-data-access' ) );
				}

				$this->item_name      = $args['item_name'];
				$this->data_type      = WPDA::get_type( $args['data_type'] );
				$this->item_icon_type = $this->data_type;
				$this->item_label     = $args['item_label'];
				$this->item_value     = $args['item_value'];
				if ( 'CURRENT_TIMESTAMP' !== $args['item_default_value'] ) {
					$this->item_default_value = $args['item_default_value'];
				}
				$this->item_extra = $args['item_extra'];
				if ( 'auto_increment' === $this->item_extra ) {
					$this->is_auto_increment = true;
				}
				$this->item_enum         = '';
				$this->item_enum_options = '';
				if ( 'enum' === $this->data_type ) {
					$this->item_enum = explode(
						',',
						str_replace(
							'\'',
							'',
							substr( substr( $args['item_enum'], 5 ), 0, - 1 )
						)
					);
				}
				if ( 'set' === $this->data_type ) {
					$this->item_enum = explode(
						',',
						str_replace(
							'\'',
							'',
							substr( substr( $args['item_enum'], 4 ), 0, - 1 )
						)
					);
				}
				$this->column_type    = $args['column_type'];
				$this->item_event     = $args['item_event'];
				$this->item_js        = $args['item_js'];
				$this->item_hide_icon = $args['item_hide_icon'];
				$this->item_class     = $args['item_class'];
				$this->hide_item      = $args['hide_item'];
				$this->is_nullable    = $args['is_nullable'];
				$this->is_key_column  = $args['is_key_column'];
			} elseif ( is_object( $args ) ) {
				if ( 0 <= strpos( self::class, 'WPDA_Simple_Form_Item' ) ) {
					foreach ( $args as $property => $value ) {
						$this->$property = $value;
					}
				} else {
					wp_die( __( 'Class must be of (sub)type WPDA_Simple_Form_Item', 'wp-data-access' ) );
				}
			} else {
				wp_die( __( 'Could not create form item instance [argument must be array or object]', 'wp-data-access' ) );
			}
		}

		/**
		 * Show item row
		 *
		 * @param string $action Requested action
		 * @param string $update_keys_allowed TRUE = allow key updates
		 */
		public function show( $action, $update_keys_allowed ) {
			if ( $this->hide_item && ! $this->is_auto_increment && ! $this->is_foreign_key_column ) {
				if ( 'new' === $action && '' !== $this->get_item_default_value() && null !== $this->get_item_default_value() ) {
					// Add hidden item to apply default value
				} else {
					// Do not show item, do not add hidden item to form, user is not allowed to view this item
					return;
				}
			}

			// Set context variables
			$this->show_context_action              = $action;
			$this->show_context_update_keys_allowed = $update_keys_allowed;

			if ( 'row' === substr( $this->item_class, 0, 3 ) ) {
				// Process row level class
				$row_class        = $this->item_class;
				$this->item_class = '';
			} else {
				$row_class = '';
			}

			if ( true === $this->hide_item_init || true === $this->hide_item ) {
				?>
				<tr style='display:none'<?php echo ''===$row_class ? '' : ' class="' . esc_attr( $row_class ) . '"'; ?>>
				<?php
			} else {
				?>
				<tr <?php echo ''===$row_class ? '' : ' class="' . esc_attr( $row_class ) . '"'; ?>>
				<?php
			}

			$label        = explode( '|', esc_attr( $this->item_label ) );
			$label_before = $label[0];
			$label_after  = '';
			if ( isset( $label[1] ) ) {
				$label_after = $label[1];
			}

			if ( 'view' === $action ) {
				$label_title = ' ';
			} else {
				$label_title = $label_before . ' ';
				$label_title .= 'NO'===$this->is_nullable ? __( 'must be entered', 'wp-data-access' ) : __( 'is optional', 'wp-data-access' );
			}
			if (
				'WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Textarea' === get_class($this) ||
				'WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Set' === get_class($this)
			) {
				$style = 'style="vertical-align:top;padding-top:7px;"';
			} else {
				$style = '';
			}
			?>
			<td class="label" <?php echo $style; ?>>
				<label for="<?php echo esc_attr( $this->item_name ); ?>"
					   title="<?php echo $label_title; ?>"
					   class="wpda_tooltip"
				>
					<?php echo 'NO' === $this->is_nullable ? '*' : ''; ?>
					<?php echo esc_attr( $label_before ); ?>
				</label>
			</td>
			<td class="data">
				<?php
				// Get column value
				$this->show_context_column_value = esc_html( str_replace( '&', '&amp;', $this->item_value ) );

				// Set primary key class(es)
				$this->show_context_class_primary_key = $this->is_key_column ? 'wpda_primary_key' : '';
				if ( $this->is_auto_increment ) {
					$this->show_context_class_primary_key .= ' auto_increment';
				}

				// Prepare events
				$this->show_context_item_events = '';
				if ( is_array( $this->item_event ) ) {
					foreach ( $this->item_event as $event_name => $event_code ) {
						$this->show_context_item_events .= "$event_name=$event_code ";
					}
				}

				// Show item
				$this->show_item();
				?>
				<input type="hidden"
					   name="<?php echo esc_attr( $this->item_name ); ?>_old"
					   value="<?php echo $this->show_context_column_value; ?>"
				/>
				<?php echo '' === $label_after ? '' : '<label>' . esc_attr( $label_after ) . '</label>'; ?>
			</td>
			<td class="icon" <?php echo $style; ?>>
				<?php
				// Add data type icon.
				if ( ! $this->item_hide_icon ) {
					$type_button = new WPDA_Simple_Form_Type_Icon( $this->item_name, $this->item_icon_type );
					$type_button->show();
				}
				?>
			</td>
			</tr>
			<?php
		}

		/**
		 * Displays default text item
		 *
		 * Overwrite this method to define specific item processing like enum, set, image and so on.
		 */
		protected function show_item() {
			// Set maxlength based on MySQL column type to prevent errors on too long values.
			$begin_int = substr( $this->column_type, strpos( $this->column_type, '(' ) + 1 );
			$int_str   = substr( $begin_int, 0, strpos( $begin_int, ')' ) );
			if ( is_numeric( $int_str ) ) {
				$max_length = "maxlength={$int_str}";
			} else {
				$max_length = "data-number-format={$int_str}";
			}

			// Set column value.
			if ( 'new' === $this->show_context_action ) {
				// Check if there is a default value.
				if ( $this->item_default_value !== null &&
				     strtolower( $this->item_default_value ) !== 'null' ) {
					$this->show_context_column_value = $this->item_default_value;
				}
			}

			// Add input box.
			$data_type = $this->data_type;
			if ( 'number' === $this->data_type ) {
				if ( 'float' === substr( $this->column_type, 0, 5 ) || 'double' === substr( $this->column_type, 0, 5 ) ) {
					$data_type        = 'float';
					$pos_open         = false;
					$number_length    = 20;
					$number_precision = 10;
				} else {
					$pos_open  = strpos( $this->column_type, '(' );
					$pos_comma = strpos( $this->column_type, ',' );
					$pos_close = strpos( $this->column_type, ')' );
					if ( false !== $pos_open ) {
						// Add check for data length and precision to prevent errors on insert/update.
						if ( false === $pos_comma ) {
							$number_length    = substr( $this->column_type, $pos_open + 1, $pos_close - $pos_open - 1 );
							$number_precision = 0;
						} else {
							$number_length    = substr( $this->column_type, $pos_open + 1, $pos_comma - $pos_open - 1 );
							$number_precision = substr( $this->column_type, $pos_comma + 1, $pos_close - $pos_comma - 1 );
							if ( 0 != $number_precision ) {
								$data_type = 'float';
							}
						}
					}
				}
			}
			?>
			<input name="<?php echo esc_attr( $this->item_name ); ?>"
				   id="<?php echo esc_attr( $this->item_name ); ?>"
				   type="text"
				   value="<?php echo $this->show_context_column_value; ?>"
				   data-db-is-null="<?php echo null === $this->item_value ? 'true' : 'false'; ?>"
				   class="wpda_data_type_<?php echo esc_attr( $data_type ); ?> <?php echo esc_attr( $this->show_context_class_primary_key ); ?> <?php echo esc_attr( $this->item_class ); ?> <?php if ( 'NO' === $this->is_nullable && ! $this->is_auto_increment ) {
				       echo 'wpda_not_null';
			       } ?>"
				<?php echo esc_attr( $max_length ); ?>
				<?php echo esc_attr( $this->show_context_item_events ); ?>
				   placeholder="<?php echo esc_attr( $this->item_placeholder ); ?>"
			/>
			<?php
			if ( 'string' === $this->data_type ) {
				?>
				<input name="<?php echo esc_attr( $this->item_name ); ?>_db_is_null"
					   value="<?php echo null === $this->item_value ? 'true' : 'false'; ?>"
					   type="hidden"
				/>
				<?php
			} elseif ( 'number' === $this->data_type ) {
				if ( false !== $pos_open && 0 != $number_precision ) {
					?>
					<script type='text/javascript'>
						jQuery(function () {
							jQuery('#<?php echo esc_attr( $this->item_name ); ?>').on('blur', function () {
								if (Math.pow(10, <?php echo esc_attr( $number_length ); ?>) - 1 < this.value) {
									jQuery(this).addClass('wpda_input_error');
									alert('Max size exceeded');
									return false;
								} else {
									jQuery(this).removeClass('wpda_input_error');
								}
							});
						});
					</script>
					<?php
				}
			}
		}

		/**
		 * Het item name
		 *
		 * @return string
		 * @since   1.0.0
		 *
		 */
		public function get_item_name() {

			return $this->item_name;

		}

		/**
		 * Get item data type
		 *
		 * @return string
		 * @since   1.0.0
		 *
		 */
		public function get_data_type() {

			return $this->data_type;

		}

		/**
		 * Get item label
		 *
		 * @return string
		 * @since   1.0.0
		 *
		 */
		public function get_item_label() {

			return $this->item_label;

		}

		/**
		 * Get item value
		 *
		 * @return mixed
		 * @since   1.0.0
		 *
		 */
		public function get_item_value() {

			return $this->item_value;

		}

		/**
		 * Get item default value
		 *
		 * @return mixed
		 * @since   1.0.0
		 *
		 */
		public function get_item_default_value() {

			return $this->item_default_value;

		}

		/**
		 * Get item 'extra' info
		 *
		 * @return mixed
		 * @see WPDA_Simple_Form_Item::$item_extra
		 *
		 * @since   1.0.0
		 *
		 */
		public function get_item_extra() {

			return $this->item_extra;

		}

		/**
		 * Get enum values or empty
		 *
		 * @return array
		 * @since   1.0.0
		 *
		 */
		public function get_item_enum() {

			return $this->item_enum;

		}

		/**
		 * Get enum options or empty
		 *
		 * @return array
		 * @since   1.6.9
		 *
		 */
		public function get_item_enum_options() {

			return $this->item_enum_options;

		}

		/**
		 * Get column type
		 *
		 * @return string
		 * @since   1.0.0
		 *
		 */
		public function get_column_type() {

			return $this->column_type;

		}

		/**
		 * Get item event
		 *
		 * @return String
		 * @since   1.0.0
		 *
		 */
		public function get_item_event() {

			return $this->item_event;

		}

		/**
		 * Get item Javascript code
		 *
		 * @return mixed
		 * @since   1.0.0
		 *
		 */
		public function get_item_js() {

			return $this->item_js;

		}

		/**
		 * Hide icon?
		 *
		 * @return boolean
		 * @since   1.0.0
		 *
		 */
		public function get_item_hide_icon() {

			return $this->item_hide_icon;

		}

		/**
		 * Get item CSS class
		 *
		 * @return string
		 * @since   1.0.0
		 *
		 */
		public function get_item_class() {

			return $this->item_class;

		}

		/**
		 * Get item visibility
		 *
		 * @return boolean
		 * @since   1.6.9
		 *
		 */
		public function get_hide_item() {

			return $this->hide_item;

		}

		/**
		 * Null values allowed?
		 *
		 * @return boolean
		 * @since   2.0.0
		 *
		 */
		public function is_nullable() {

			return $this->is_nullable;

		}

		/**
		 * Is column part of the primary key?
		 *
		 * @return boolean
		 * @since   2.0.0
		 *
		 */
		public function is_key_column() {

			return $this->is_key_column;

		}

		/**
		 * Item value
		 *
		 * @param string $value Item value
		 */
		public function set_item_value( $value ) {

			$this->item_value = $value;

		}

		/**
		 * Item label
		 *
		 * @param string $label Item label
		 */
		public function set_label( $label ) {

			$this->item_label = $label;

		}

		/**
		 * Set item default value
		 *
		 * @param string $item_default_value Default value
		 *
		 * @since   1.6.2
		 */
		public function set_item_default_value( $item_default_value ) {

			$this->item_default_value = $item_default_value;

		}

		/**
		 * Set item CSS class
		 *
		 * @param string $item_class HTML class name
		 *
		 * @since   1.6.2
		 *
		 */
		public function set_item_class( $item_class ) {

			$this->item_class = $item_class;

		}

		/**
		 * Set item visibility
		 *
		 * @param boolean $hide_item TRUE = hide item
		 *
		 * @since   1.6.9
		 *
		 */
		public function set_hide_item( $hide_item ) {

			$this->hide_item = $hide_item;

		}

		/**
		 * Set item js code
		 *
		 * @param string $item_js Item specific javascript code
		 *
		 * @since   1.6.9
		 *
		 */
		public function set_item_js( $item_js ) {

			$this->item_js = $item_js;

		}

		/**
		 * Set item enum
		 *
		 * @param string $item_enum Item enum value list
		 *
		 * @since   1.6.9
		 *
		 */
		public function set_enum( $item_enum ) {

			$this->item_enum = $item_enum;

		}

		/**
		 * Set item enum options
		 *
		 * @param string $item_enum_options Item enum option list
		 *
		 * @since   1.6.9
		 *
		 */
		public function set_enum_options( $item_enum_options ) {

			$this->item_enum_options = $item_enum_options;

		}

		/**
		 * Set item data_type
		 *
		 * @param string $data_type Item data type
		 *
		 * @since   1.6.9
		 *
		 */
		public function set_data_type( $data_type ) {

			$this->data_type = $data_type;

		}

		/**
		 * Set item visibility
		 *
		 * @param boolean $item_hide_icon TRUE = hide type icon behind text field
		 *
		 * @since   2.0.8
		 *
		 */
		public function set_item_hide_icon( $item_hide_icon ) {

			$this->item_hide_icon = $item_hide_icon;

		}

		/**
		 * Set item is key column
		 *
		 * @param boolean $is_key_column TRUE|FALSE
		 */
		public function set_is_key_column( $is_key_column ) {

			$this->is_key_column = $is_key_column;

		}

		/**
		 * Set item is foreign key column
		 *
		 * @param boolean $is_foreign_key_column TRUE|FALSE
		 */
		public function set_foreign_key_column( $is_foreign_key_column ) {

			$this->is_foreign_key_column = $is_foreign_key_column;

		}

		/**
		 * Set item visibility on initialization
		 *
		 * @param boolean $hide_item_init TRUE = hide item init
		 *
		 * @since   1.6.9
		 *
		 */
		public function set_hide_item_init( $hide_item_init ) {

			$this->hide_item_init = $hide_item_init;

		}

		/**
		 * Check if item is valid
		 *
		 * @param $pre_insert
		 *
		 * @return bool
		 */
		public function is_valid( $pre_insert = false ) {
			if ( $this->is_auto_increment && $pre_insert ) {
				return true;
			}

			if ( 'NO' === $this->is_nullable ) {
				// Empty values are not allowed for this column: check value.
				if ( '' === $this->item_value ) {
					if ( isset( $_REQUEST[ "{$this->item_name}_db_is_null" ] ) && 'false' === strtolower( $_REQUEST[ "{$this->item_name}_db_is_null" ] ) ) {
						// Allow update if original column already was an empty string
						return true;
					}

					// No value: inform user and set validation to failed.
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => ucfirst( str_replace( '_', ' ', $this->item_name ) ) .
							                            ' ' . __( 'must be entered', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();

					return false;
				}
			}

			if ( 'number' === $this->data_type ) {
				if ( '' !== $this->item_value && null !== $this->item_value && ! is_numeric( $this->item_value ) ) {
					// Value is not numeric: inform user and set validation to failed.
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => ucfirst( str_replace( '_', ' ', $this->item_name ) ) .
							                            ' ' . __( 'must be numeric', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();

					return false;
				}
			}

			return true;
		}

	}

}
