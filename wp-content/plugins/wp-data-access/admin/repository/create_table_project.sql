CREATE TABLE {wp_prefix}wpda_project{wpda_postfix}
( project_id mediumint(9) NOT NULL AUTO_INCREMENT
, project_name varchar(100) NOT NULL
, project_description text
, add_to_menu enum('Yes','No') DEFAULT NULL
, menu_name varchar(30) DEFAULT NULL
, project_sequence smallint(6) DEFAULT NULL
, PRIMARY KEY (project_id)
, UNIQUE KEY {wp_prefix}wpdp_project_project_name (project_name)
);