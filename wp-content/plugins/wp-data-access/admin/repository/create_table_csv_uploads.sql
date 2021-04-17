CREATE TABLE {wp_prefix}wpda_csv_uploads{wpda_postfix}
( csv_id mediumint(9) NOT NULL AUTO_INCREMENT
, csv_name varchar(100) NOT NULL
, csv_real_file_name varchar(4096) NOT NULL
, csv_orig_file_name varchar(4096) NOT NULL
, csv_timestamp datetime
, csv_mapping text
, PRIMARY KEY (csv_id)
);