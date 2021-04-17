CREATE TABLE {wp_prefix}wpda_table_settings{wpda_postfix} (
wpda_schema_name	VARCHAR(64)	NOT NULL DEFAULT '',
wpda_table_name		VARCHAR(64)	NOT NULL,
wpda_table_settings	TEXT		NOT NULL,
PRIMARY KEY (wpda_schema_name, wpda_table_name)
);