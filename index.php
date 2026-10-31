<?php namespace F13Dev\PhotoArchive;

header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) {
    ob_end_flush();
}
ob_implicit_flush(1);

require_once('app/inc/functions.php');

define('PHOTO_ARCHIVE_VERSION', '0.1 Alpha');

// Base URL and path defines
define('PHOTO_ARCHIVE_PATH', dirname(__FILE__));
define('PHOTO_ARCHIVE_URL', (isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__)).'/');
define('PHOTO_ARCHIVE_AJAX', PHOTO_ARCHIVE_URL.'?ajax=true&');

// Load user settings
require_once('app/inc/user_settings.php');

// Set folder locations
define('PHOTO_ARCHIVE_FOLDER', PHOTO_ARCHIVE_PATH.'/'.PHOTO_ARCHIVE_DIR.'/');
define('PHOTO_ARCHIVE_FOLDER_PATH', PHOTO_ARCHIVE_FOLDER.'images');
define('PHOTO_ARCHIVE_FOLDER_URL', PHOTO_ARCHIVE_URL.PHOTO_ARCHIVE_DIR.'/');
define('PHOTO_ARCHIVE_IMAGES_URL', PHOTO_ARCHIVE_FOLDER_URL.'images');
define('PHOTO_ARCHIVE_MID_URL', PHOTO_ARCHIVE_FOLDER_URL.'mid/');
define('PHOTO_ARCHIVE_THUMB_URL', PHOTO_ARCHIVE_FOLDER_URL.'thumbs/');
define('PHOTO_ARCHIVE_IMAGES_FOLDER', PHOTO_ARCHIVE_FOLDER.'images');
define('PHOTO_ARCHIVE_MID_FOLDER', PHOTO_ARCHIVE_FOLDER.'mid');
define('PHOTO_ARCHIVE_THUMB_FOLDER', PHOTO_ARCHIVE_FOLDER.'thumbs');

// Raw extensions
$raw_extensions = array(
    'cr2', 'erf', 'raf', 'rw2', 'nrw', 'nef', 'arw', 'rwz',
    'eip', 'dng', 'bay', 'dcr', 'raw', 'crw', '3fr', 'k25',
    'kc2', 'mef', 'dng', 'cs1', 'orf', 'ari', 'sr2', 'mos',
    'cr3', 'gpr', 'srw', 'srf', 'fff', 'kdc', 'j6i', 'mrw',
    'mfw', 'x3f', 'rwl', 'pef', 'iiq', 'cxi', 'mdc', 'nksc'
);

// Use minified JS and CSS (disable for development)
define('PHOTO_ARCHIVE_MINIFY', false);

class photo_archive
{
    public function __construct()
    {
        spl_autoload_register(function($class) {
            $class = str_replace(__NAMESPACE__, '', $class);
            $class = ltrim($class, '\\');
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            $class = strtolower($class);
            require_once $class.'.php';
        });

        echo $this->init();
    }

    public function init()
    {
        if (PHOTO_ARCHIVE_USE_DB && PHOTO_ARCHIVE_USE_ACCOUNTS) {
            session_start(); 
            if ((int) filter_input(INPUT_GET, 'logout')) {
                session_destroy();  
                header('Location: /'); 
            }
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
                return $this->page();
                $c = new \F13Dev\PhotoArchive\App\Controllers\Control();
                return $c->photo_archive();
            } else {
                $c = new \F13Dev\PhotoArchive\App\Controllers\Accounts();
                return $c->login();
            }
        } else {
            return $this->page();
            $c = new \F13Dev\PhotoArchive\App\Controllers\Control();
            return $c->photo_archive();    
        }
    }

    public function page() 
    {
        $page = filter_input(INPUT_GET, 'page');
        switch ($page) {
            case 'tree':
                $c = new \F13Dev\PhotoArchive\App\Controllers\Tree();
                return $c->family_tree();
            case 'search':
                $c = new \F13Dev\PhotoArchive\App\Controllers\Control();
                return $c->search_tags();
            case 'tags':
                $c = new \F13Dev\PhotoArchive\App\Controllers\Tags();
                return $c->tags();
            default:
                $c = new \F13Dev\PhotoArchive\App\Controllers\Control();
                return $c->photo_archive();
        }
    }
}

new photo_archive();
