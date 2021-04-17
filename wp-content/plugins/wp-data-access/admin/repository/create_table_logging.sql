CREATE TABLE {wp_prefix}wpda_logging{wpda_postfix}
( log_time datetime       NOT NULL
, log_id   varchar(50)    NOT NULL
, log_type enum('FATAL', 'ERROR', 'WARN', 'INFO', 'DEBUG', 'TRACE')
, log_msg  varchar(4096)
, PRIMARY KEY (log_time, log_id)
);