CREATE TABLE {wp_prefix}wpda_table_design{wpda_postfix} (
wpda_table_name		VARCHAR(64)	NOT NULL,
wpda_schema_name	VARCHAR(64)	NOT NULL DEFAULT '',
wpda_table_design	TEXT		NOT NULL,
wpda_date_created	TIMESTAMP   NULL,
wpda_last_updated	TIMESTAMP   NULL,
PRIMARY KEY (wpda_schema_name, wpda_table_name)
);