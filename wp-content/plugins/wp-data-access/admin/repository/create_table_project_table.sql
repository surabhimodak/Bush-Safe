CREATE TABLE {wp_prefix}wpda_project_table{wpda_postfix}
( wpda_table_name varchar(64) NOT NULL
, wpda_schema_name varchar(64) NOT NULL DEFAULT ''
, wpda_table_setname varchar(100) NOT NULL DEFAULT 'default'
, wpda_table_design text NOT NULL
, PRIMARY KEY (wpda_schema_name, wpda_table_name, wpda_table_setname)
);