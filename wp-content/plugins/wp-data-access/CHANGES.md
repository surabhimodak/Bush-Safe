### ChangeLog for WP Data Access

#### 4.2.1 / 2021-03-28

Fixed: List of values not working
Fixed: Field content truncated when entering a quote during inline editing (Luis)

#### 4.2.0 / 2021-03-25

Added: Support for estimated row count instead of count(*) (Charles)
Added: Load more button feature to publisher for large tables
Added: Default value wpda_buttons = 'CEFPYSVT' if wpda_buttons_custom = ''
Added: Optionally truncate table before importing CSV file (Mark Williams)
Added: Use OR with URL parameter filter (Barbara, grl570810, Charles)
Added: Environment variable $$EMAIL$$ = current user email address (tnijland3)
Added: Geolocation search and mapping (beta - under development)
Added: Repository backup and restore management to Settings > Manage Repository
Improved: Check if data type allows max length in Data Designer (Nicola)
Improved: Remove ID from tool exports (Charles)
Improved: Plugin installation procedure
Improved: Icon no longer shown in a separate column with buttons
Improved: Cookie experation on default schema changed from 24 hours to 365 days
Improved: Remote database error handling
Improved: Repository backup procedure
Fixed: Foreign key constraints of remote database not shown (Alex)
Fixed: Added dashicons to publications for media attachments (Dirk)
Fixed: Export/import prefix to WordPress and plugin tables (renpersonal)
Fixed: @ini_set return error with php 8 (grema)
Fixed: Update failed when saving post with invalid shortcode 
Fixed: Default order by not working if "Allow ordering?" is disabled in a publication (Steve)
Fixed: Column search box shown for hidden columns in responsive mode (Charles) 
Fixed: Repository issues with multi site installation
Fixed: Wildcard search on Data Explorer main page not correctly stored 
Fixed: Do not use the plugin transaction settings for plugin tables
Fixed: Inline editing does not correctly support double (Mark)
Fixed: Cannot use double quotes in advanced table options
Fixed: Bulk export remote database tables not working
Remove: jQuery.url() from publication

#### 4.1.3 / 2021-01-29

Patch: Float and double datatypes not taken into account (Dennis)

Changed: Message "Not authorized" to "Token expired or not authorized"
Added: New lookup type: auto complete (Steve)
Added: Responsive Data Forms project menu
Added: Option page length = 5 to show entries listbox
Fixed: Cannot enter numeric data (dennistennis)
Fixed: Wrong path in wpdadiehard premium when free version is uninstalled (Paul)
Fixed: Cannot access remote database in Data Forms
Fixed: Shortcode wpdadiehard returns error in editor
Fixed: Disable selector on first column|plus icon
Fixed: Support filter arguments in Data Forms (Andrew)
Fixed: Lookup value not shown in responsive columns (Barbara)
Fixed: Column label of hyperlink switched (Barbara)
Fixed: Replaced TODO with a proper error message 
Fixed: Cannot add hyperlinks containing spaces to Project Template 
Fixed: Spaces in dynamic hyperlinks not preserved
Fixed: Display items as read-only in view mode (Barbara)

#### 4.1.1 / 2021-01-14

Updated: Help links
Changed: Filter bar remains expanded when search values were entered (Steve)
Fixed: Error WPDA_Settings.php on line 4022 (lrydant)
Added: Support macros in advanced hyperlinks (IF-THEN-ELSE)
Fixed: Hide dynamic hyperlinks when URL is empty (Barbara)
Fixed: Hidden not null date column blocks update

#### 4.1.0 / 2021-01-06

Patched: Removed projects tab which gave an error on some installations (Kyle)
Patched: Plain text hyperlinks not shown in Data Forms (Barbara)

Updated: Read me
Updated: Language files
Fixed: Numeric precision (Hermann)
Fixed: Shortcode wpdadiehard does not use default order by (Barbara) 
Fixed: No error message shown on update or insert (Steve)
Fixed: Default value set column type not working (Barbara)
Fixed: Allow to update not null columns which already contain an empty string (Andrew)
Improved: Error handling CSV import
Fixed: Criteria added with wpda_search_column_ are not decoded in publications
Moved: Manage Table Options to Project Templates
Data Forms: Allow anonymous access role is empty
Data Forms: Added field validation before saving data
Data Forms: Added min|max validation for numeric fields
Data Forms: Added default value support
Data Forms: Added export buttons
Data Forms: Added hyperlink management
Data Forms: Added debug mode
Data Forms: Added validation on min|max values of numeric fields
Data Forms: Added validation on maximum length of text fields
Data Forms: Added tooltips
Data Forms: Added media library support
Data Forms: Added static|dynamic hyperlinks (Barbara)
Data Forms: Added full support for many to many relationships
Data Forms: Added previous|next row navigation (Vincent)
Fixed: Exception in foreach in Data Forms WS
Fixed: Modsecurity blocking schema_name - renamed schema_name to wpdaschema_name (Andrea)

#### 4.0.1 / 2020-12-04

Patch: CSV import does not work with empty dates
Patch: Default values not added to hidden columns

Fixed: Copy shortcode wpdataaccess returning wrong shortcode  

#### 4.0.0 / 2020-12-03

Update: jQuery and jQuery UI to prepare for WordPress 5.6 and 5.7
Update: Implode usage - deprecated message in 7.4 (Kooyaya)
Added: Define default database per user (Charles)
Changed: Improved remote database error handling
Updated: jQuery DataTables responsive library and style
Updated: jQuery UI darksness theme (used in data entry forms)
Added: Allow users to map CSV to tables in other (remote) databases (Charles)
Fixed: Default order by desc on child table not working (Jim)
Fixed: Search returns no result when criteria contains a quote (wwdz + Charles)
Fixed: Default order by for child table does not allow to reorder on other columns (Robert)
Updated: Readme
Updated: Freemius library
Fixed: Order by clause not working when column name starts with "order" (Nanne)
Added: Data Forms (beta)

Patched: Column headers not visible in export (Charles)
Patched: Broken link (what's new)
Patched: Column values in dynamic hyperlinks are not substituted in publication if the column is not selected (Charles)
Patched: Dates not correctly imported from csv if not matching the exact format (Erwin)

#### 3.6.5 / 2020-10-14

Patched: Column headers not visible in export (Charles)
Patched: Broken link (what's new)
Patched: Column values in dynamic hyperlinks are not substituted in publication if the column is not selected (Charles)
Patched: Dates not correctly imported from csv if not matching the exact format (Erwin)

Added: Parameters wpda_search_placeholder_prefix and wpda_search_placeholder to format search box (Charles)
Added: Row level access control (Fedi)
Fixed: Added column alias to prevent case sensitive column nam errors
Fixed: Date format not taken into account (Erwin)
Removed: Local version of clipboard library (using WordPress library) 
Updated: Freemius library
Fixed: Spaces in dynamic hyperlink arguments not working (Richard)
Fixed: Uninstall settings not working if not specifically saved
Added: Select individual columns  individual search per column (Moshe)
Added: Filter argument to filter wpda_column_default to support priorities (Anastasiia)  
Fixed: CSV import paging and searching not working (Moshe)
Added: Define dynamic hyperlink labels in Data Publisher (Charles)
Changed: Buttons in publication header in separate row (Charles) 
Fixed: Listbox error in Data Publisher if field value contains quote (Lorelei)
Changed: Publication columns are now nowrap by default
Added: Classes to search textbox, listbox and icons (for Charles)
Added: Recursive option conversion to Data Publisher to support functions on all levels

#### 3.6.0 / 2020-09-12

Added: Parameter wpda_search_placeholder_prefix to change the placeholder
Changed: Data Publisher no longer requires granting table access
Added: New column type ImageURL (auto convert image url to img tag)
Fixed: CSV import not working with unicode characters (Moshe)
Fixed: SSL support (Rene)
Fixed: Delete csv import not working (Mike)
Added: Default order by to Data Projects main page (for Andrea)
Added: Search box to premium Data Publisher (header|footer) (Charles)
Updated: Disable caching for all javascript ajax calls
Removed: Message "Auto-updates are not available for this plugin"
Fixed: Support Form menu is added to WP Data Access menu of non admin users (Anna)
Removed: Website link from plugin list
Fixed: Export from wpdadiehard not working for anonymous users
Fixed: Data Project JS library not found in shortcode wpdadiehard
Fixed: wp_enqueue_script( 'wpdadiehard' ); returns errors in WP 5.5
Added: Export buttons to Data Publisher (premium version only)
Added: Use WordPress date and time format in publications (Lorelei)
Fixed: Premium search filter priority not working correctly (Charles)

#### 3.5.0 / 2020-08-17

Updated: Dutch translation
Added: Shortcode arguments to filter wpdadiehard (forum support - Jack)
Fixed: inline editing checkbox uses tinyint, must be tinyint(1)
Fixed: checkbox set to null when disable, should be 0 to support not null (forum support - kooyaya)
Fixed: load-scripts.php error in WP 5.5
Removed: publication test from list table (remains available from data entry form)
Added: query ui darkness images to prevent 404
Removed: page arguments from public web pages (shortcode wpdadiehard)
Added: disable auto-updates in WordPress 5.5
Fixed: wpda_search_column_ values lost after re-order (forum support - Mark)
Changed: show more|less button label
Fixed: Data Publisher does not use dynamic filter arguments: wpda_search_column_ (forum support Mark)
Fixed: cannot export view to csv (forum support mrams93)
Fixed: cannot redeclare submit_button (forum support - Allen)
Added: publication name to shortcode (forum support - Andrew)
Added: sort icons selection from Data Publisher
Fixed: added permanently delete message to bulk actions buttons
Added: allow to drop views from bulk actions menu 
Changed: shorter button labels (support forum - Lawrence)
Fixed: searching, ordering and paging disabled for publication without pub_id
Added: parameter nl2br to shortcode wpdataaccess to convert New Lines to BR tags (forum support - Howard)
Added: notify library to show interactive notifications
Added: material icons to buttons and tabs
Added: more help and info icons to improve usability
Added: jQuery tooltips to help and info titles
Added: freemius library to support premium plugin version

#### 3.1.5 / 2020-07-05

Updated: Dutch translation
Fixed: Table settings hyperlink column id and name not unique
Fixed: Change image width to 100% (forum support - Chiara)
Fixed: Export button in Data Explorer not working
Added: Option to Data Publisher to convert NL to BR (forum support - Howard)
Fixed: Month in date field changes on mousewheel event (Michiel)
Added: Shortcode arguments to filter publication (forum support - Howard)
Fixed: Screen options (again!)
Fixed/Added: One click export for Data Projects including all option sets
Added: CSV upload table to plugin table array (Michiel)
Added: Disable attribute to filter wpda_add_column_settings
Added: Filter wpda_column_default to first column
Fixed: Apply plugin date format to primary key columns 
Fixed: Allow lookup on first column in list table
Fixed: Cookie samesite warning
Fixed: Removed iframes from project
Fixed: Show text only if hyperlink column does not contain a URL on frontend (forum support - Howard)

#### 3.1.4 / 2020-06-22

Fixed: Some sanitization issues
Added: Show text only if hyperlink column does not contain a URL (forum support - Howard)
Added: Custom items to search
Fixed: Hidden columns not available in default where construction
Fixed: Use option set default is named option set is not found

#### 3.1.3 / 2020-06-15

Added: Disable hyperlinks on list tables in Data Projects (parent/child)
Fixed: Export not available on web pages using wpdadiehard
Fixed: Responsiveness not working with shortcode wpdadiehard (forum support - nmarlon)
Fixed: Back to parent list button not working (forum support - alevel)
Fixed: API doc missing package WPDA_Roles
Fixed: Search not remembered in list tables

#### 3.1.2 / 2020-05-25

Fixed: ERROR: Wrong arguments [missing WPDA_PARENT_KEY*?] (Michiel)
Added: Added actions and filters to allow custom settings in Data Projects
Added: Show column of data type SET as CSV, UL or OL in list table (see plugin settings) 
Added: Hide ID in lookup list (forum support - hhagmxeu)
Fixed: Remove Add New button when insert is set to NO (forum support - hhagmxeu)
Updated: Original WP_List_Table WordPress class
Added: Add help link to list table and data entry form titles
Fixed: Invalid table open tag
Fixed: Data Publisher does not accept blanks in column names (forum support - neiljw)
Fixed: Need to remap when switching file type (CSV <> TSV)

#### 3.1.1 / 2020-05-08

Fixed: Error when clicking on child tab in Data Project  (forum support #12790240 - maverjk)
Fixed: CSV repository table not created during update (forum support #12728343 - tobiaseigen)
Fixed: Showing all tinyint columns should as checkboxes (forum support #12785271 - mikefoley)
Fixed: Disable bulk actions has no effect on shortcode (works correctly in dashboard)

#### 3.1.0 / 2020-05-05

Fixed: Check page mode against action on Data Project pages
Fixed: Data Designer – New table – Collation not working? (forum support #12597488 - Mattia Roccoberton)
Added: Allow json as well as plain text hyperlinks (defined in table settings)
Changed: Plugin page titles and layout more consistent
Removed: Tutorials and examples (now available on plugin website)
Changed: Data entry form layout
Added: Add table to user dashboard menu for non admin users
Added: Upload, map and import CSV files into an existing table
Added: Hook wpda_before_list_table to add code before a list table
Renamed: Hook wpda_extend_simple_form to wpda_after_simple_form
Renamed: Hook wpda_prepend_simple_form to wpda_before_simple_form
Renamed: Hook wpda_extend_list_table to wpda_after_list_table
Added: Handle to list table instance to hook wpda_extend_list_table
Added: Handle to simple form instance to hook wpda_extend_simple_form
Added: Handle to simple form instance to hook wpda_prepend_simple_form
Changed: Improved naming convention of actions and filters
Added: "Add New" button to simple form title (forum support #12404331 - merlinsilk)
Fixed: Lookup multiple column not shown correctly in table options
Fixed: Date and time picker not available for child tables (Ivan)
Added: Disable import on Data Projects page
Added: Disable bluk actions on Data Projects page (forum support #12612463 - Wolter)
Added: Define when hyperlinks are shown on a responsive modal window (Miguel)
Fixed: Plugin allows import on Data Projects pages with insert disabled
Fixed: Value 0 for sequence number in project and project pages missing in export
Fixed: Table settings stored incorrectly after opening multiple table settings
Fixed: Settings tables are not exported with {wp_prefix}
Fixed: Dynamic hyperlinks not exported with table settings
Fixed: Cannot use non primary columns in parent in relationships (forum support #12591142 - trebair) 
Removed: Donation and review links from plugin description
Changed: Order of plugin menu items
Updated: Plugin readme content
Fixed: Cannot create dynamic hyperlink (forum support #12586657 - mdurao) (patched)

#### 3.0.3 / 2020-03-26

Added: Allow lookups to other databases (including remote databases)
Added: Checkbox support for tinyint columns
Added: Dynamic hyperlinks to table settings
Added: New filter wpda_wpdataaccess_prepare
Added: Two new action hooks wpda_add_search_actions and wpda_add_search_filter
Fixed: Remote relationships not displayed
Fixed: Publication now really responsive when Number Of Columns = 0
Fixed: User must save design before table can be created (forum support #12558071 - holowkass)

#### 3.0.2 / 2020-03-10

Added: JSON validation to table options advanced column in Data Publisher
Added: Show search box on empty list
Added: Hook wpda_prepend_table_settings to add table settings at the beginning of the table settings section
Added: Hook wpda_append_table_settings to add table settings at the end of the table settings section
Added: Hook wpda_wpdadiehard_prepare to add preparation code to shortcode wpdadiehard
Added: Plugin class to jQuery DataTables to simplify styling (no css added in plugin)
Added: Filter wpda_get_column_headers to add custom labels to screen options 
Changed: No data from “Test Publication” (forum support #12520792 - Dave)
Fixed: Not possible to enter null dates (forum support #12515913 - Melody)
Fixed: Labels and icons not correctly aligned in data entry forms
Fixed: Cannot manage system views in Data Explorer
Fixed: ERROR: Wrong arguments [missing primary key value] (forum support #12487390 - flazza87)
Fixed: Comparing with translated table_type gives wrong results  
Fixed: Textarea editable in view mode (forum support #12461671 - charlesgodwin)

#### 3.0.1 / 2020-02-19

Patch: Added textarea support for all text columns (tinytext, text, mediumtext, longtext)
Patch: Table settings are not updated correctly if the settings have been changed between version 3.0.0 and 3.0.1 
Added: Filter wpda_add_column_settings to Data Explorer column settings
Added: Action hook 'wpda_default_screen_option' to support default screen option settings
Changed: Simplified layout table settings
Fixed: Order by publication needs manual overring stateSave setting
Fixed: Reset form to add a record does not work (forum support #12404331 - merlinsilk)
Fixed: Export/import {wp_schema} if schema name = wordpress schema || '' (transfer repositories)
Fixed: Manage table window in Data Explorer not using full width
Fixed: Frontend table view doesn’t work in IE11 (forum support #12397113 - peterdietz)
Fixed: Favourites class dies with wrong schema_name in url
Fixed: Cannot save remote database (forum support #12391185 emma26)
Fixed: User roles removed on profile change when plugin role management is enabled
Fixed: Class ‘WPDataProjects\Parent_Child\WPDA_Message_Box’ not found (form post #12399837 - mitfi)
Fixed: Error on trying to add any table to PROJECTS (form post #12385381 - aa11plus)

#### 3.0.0 / 2020-01-30

Fixed: Column name in DataTable class gone 
Added: Action hook 'wpda_extend_list_table' to support list table extension 
Added: Filter wpda_column_default to influence column layout in list tables
Added: New media types to Data Publisher
Fixed: Function wpdadiehard_convert_to_screen() not found (form post #12362969 - charlesnguyen)
Added: Full remote database support to Data Backup
Fixed: Search context lost on web pagel
Added: Support for $$USER$$ and $$USERID$$ to Data Publisher (form post #12352968 - Dozen)
Added: Use second, third, nth column in lookup to build where clause (change request - mieke van kooten)
Added: Support for media files of type video (play inline)
Added: Support for media files of type music (play inline) (form post #12258885 - dizwell)
Fixed: Media files not shown correctly after upload from WordPress media page
Fixed: Data Publisher gives an error when "Allow paging?" = NO
Added: Text columns are now shown in a textarea (multi line) instead of an input element
Added: Info to the Data Publisher how to authorise tables
Added: Allow registration only > project page > allow insert = only (no list table, update, delete, import)
Added: Allow Data Project to insert only (no update, delete, import)  (website comment database administration - jeffrey turner)
Added: Allow functions to be used in Data Publisher advanced table options (forum support #12332715 - marcellein)
Fixed: Layout messes up when using multiple columns in a relationship
Added: Filter to user search (WPDA_List_Table->construct_where_clause) (forum support #12315718 - charlesgodwin)
Added: Allow to hide plugin menu in dashboard (does not hide data projects)
Changed: Moved plugin settings page to dashboard settings menu
Added: Context sensitive help to plugin pages
Removed: Plugin help from menu
Added: Manage remote databases from Plugin Settings
Added: Full remote database support to Data Designer
Added: Test publication directly from Data Publisher main page
Added: Copy publication link to Data Publisher (form post #12275882 - dizwell)
Fixed: Change responsive type "collaped" to "collapsed" (form post #12275882 - dizwell)
Added: Screen options now also available in Data Projects
Fixed: WHERE Clause in Data Publisher only works with equals (=) (form post #12301684 - philippkaiser)
Fixed: DB_NAME in wp-config.php does not match real database name (lower_case_table_names = 1)
Fixed: Screen options not working correctly
Changed: Improved layout test frame Data Publisher
Added: Settings, review and donation links to plugin description
Fixed: Do not use offset and limit if serverSide is false
Fixed: Invalid Dropbox key
Added: Full remote database support to Data Projects
Added: Full remote database support to Data Publisher
Added: Full remote database support to Plugin Settings pages
Added: Full remote database support to Data Explorer
Added: Manage remote databases from Data Explorer
Changed: Menu item shows error page if repository table not found (instead of hiding menu item)
Added: On drop table delete all table settings from repository (labels, media columns, menus) 

#### 2.7.3 / 2019-12-18

Added: Help info to advanced table options
Added: Customize Datatables shortcode - adding standard and advanced options (form post #12236372 - rswebmaster)
Fixed: Can’t seem to change the number of rows initially displayed (form post #12247152 - dizwell)
Fixed: Cannot drop view in another database
Changed: Listbox behaviour responsive output
Fixed: Select listbox in Data Publisher not working correctly.
Added: Default where and order by to child table (table options) (form post #12232151 - khansadi)
Changed: Switched to new Dropbox app "WP Data Access Box"
Added: Sort on multiple columns in Data Publisher (form post #12226580 - spounch)
Changed: Layout simple form items to save space
Changed: Decreased parent area on Data Projects pages (remove title + add less/more button)
Added: External database support to WordPress media library columns
Added: External database support to data menus
Added: External database support to table settings
Added: External database support to shortcode wpdadiehard
Added: External database support to Data Projects
Added: Table access control for external databases to Data Explorer
Added: Table access control for external databases to plugin backend settings
Fixed: Disabled select and format columns buttons in Data Publisher in view mode
Changed: Removed WordPress table access options from Front-end Settings for external databases
Fixed: Some buttons and actions available in Data Designer for WP tables and view mode
Fixed: Added new line to end of export file to prevent error when importing as ZIP file  
Added: Create database from Data Explorer main page (forum post #11706835)
Added: Drop database from Data Explorer main page (forum post #11706835)
Fixed: Manage link in Data Explorer not working with system views
Fixed: Sort not working without default order by (support forum #1219867 - @ssamyn)

#### 2.7.2 / 2019-11-29

Added: Cookie settings (plugin settings page) to allow keeping cookies when switching panels
Added: Arguments added to shortcode [wpdataaccess] database, sql_where, sql_orderby
Added: Default WHERE/ORDER BY to publication (support forum #11907073 - @Gbade)
Removed: Settings tab and alter table button from Data Explorer when connected to other database
Added: Connect to other databases from Data Publisher (forum support #11706835 - steveediger)
Added: Internationalisation to Data Publisher front-end (data publisher settings page) (forum support #12181966 - ssamyn)
Updated: All html script tags to use text instead of language attribute
Updated: Menu item link to plugin help
Added: Allow shortcode access in posts and pages (plugin settings page)
Added: Support custom date and time formats (plugin settings page) (form post #12123210 - dmnauta)
Fixed: List tables not supporting responsive mode (forum support #12146070 - dsbking)
Fixed: Tabpage not responding (forum support #12123137 - dmnauta)
Added: Allow to export view to XML, JSON, Excel and CSV (forum support #12131944 - dsbking)
Fixed: Listboxes not working correctly in Safari (forum support #12114671 - sander zumbrink)

#### 2.7.1 / 2019-10-10

Fixed: Do not show version update notification when page called from shortcode
Fixed: Ask user for confirmation on copy table options set
Fixed: Make text "back to list" more specific on parent/child pages
Fixed: User should confirm when pressing the Reconcile Table button 
Fixed: Error on lookup if item value is null
Fixed: Warning creating default object from empty value when entering tab_label first time
Fixed: Plugin table Settings not used if no table options found for Data Projects table
Fixed: Column labels are not taken into account in exports (CSV and Excel)
Fixed: Role selection in Data Menus should show the role label not the role
Fixed: Role selection in Data Projects should show the role label not the role
Fixed: Cannot change Options Set Name  (forum support #12099274 - mieke van kooten)
Fixed: Shortcode wpdadiehard returns an error if convert_to_screen is already declared (forum support #12084970 - kirkgroome)

#### 2.7.0 / 2019-10-31

Added: Support role checking in shortcode wpdadiehard (data management on web pages)
Added: CSS class to DataTables (class name = database column name)
Fixed: Move back to list after adding an existing record for an n:m relationship
Fixed: Updated for WordPress 5.3 and 5.4
Fixed: Changed wp_die call to work properly in WordPress 5.4
Fixed: Data Designer listboxes not showing correctly in WordPress 5.4
Fixed: Width select item not showing correctly in WordPress 5.4
Fixed: Generated HTML media listbox wrong format 
Fixed: Input item of type text not showing correctly in WordPress 5.4
Added: Copy table options to new set
Added: Support for multiple table options sets
Fixed: Cannot use lookup in list table as first column
Moved: WPDA_Design_Table_Model and WPDP_Project_Design_Table_Model to Plugin_Table_Models
Fixed: PHP error for incorrect n:m relationship
Removed: Media columns from Data Projects (media columns now supported in table settings only)
Removed: Media columns from Data Publisher (media columns now supported in table settings only)
Added: Dynamic hyperlink to list table (review wmuskie | forum support #12038786 - OriOn)
Added: Button "Add New" child record always visible (website comment known limitations - mieke van kooten)
Changed: Label SHOW MORE/LESS button
Added: $$USERID$$ environment variable (forum support #12022533 - docwatsons)
Added: WordPress role management to allow multiple rows per user
Fixed: Select/deselect all rows for bulk actions not working for shortcode wpdadiehard
Changed: Rename Data Projects menu slug and file names from wpdp to wpda
Added: Export table settings with table (SQL export only - selectable)
Fixed: Page type table using wrong classes in shortcode wpdadiehard
Removed: Project ID column from Data Project page list tables
Added: Show shortcode action Data Projects page list table
Changed: Column order on Data Projects page
Fixed: Disable autocomplete for data/time columns
Fixed: $$USER$$ filter not working in shortcode (forum support #012022533 - docwatsons)
Fixed: Plugin backup tables not deleted on plugin removal
Fixed: On plugin activation backup plugin tables only for a new version

#### 2.6.1 / 2019-01-04

Fixed: Data type attribute not taken into account in Data Designer
Fixed: jQuery DataTables auto width calculation removed
Changed: Renamed Data Projects table prefix from wpdp to wpda
Removed: Const OPTION_WPDA_PREFIX (no functionality)
Removed: Const OPTION_WPDA_NAME (never used)
Fixed: Table wp_wpda_table_settings not removed on uninstall (forum support #11970313 - soprano)
Fixed: Tab labels not set correctly when using shortcode
Fixed: Message box not shown when using shortcode
Fixed: jQuery datetimepicker not available when using shortcode
Fixed: $wp_user->data->user_login not set for anonymous user (no login) 
Fixed: Add New button shown when parent-child form in view mode
Fixed: Two back buttons show in view mode for child rows
Fixed: Delete action available for child rows even in view mode
Fixed: Column ordering in Data Projects not using table options
Fixed: Error $actions is not an array if batches are disabled 
Fixed: Button SHOW LESS/SHOW MORE not shown on web page
Added: Action hook 'wpda_extend_simple_form' to support form extension 
Changed: Allow plugin folder dir to be overwritten to improve support for inheritance
Changed: Reference self to static to improve support for inheritance
Changed: Cursor type when dragging and dropping element

#### 2.6.0 / 2019-09-18

* Updated: Dutch language translation
* Added: Quick tours Data Publisher and Data Projects (support forum #11794759 - merlinsilk)
* Changed: Menu item Plugin Help opens external public page in new tab/window
* Changed: Moved documentation to public website
* Fixed: Numeric fields do not allow negative values (support forum #11892289 - wpsd2006)
* Changed: Disabled media column selection in Data Projects (moved to Data Explorer)
* Fixed: Cannot delete page from Data Project (support forum - #11889053 - wpsd2006)
* Changed: Default column label to first letter upper and rest lower for every word in label
* Fixed: Confirm delete backup tables
* Changed: Disabled media column selection in Data Publisher (moved to Data Explorer)
* Added: Message if button Select is clicked in Data Publisher on insert (need to save first)
* Fixed: Button Format Columns not working if format column is empty
* Removed: Test publication link from Data Publisher list table
* CLEANUP: Rewritten all plugin table models to use one base class
* Added: Table model for plugin table wp_wpda_table_settings
* Added: Plugin settings table to store table related settings
* Changed: Simplified Data Menus structure
* Moved: Data Menus to Data Explorer main page
* Moved: Manage Media to Data Explorer main page
* Moved: Data Backup menu to Data Explorer main page
* Added: Filter parameter to shortcode wpdadiehard (support forum #11844079 - tritongr)
* Fixed: Error on populating listbox when no tables selected in front-end settings (support forum #11844474 - rllopez66)
* Fixed: Column labels Manage Media list table not correctly defined
* Added: Plugin table and column settings to Data Explorer (work in progress)
* Added: Date / Time picker to data entry forms
* Fixed: Debian/MySQL8 sys table columns unordered without ordinal_position (support form #11820996 - jblakely)
* Fixed: Debian/MySQL8 sys table columns in uppercase without alias (support form #11820996 - jblakely)
* Added: Foreign keys to Table management on Data Explorer main page
* Fixed: Error message on duplicate key (support form #1179998 - Merlin Silk)

#### 2.5.1 / 2019-08-13

* Fixed: Font on web pages changed after updating to 2.5.0 (support forum #11814585 - bwhitemm and olbweb)
* Added: Data Project pages are now available on web pages using shortcode 'wpdadiehard'

#### 2.5.0 / 2019-08-02

* Removed: Bootstrap scripts and styles
* Changed: Scripts and styles for shortcodes only loaded when needed
* Changed: Scripts and styles for jQuery DataTables only loaded when needed
* Added: Data management on web page (forum post #11694569)
* Added: Use user defined title in project CRUD forms
* Added: Support for column labels to Data Publisher
* Added: Support for images to Data Publisher (forum post #11658244 - kentauron)
* Added: Support for media items (forum post #11658244 - kentauron)
* CLEANUP: Moved validation check to Simple_Form_Item (and sub classes)
* CLEANUP: Removed JS templates to support older browsers
* Changed: Updated JS/CSS versions for bootstrap, datatables and datatables responsive
* Added: Use unique index for row actions if no primary key is defined in Data Explorer
* Added: Drag and drop columns in Data Designer and Data Projects > Manage Table Options
* CLEANUP: Allow sub classes of WPDA_Simple_Form_Item to handle specific column types

#### 2.0.15 /  2019-07-08

* Added: Video tutorials for the Data Publisher tool
* Added: Set WordPress username as default user $$USER$$ (support topic #11656471 - kentauron)
* Fixed: Cascading delete on parent performs delete on child views
* Fixed: Auto increment field shown as key=no and mandatory=no in Data Projects
* Added: Show less/more button to parent form
* Fixed: MariaDB 10.2.7 and higher handles default values different than other DBMSs (support topic #11675290 - smolenaar)
* Fixed: Join USING not correctly handled on CentOS 7 MariaDB 10.3 (create project error finally solved!)
* Added: Data Publisher tool (supports generation of shortcodes)
* Removed: Shortcode button from visual editor
* Fixed: Check if auto increment column is false (create project error?)
* Fixed: Do not add auto increment column to insert (create project error?)
* Added: Value for sql_mode to system info
* Fixed: Updating failed error when saving a page that uses the plugin shortcode
* Fixed: Label for primary key columns not showing correctly in project list tables
* Added: Button to remove old backup tables (Manage Plugin > Manage Repository)
* Fixed: Allow insert/delete not working for project pages
* CLEANUP: Remove deprecated options
* CLEANUP: Replace nobr tags with span + nobr class
* CLEANUP: Language translation support
* CLEANUP: Source code documentation
* CLEANUP: API documentation
* CLEANUP: Source code reformatted to WordPress standards

#### 2.0.14 /  2019-06-08

* Changed: Import from Data Explorer main page is always allowed (admin user)
* Added: Data Designer integrated with Data Explorer (alter table and indexes directly from Data Explorer) 
* Fixed: Cannot enter html characters in Simple Form text fields (support topic 11562559 - leouesb)
* Added: Export from Data Explorer table page to XML, JSON, Excel and CSV  (support topic 11565221 - rswebmaster)
* Fixed: Error on delete parent when parent has lookups defined
* Added: Reconcile button to Data Designer
* Added: (re)Create index button to Data Designer
* Added: Alter table button to Data Designer
* Added: Drop index button to Data Designer
* Added: Drop table button to Data Designer
* Added: Show alter table script button to Data Designer
* Added: Show create table script button to Data Designer
* Added: Allow to show/hide deleted columns (compared with database table)
* Added: Highlight new, deleted and modified columns in Data Designer
* Added: Listbox to Data Backup to enable viewing all scheduled WordPress jobs
* Added: Data Backup button to Data Explorer header
* Changed: Uniform layout and behaviour for all buttons and links in page titles 
* Changed: Import title and info text (checks if zip upload is allowed)  
* Fixed: Export to csv deletes double quotes in text

#### 2.0.13 / 2019-05-17

* Fixed: Database name containing minus character leads to query errors (support topic 11540179 - Prause)
* Added: Export tables from Data Explorer to SQL (with(out) WP prefix), XML, json, Excel, csv files (support topic 11533487 - rswebmaster)

#### 2.0.12 / 2019-05-14

* Updated: Plugin help pages
* Added: Video tutorial to install the demo app
* Fixed: Search on table with no search columns should show no rows
* Fixed: Cannot search on lookup items
* Fixed: Sorting on lookup columns is not possible (removed header link from table list)
* Added: Check if file_uploads = On before upload (disable file upload if file_uploads = Off) 
* Fixed: Not correctly jumping back to list table source page after "Add Existing" > "search"
* Fixed: Data Explorer main page shows all tables on show favourites only no favourites defined
* Fixed: Export and Data Backup fail when memory_limit is too small
* Added: Check file size against upload_max_filesize before uploading imnport file
* Changed: Import now using streams to better support large files
* Changed: Export and Data Backup now using streams to better support large files
* Added: Log table to "Manage Repository" and "System Info"
* Changed: Export procedure now writes seperate insert statement for every row
* Fixed: Export/import procedures non WP schema performed on WP schema
* Added: System info tab to improve and simplify plugin support and communication

#### 2.0.11 / 2019-04-30

* Fixed: After editing a data record user always returns to page 1 (support topic 11476140 - Hannes - Decentris)
* Fixed: Cannot add new page to project (support topic 11477423 - fendervr)
* Added: Drop logging table on uninstall
* Added: Possibility to save repository backup tables during a plugin update
* Changed: Simplified repository (re)creation to decrease the possibility of failure
* Fixed: Export files writes {wp_prefix}_ instead of {wp_prefix}
* Fixed: View only list tables should not allow delete bulk actions
* Fixed: Cannot search in list of values (search is performed on main list table)
* Fixed: Site blocked after unattended plugin update (support topic 11472418 - tjgorman) (patched version 2.0.10)
* Fixed: Class 'WPDataProjects\List_Table\WPDP_List_Columns_Cache' not found (patched version 2.0.10)
* Fixed: Plugin table array removed from table cache (patched version 2.0.10)

#### 2.0.10 / 2019-04-25

* Changed: Moved all security checks from menu preparation to page preparation
* Added: Data Backup now supports unattended (background/no browser) adhoc backups (support topic 11466155 - stevekatasi)
* Changed: Improved and simplified Data Backup procedure
* Fixed: Added WordPress database schema and plugin tables to cache (support topic 11461930 - stevekatasi)
* Fixed: Added cache to list column classes to increase database performance (support topic 11461930 - stevekatasi)
* Fixed: Optimized class WPDP_List_Table_Lookup due to bad performance issue (support topic 11461930 - stevekatasi)
* Fixed: Create table menu items fails for MySQL 5.6 and prior (support topic 11461174 - rswebmaster)
* Added: Demo project (app) WPDA_SAS - School Administration System
* Added: Code example how to use WP Data Access classes in PHP plugin code
* Fixed: Default and list-values imported without single quotes on Reverse Engineering (support topic 11423815 - Hannes - Decentris)
* Fixed: Data Projects export not working in FireFix (support topic 11429499 - Hannes - Decentris)
* Fixed: Submenus of data apps not shown correctly for roles null or empty string
* Changed: Export tables with variable wpdb prefix to support import into repository with different wpdb prefix
* Fixed: Set data type not handled correctly in the Data Designer (support topic 11423815 - Hannes - Decentris)
* Added: Explain how to define enum and list type in the Data Designer (support topic 11423815 - Hannes - Decentris)
* Fixed: Added latest version of WP_List_Table to project to reclaim navigation buttons
* Fixed: Submenus of data apps not shown correctly for roles other than administrator
* Fixed: WP table prefix not taken into account (support topic 11411195 - Hannes - Decentris)
* Fixed: Key column labels not displayed correctly in table list
* Added: A listbox is generated for lookup items in data entry forms  
* Added: It is now possible to add a lookup column to a table list 
* Added: Disable relationship and data entry form config for views and tables without a primary key (Data Projects)
* Added: Allow views and tables without a primary key to be used (Data Projects)
* Added: Allow to create relationships between tables and views (Data Projects)
* Added: Table type (TABLE,VIEW) to WPDA_Design_Table_Model (WPDP_Project_Design_Table_Model inherited)
* Fixed: Import script containing multiple SQL statements failed on Windows (using \r\n) 

#### 2.0.8 / 2019-02-05

* Added: Video tutorial to explain how to create many to many relationships in Data Projects
* Changed: Static content not correctly filtered
* Added: Make username accessible in where clause of project list tables
* Added: Where clause to project list tables to influence selection (parent only)
* Added: Support for MySQL set data type (listbox handling multiple selections)
* Added: Role (multiple) to data project pages to give non admin users access to data apps
* Changed: Content in list table wrapped (request from Enterprise Branding) 
* Changed: What's new message now shown on all plugin pages
* Changed: Dropbox path now updatable
* Changed: Add / at the end of the backup folder name if not entered

#### 2.0.7 / 2019-01-27

* Added: Data backup tool to automatically backup table data to a local folder or Dropbox folder

#### 2.0.6 / 2018-12-16

* Added: Check number max size and precision in data entry forms
* Added: "Add New" button for parent in parent-child pages
* Added: Show list of available tables in data entry form for project>pages
* Fixed: Data Explorer manage table tabs not working correctly with multiple windows 
* Changed: Allow to hide primary key columns in data entry forms
* Changed: Allow to hide primary key columns in table list
* Fixed: Hide columns not working in all data entry forms
* Fixed: Data Project table page: mode, title and subtitle not taken into account

#### 2.0.5 / 2018-12-14

* Removed /themes/smoothness/jquery-ui.css from plugin admin class (shortcode button not working)
* Added: New screenshots to WordPress Plugin Directory
* Fixed: Export not working when "ask for confirmation when starting export" in settings is checked
* Changed: Tabs in list table (table actions) not working in Internet Explorer
* Changed: Links in list table not working in Internet Explorer

#### 2.0.4 / 2018-12-11

* Changed: Plugin description in WordPress Plugin Directory
* Changed: Layout of the manage table window

#### 2.0.3 / 2018-12-05

* Added: Optimize table from Data Explorer > manage table > actions tab
* Added: Hint user if table optimization should be considered
* Changed: Data menus was moved to Data Projects > Manage Dashboard Menus
* Added: Columns data size, index size and overhead to Data Explorer main page
* Added: Hide columns on Data Explorer mainpage
* Changed: Changed to order of the tabs in the manage table/view window
* Changed: Replaced icon to manage table of view with standard WordPress listtable link
* Changed: Changed import button text and labels for better understanding of import functionality
* Added: Video tutorial to explain how to create one to many relationships in Data Projects

#### 2.0.2 / 2018-12-03

* Fixed: Removed subtitle from Data Designer and Data Menus list
* Added: What's new page to inform users about new features
* Added: First video tutorial to explain Data Projects tool

#### 2.0.1 / 2018-11-27

* Fixed: Null values not exported correctly
* Fixed: Do not allow to hide mandatory columns in data entry forms

#### 2.0.0 / 2018-11-09

* Added: Data Projects to plugin
  * Create WordPress Data Apps
  * Add app to dashboard menu
  * Supports static pages
  * Supports CRUD pages
  * Supports parent/child pages
* Added: Documentation to plugin menu
* Fixed: Repository activation error
* Stopped: Website redirected to WordPress Plugin Directory

#### 1.6.9 / 2018-03-20

* Fixed: Bulk actions not executed due to fix in 1.6.7 on favourites change
* Added: Show MySQL error when create table fails
* Changed: Prepared WPDA_Design_Table_Model to support transparent structures

#### 1.6.8 / 2018-03-17

* Changed: Added new screenshots
* Fixed: Missing check unique column names and index names
* Fixed: Delete index in Data Designer not working
* Changed: Default mode Data Designer changed to advanced

#### 1.6.7 / 2018-03-16

* Fixed: Switch to editing mode after create table/index in Data Designer
* Fixed: Prevent bulk selections being executed on favourites change
* Fixed: Multiple alerts on invalid bulk drop or truncate selection

#### 1.6.6 / 2018-03-16

* Added: Copy table (including/excluding data)
* Added: Rename table/view
* Changed: Simplified usage of table/view/index actions from Data Explorer

#### 1.6.5 / 2018-03-15

* Added: Drop index from Data Explorer

#### 1.6.4 / 2018-03-14

* Fixed: Column 'Unique?' on 'Indexes' tab of Data Explorer always showing 'No'

#### 1.6.3 / 2018-03-14

* Fixed: Create table not working

#### 1.6.2 / 2018-03-01

* Fixed: Action button issues
* Fixed: Ask for confirmation on bulk-drop and bulk-truncate
* Fixed: Schema issues

#### 1.6.1 / 2018-03-01

* Added: Allow ZIP file imports to support larger import files (uses ZipArchive)

#### 1.6.0 / 2018-02-15

* Added: Create tables in basic or advanced mode (switch between modes)
* Added: Allow data and database administration of other schemas
* Added: Import table(s) button to Data Explorer (allows multiple imports)

#### 1.5.2 / 2018-02-06

* Added: Check every request for plugin updates (compare db version with plugin version)  

#### 1.5.1 / 2018-02-03

* Added: Check #Rows ( perform count if #Rows < WPDA::OPTION_BE_INNODB_COUNT )

#### 1.5.0 / 2018-01-23

* Added: Engine field to Data Explorer
* Added: Number of records field to Data Explorer
* Added: Drop and bulk drop for views (accessible through icon in Data Explorer)
* Added: Bulk drop and bulk truncate for tables (accessible through icon in Data Explorer)
* Added: View table/view structure (accessible through icon in Data Explorer)
* Added: Option to backend settings to get default search value functionality (forget search value)
* Added: Support for parent detail navigation
* Added: Added argument 'allow_import' to WPDA_List_Table to hide import button
* Changed: Always show page 1 on new search 
* Changed: Improved layout Simple Form
* Changed: Hide button 'Back To List' in view mode
* Removed: Menu WP Data Tables (replaced by favourites menu)
* Fixed: Current page selector not working
* Fixed: Check max length for input (attribute maxlength)
* Fixed: On expanding favourites table name not shown
* Fixed: Remember search value after navigating to details
* Fixed: WPDA_List_Table::construct_where_clause() not respecting values already in $this->where 
* Fixed: Searching in favourites not working
* Fixed: Disable only form items in view mode
* Fixed: Argument 'show_view_link' has no effect
* Fixed: Argument 'allow_insert' has no effect
* Fixed: Back button in list table when called from data explorer or favourites

#### 1.2.1 / 2018-01-14

* Fixed: Skip empty index on create table
* Fixed: Data entry form should showing CURRENT_TIMESTAMP as default value
* Fixed: Bulk checkboxes shown without bulk actions (tables export disabled)
* Fixed: List table favourites not showing labels when empty

#### 1.2.0 / 2018-01-13

* Fixed: Recognize missing wp_wpda_table_design
* Fixed: Single file for every alter table stetement (wp_wpda_table_design) 
* Added: Add tables to favourites (WP Data Tables still in menu but will be removed soon)

#### 1.1.1 / 2018-01-13

* Fixed: Create table wp_wpda_table_design (older versions of mysql not supporting timestamp)
* Fixed: Hidden columns array returns false

#### 1.1.0 / 2018-01-09

* Added: Data Designer
    * Design tables and indexes
    * Create tables and indexes from design
* Added: Drop table (from list table)
* Added: Truncate table (from list table)
* Fixed: Recognize all WordPress tables (single and multisite)
* Fixed: Link 'export' not showing in Data Explorer

#### 1.0.0 / 2017-12-04

* Fixed: I can’t add table to menu (2017-12-29)
* Fixed: Activating the plugin affects styles on the front page (2017-12-29)
* Fixed: Sanitization error (2017-12-29)
* Initial commit
