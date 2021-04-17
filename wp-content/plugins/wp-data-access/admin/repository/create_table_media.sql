CREATE TABLE {wp_prefix}wpda_media{wpda_postfix}
( media_schema_name varchar(64)    NOT NULL DEFAULT ''
, media_table_name  varchar(64)    NOT NULL
, media_column_name varchar(64)    NOT NULL
, media_type        enum('Image', 'ImageURL', 'Attachment', 'Hyperlink', 'Audio', 'Video')
, media_activated   enum('Yes', 'No')
, PRIMARY KEY (media_schema_name, media_table_name, media_column_name)
);