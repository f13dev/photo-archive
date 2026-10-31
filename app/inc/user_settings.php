<?php 
/**
 * User configurable settings
 * Modify these variables to change functionality
 */

// Relative archive folder
define('PHOTO_ARCHIVE_DIR', 'archive');

// Enable database connection
define('PHOTO_ARCHIVE_USE_DB', true);

// Enable user acounts (requires database)
define('PHOTO_ARCHIVE_USE_ACCOUNTS', true);
define('PHOTO_ARCHIVE_SALT', '68b3ffbd983851fc458874da0c85426479aec72f');

// Database settings
define('PHOTO_ARCHIVE_DB_HOST', 'localhost');
define('PHOTO_ARCHIVE_DB_USER', 'root');
define('PHOTO_ARCHIVE_DB_PASSWORD', 'qZJRrn9lw1urKRjk');
define('PHOTO_ARCHIVE_DB_DATABASE', 'photos');

// Max thumb dimensions
define('THUMB_X', '192');
define('THUMB_Y', '108');

// Max mid image dimensions
define('MID_X', '1920');
define('MID_Y', '1080');

define('PHOTO_ARCHIVE_PROCESSOR', 'imagick');
