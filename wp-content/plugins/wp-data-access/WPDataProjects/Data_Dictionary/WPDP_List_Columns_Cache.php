<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Data_Dictionary
 */

namespace WPDataProjects\Data_Dictionary {

	/**
	 * Class WPDP_List_Columns_Cache
	 *
	 * Instances of object WPDP_List_Columns are cached in this class
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_List_Columns_Cache {

		/**
		 * List of cached instances of WPDP_List_Columns
		 *
		 * @var array
		 */
		static protected $cached_list_columns = [];

		/**
		 * Gets an instance of WPDP_List_Columns from cache or creates a new instance
		 *
		 * New instances are added to the cache
		 *
		 * @param string $schema_name Database schema name
		 * @param string $table_name Database table name
		 * @param string $label_type Label type
		 * @param string $setname Options set name
		 *
		 * @return object Handle to WPDP_List_Columns instance
		 */
		static public function get_list_columns( $schema_name, $table_name, $label_type, $setname = 'default' ) {
			$index = "$schema_name.$table_name.$label_type.$setname";
			if ( ! isset( self::$cached_list_columns[ $index ] ) ) {
				self::$cached_list_columns[ $index ] = new WPDP_List_Columns( $schema_name, $table_name, $label_type, $setname );
			}

			return self::$cached_list_columns[ $index ];
		}

	}

}