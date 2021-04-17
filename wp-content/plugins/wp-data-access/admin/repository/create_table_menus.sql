CREATE TABLE {wp_prefix}wpda_menus{wpda_postfix} (
  menu_id         mediumint(9) NOT NULL AUTO_INCREMENT,
  menu_schema_name VARCHAR(64) NOT NULL DEFAULT '',
  menu_table_name VARCHAR(64)  NOT NULL,
  menu_name       VARCHAR(100) NOT NULL,
  menu_slug       VARCHAR(100) NOT NULL,
  menu_role       VARCHAR(100),
  PRIMARY KEY (menu_id)
);