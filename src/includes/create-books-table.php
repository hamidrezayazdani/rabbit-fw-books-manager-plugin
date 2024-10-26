<?php
global $wpdb;
$table_name = $wpdb->prefix . 'books_info';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    post_id bigint(20) UNSIGNED NOT NULL,
    isbn varchar(13) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY post_id (post_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);