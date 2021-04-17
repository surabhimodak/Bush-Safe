<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Dictionary
 */

namespace WPDataAccess\Data_Dictionary {

	/**
	 * Class WPDA_List_Columns_Cache
	 *
	 * This class caches the instances of class {@see WPDA_List_Columns} used during the request to prevent repeating
	 * the same query more than once.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 *
	 * @see WPDA_List_Columns
	 */
	class WPDA_List_Columns_Cache {

		/**
		 * @var array Cached instances of WPDA_List_Columns
		 */
		static protected $cached_list_columns = [];

		/**
		 * Get instance of WPDA_List_Columns for supplied schema and table name
		 *
		 * Checks if an instance of class {@see WPDA_List_Columns} for the given combination of $schema_name and
		 * $table_name is already in cache. If an instance was found, a handle to it is returned. If not, a new
		 * instance is created, added to the cache and a handle to it is returned.
		 *
		 * @param $schema_name Database schema name (= MySQL database)
		 * @param $table_name Datable table name
		 *
		 * @return object Handle to instance of {@see WPDA_List_Columns} for supplied $schema_name and $table_name
		 */
		static public function get_list_columns( $schema_name, $table_name ) {
			$index = "$schema_name.$table_name";
			if ( ! isset( self::$cached_list_columns[ $index ] ) ) {
				self::$cached_list_columns[ $index ] = new WPDA_List_Columns( $schema_name, $table_name );
			}

			return self::$cached_list_columns[ $index ];
		}

	}

}