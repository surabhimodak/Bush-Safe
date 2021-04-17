<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	/**
	 * Class WPDA_Publisher_Model
	 *
	 * Model for plugin table 'menus'
	 *
	 * @author  Peter Schulz
	 * @since   2.6.0
	 */
	class WPDA_User_Menus_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_menus';

		/**
		 * List of external menu items
		 *
		 * Used in {@see \WP_Data_Access_Admin::add_menu_my_tables()} to build user defined menus.
		 *
		 * Returns all external menu items. These menus are below a user defined menu.
		 *
		 * @return array List of menu items
		 * @since   1.0.0
		 *
		 */
		public static function list_external_menus() {
			global $wpdb;

			if ( self::table_exists() ) {
				return $wpdb->get_results( '
					select * 
					from   ' . self::get_base_table_name() . ' 
					order by menu_name
				' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			} else {
				return [];
			}
		}

		/**
		 * Get menus defined for a specific table
		 *
		 * @param string $menu_table_name Table name
		 * @param string $menu_schema_name Schema name
		 *
		 * @return array List of menus for the requested table name
		 */
		public static function get_table_menus( $menu_table_name, $menu_schema_name ) {
			global $wpdb;

			if ( self::table_exists() ) {
				return $wpdb->get_results(
					$wpdb->prepare( '
						select * 
						from   ' . self::get_base_table_name() . ' 
						where menu_schema_name = %s
						  and menu_table_name = %s
						order by menu_name
					',
						[
							$menu_schema_name,
							$menu_table_name
						]
					), 'ARRAY_A'
				); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			} else {
				return [];
			}
		}

		/**
		 * Insert a record in the base table
		 *
		 * @param string $menu_table_name Table name
		 * @param string $menu_name Menu name
		 * @param string $menu_slug Menu slug
		 * @param string $menu_role Menu role(s)
		 * @param string $menu_schema_name Schema name
		 *
		 * @return bool TRUE = Insert was successful
		 */
		public static function insert( $menu_table_name, $menu_name, $menu_slug, $menu_role, $menu_schema_name ) {
			global $wpdb;

			if ( self::table_exists() ) {
				return ( 1 === $wpdb->insert(
						static::get_base_table_name(),
						[
							'menu_schema_name' => $menu_schema_name,
							'menu_table_name'  => $menu_table_name,
							'menu_name'        => $menu_name,
							'menu_slug'        => $menu_slug,
							'menu_role'        => $menu_role,
						]
					)
				);
			}
		}

		/**
		 * Update a record in the base table
		 *
		 * @param int $menu_id Menu id
		 * @param string $menu_table_name Table name
		 * @param string $menu_name Menu name
		 * @param string $menu_slug Menu slug
		 * @param string $menu_role Menu role(s)
		 * @param string $menu_schema_name Schema name
		 *
		 * @return mixed Transaction status
		 */
		public static function update( $menu_id, $menu_table_name, $menu_name, $menu_slug, $menu_role, $menu_schema_name ) {
			global $wpdb;

			return $wpdb->update(
				static::get_base_table_name(),
				[
					'menu_schema_name' => $menu_schema_name,
					'menu_table_name'  => $menu_table_name,
					'menu_name'        => $menu_name,
					'menu_slug'        => $menu_slug,
					'menu_role'        => $menu_role,
				],
				[
					'menu_id' => $menu_id,
				]
			);
		}

		/**
		 * Delete a record fro the base table
		 *
		 * @param int $menu_id Menu id
		 *
		 * @return mixed Transaction status
		 */
		public static function delete( $menu_id ) {
			global $wpdb;

			return $wpdb->delete(
				static::get_base_table_name(),
				[
					'menu_id'  => $menu_id,
				]
			);
		}

	}

}