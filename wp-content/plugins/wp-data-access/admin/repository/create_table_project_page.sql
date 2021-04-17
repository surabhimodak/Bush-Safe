CREATE TABLE {wp_prefix}wpda_project_page{wpda_postfix}
( project_id mediumint(9) NOT NULL
, page_id mediumint(9) NOT NULL AUTO_INCREMENT
, page_name varchar(100) NOT NULL
, add_to_menu enum('Yes','No') DEFAULT NULL
, page_type enum('table','parent/child','static') NOT NULL
, page_schema_name varchar(64) NOT NULL DEFAULT ''
, page_table_name varchar(64) DEFAULT NULL
, page_setname varchar(100) DEFAULT 'default'
, page_mode enum('edit','view') NOT NULL
, page_allow_insert enum('yes','no','only') NOT NULL
, page_allow_delete enum('yes','no') NOT NULL
, page_allow_import enum('yes','no') NOT NULL
, page_allow_bulk enum('yes','no') NOT NULL
, page_content bigint(20) unsigned DEFAULT NULL
, page_title varchar(100) DEFAULT NULL
, page_subtitle varchar(100) DEFAULT NULL
, page_role varchar(100) DEFAULT NULL
, page_where varchar(4096) DEFAULT NULL
, page_orderby varchar(4096) DEFAULT NULL
, page_sequence smallint(6) DEFAULT NULL
, PRIMARY KEY (page_id)
, UNIQUE KEY (project_id, page_name, page_role)
);