<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	/**
	 * Class WPDA_Message_Box
	 *
	 * Displays a message box the WordPress way.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Message_Box {

		/**
		 * Default message type
		 */
		const DEFAULT_MESSAGE_TYPE = 'notice';

		/**
		 * Default setting dismissible
		 */
		const DEFAULT_MESSAGE_IS_DISMISSIBLE = true;

		/**
		 * Sequence number
		 *
		 * Used to give message boxes a unique number within the current response
		 *
		 * @var int
		 */
		static protected $message_box_seq = 0;

		/**
		 * Message box number
		 *
		 * @var int
		 */
		protected $message_box_no;

		/**
		 * Message box text
		 *
		 * @var string
		 */
		protected $message_text;

		/**
		 * Message type (error, notice, action)
		 *
		 * @var string
		 */
		protected $message_type;

		/**
		 * Indicates whether message is dismissible
		 *
		 * @var string
		 */
		protected $message_is_dismissible;

		/**
		 * Message type shown to user
		 *
		 * @var string
		 */
		protected $message_hint;

		/**
		 * WPDA_Message_Box constructor
		 *
		 * @param array $args [
		 *
		 * 'message_text'           => (string) Message text
		 *
		 * 'message_type'           => (string) Message type
		 *
		 * 'message_is_dismissible' => (boolean) Indicates whether message box is dismissible
		 *
		 * ].
		 * @since   1.0.0
		 *
		 */
		public function __construct( $args = [] ) {
			$args = wp_parse_args(
				$args, [
					'message_text'           => '',
					'message_type'           => self::DEFAULT_MESSAGE_TYPE,
					'message_is_dismissible' => self::DEFAULT_MESSAGE_IS_DISMISSIBLE,
				]
			);

			if ( '' === $args['message_text'] ) {
				wp_die( __( 'ERROR: Wrong arguments [missing message text argument]', 'wp-data-access' ) );
			}

			$this->message_text           = $args['message_text'];
			$this->message_type           = $args['message_type'];
			$this->message_is_dismissible = true === $args['message_is_dismissible'] ? 'is-dismissible' : '';
			switch ( $this->message_type ) {
				case 'error':
					$this->message_hint = 'ERROR';
					break;
				case 'action':
					$this->message_hint = 'ACTION';
					$this->message_type = 'notice';
					break;
				default:
					$this->message_hint = 'INFO';
			}

			$this->message_box_no = ++ self::$message_box_seq; // Give message box a unique number.
		}

		/**
		 * Show message box
		 *
		 * @since   1.0.0
		 */
		public function box() {
			$msgbox_id = 'message_box_' . esc_attr( $this->message_box_no );
			global $allowedposttags;
			?>
			<div id='<?php echo esc_attr( $msgbox_id ); ?>'
				 class='updated published <?php echo esc_attr( $this->message_type ); ?> <?php echo esc_attr( $this->message_is_dismissible ); ?>'>
				<p>
					<?php echo esc_attr( $this->message_hint ); ?>: <?php echo wp_kses( $this->message_text, $allowedposttags ); ?>
				</p>
			</div>
			<?php
		}

		public function custom_box( $content ) {
			$msgbox_id = 'message_box_' . esc_attr( $this->message_box_no );
			?>
			<div id='<?php echo esc_attr( $msgbox_id ); ?>'
				 class='updated published <?php echo esc_attr( $this->message_type ); ?> <?php echo esc_attr( $this->message_is_dismissible ); ?>'>
				<p>
					<?php echo $content; ?>
				</p>
			</div>
			<?php
		}

	}

}
