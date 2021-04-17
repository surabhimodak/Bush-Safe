<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataRoles
 */

namespace WPDataRoles {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Roles
	 *
	 * Allow users to have multiple roles
	 *
	 * @author  Peter Schulz
	 * @since   2.7.0
	 */
	class WPDA_Roles {

		protected $is_role_management_enabled = false;

		public function __construct() {
			$this->is_role_management_enabled =
				( 'off' !== WPDA::get_option( WPDA::OPTION_WPDA_ENABLE_ROLE_MANAGEMENT ) );
		}

		/**
		 * @param $user
		 */
		public function multiple_roles_selection( $user ) {
			if ( ! $this->is_role_management_enabled ) {
				return;
			}

			$user_roles = isset( $user->roles ) ? implode("','", $user->roles) : '';
			?>
			<script type='text/javascript'>
				jQuery('select[name="role"]').attr('multiple', 'yes').attr('size', '6').prop('name', 'wpda_role[]');
				jQuery('#role').val(['<?php echo $user_roles; ?>']);
			</script>
			<?php
		}

		/**
		 * @param $user_id
		 */
		public function multiple_roles_update( $user_id ) {
			if ( ! $this->is_role_management_enabled ) {
				return;
			}

			$wp_user = new \WP_User( $user_id );
			if ( isset( $wp_user->data->user_login ) ) {
				$user_login = $wp_user->data->user_login;
				// Get access to editable roles
				global $wp_roles;
				if ( isset( $_REQUEST['wpda_role'] ) && is_array( $_REQUEST['wpda_role'] ) ) {
					// Process roles
					$sanitized_roles = [];
					foreach ( $_REQUEST['wpda_role'] as $new_user_role ) {
						$sanitized_new_user_role = sanitize_text_field( wp_unslash( $new_user_role ) ); // input var okay.
						$wp_user->add_role( $sanitized_new_user_role );
						$sanitized_roles[ $sanitized_new_user_role ] = true;
					}

					// Remove unselected roles
					foreach ( $wp_roles->roles as $role => $val ) {
						if ( ! isset( $sanitized_roles[ $role ] ) ) {
							$wp_user->remove_role( $role );
						}
					}
				} else {
					// BUG!!! REMOVED!!!
					// When plugin role management is enabled, this removes all user roles when a user updates his profile.
//					foreach ( $wp_roles->roles as $role => $val ) {
//						$wp_user->remove_role( $role );
//					}
				}
			}
		}

		/**
		 * Change role label in user list table
		 *
		 * @param $columns
		 *
		 * @return mixed
		 */
		public function multiple_roles_label( $columns ) {
			if ( ! $this->is_role_management_enabled ) {
				return $columns;
			}

			$columns['role'] = __( 'Role(s)', 'wp-data-access' );

			return $columns;
		}

	}

}