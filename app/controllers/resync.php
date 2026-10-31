<?php namespace F13Dev\PhotoArchive\App\Controllers;

class Resync
{
    public $request_method;

    public function __construct()
    {
        $this->request_method = ($_SERVER['REQUEST_METHOD'] === 'POST') ? INPUT_POST : INPUT_GET;
        $this->ffmpeg = trim(shell_exec('type -P ffmpeg'));
    }

    public function _create_folders_iterator($folder, $echo)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $path => $object) {
            if (substr($path, -1) != '.') {

                $thumb = str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_THUMB_FOLDER, $path);
                $mid = str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_MID_FOLDER, $path);

                if (is_dir($path)) {
                    echo ($echo) ? str_pad('.', 4096) : '';
                    if (!file_exists($thumb)) {
                        if (mkdir($thumb, 0777)) {
                            echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Created folder: '.str_replace(PHOTO_ARCHIVE_PATH, '', $thumb),4096) : '';
                            if (touch($thumb.DIRECTORY_SEPARATOR.'index.php')) {
                                echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Created NULL index: '.str_replace(PHOTO_ARCHIVE_PATH, '', $thumb).DIRECTORY_SEPARATOR.'index.php',4096) : '';
                            } else {
                                echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Error creating NULL index: '.str_replace(PHOTO_ARCHIVE_PATH, '', $thumb).DIRECTORY_SEPARATOR.'index.php',4096) : '';
                            }
                        } else {
                            echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Error creating folder: '.str_replace(PHOTO_ARCHIVE_PATH, '', $thumb),4096) : '';
                        }
                    }

                    if (!file_exists($mid)) {
                        if (mkdir($mid, 0777)) {
                            echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Created folder: '.str_replace(PHOTO_ARCHIVE_PATH, '', $mid),4096) : '';
                            if (touch($mid.DIRECTORY_SEPARATOR.'index.php')) {
                                echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Created NULL index: '.str_replace(PHOTO_ARCHIVE_PATH, '', $mid).DIRECTORY_SEPARATOR.'index.php',4096) : '';
                            } else {
                                echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Error creating NULL index: '.str_replace(PHOTO_ARCHIVE_PATH, '', $mid).DIRECTORY_SEPARATOR.'index.php',4096) : '';
                            }
                        } else {
                            echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Error creating folder: '.str_replace(PHOTO_ARCHIVE_PATH, '', $mid),4096) : '';
                        }
                    }
                    echo ($echo) ? str_pad('<script>window.scrollTo(0,document.body.scrollHeight);</script>', 4096) : '';
                }
                set_time_limit(20);
            }
        }
    }

    public function _create_video($source, $echo)
    {
        if (!$this->ffmpeg) {
            // FFMPEG is not installed, skip this step
            echo ($echo) ? "<br>FFMPEG is not installed" : '';
            return;
        }

        $extensions = array('mp4', 'avi', 'mov');
        $extension = explode('.', $source);
        $extension = end($extension);

        if (in_array(strtolower($extension), $extensions)) {
            // Aim to have all videos saved as MP4
            $new_source = $source.'.mp4';
            if (!file_exists($new_source) && strtolower($extension) != 'mp4') {  // change to != mp4
                // Convert the video to MP4
                // Unset the original file
                // Change source to use .mp4 extension
                echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Starting video conversion', 40960) : '';
                $com = "ffmpeg -y -i '$source' -vcodec h264 -acodec mp2 '$new_source'";
                //echo ($echo) ? exec($com) : '';

                echo str_pad('<br>'.date('Y-m-d H:i:s').' - Completed conversion', 40960);
                if (file_exists($new_source)) {
                    echo ($echo) ? exec("chmod 0777 '$source'") : '';
                    echo ($echo) ? exec("chmod 0777 '$new_source'") : '';
                    echo ($echo) ? exec("rm -rf '$source'") : '';
                    echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Original video file removed', 40960) : '';
                    // Generate thumbnail
                    $thumb = str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_THUMB_FOLDER, $new_source).'.jpg';
                    //$com = "ffmpeg -i '$new_source' -vf \"select=eq(n\,0)\" -q:v 3 '$thumb'";
                    echo ($echo) ? exec($com) : '';
                    // Add in code to convert this image to thumbnail sized
                    echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Video thumbnail created', 40960) : '';
                }
            } else 
            if (strtolower($extension) == 'mp4' && !file_exists(str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_THUMB_FOLDER, $source).'.jpg')) {
                $thumb = str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_THUMB_FOLDER, $source).'.jpg';
                $com = "ffmpeg -i '$source' -vf \"select=eq(n\,0)\" -q:v 3 '$thumb'";
                //ECHO ($echo) ? $com : '';
                echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Video thumbnail created - '.$thumb, 40960) : '';
            }
        }
    }

    public function _create_video_image($source, $destination) 
    {
        $sec = 1;
        $thumb_size = '300x300';
        
        $cmd = 'ffmpeg -i "'.$source.'" -ss 00:00:01.000 -vframes 1 "'.$destination.'"';

        exec($cmd);
    }

    public function _create_image($source, $destination, $max_x = 1920, $max_y = 1080, $echo = true)
    {
        if (file_exists($destination)) {
            return false;
        }

        $folder = substr($source, 0, strrpos( $source, '/'));
        $mid = str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_MID_FOLDER, $folder);
        @mkdir($mid, 0777, true);
        $thumb = str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_THUMB_FOLDER, $folder);
        @mkdir($thumb, 0777, true);

        $extensions = array('jpg', 'jpeg', 'gif', 'png');
        $extension = explode('.', $source);
        $extension = end($extension);

        if (in_array(strtolower($extension), $extensions)) {
            if (defined('PHOTO_ARCHIVE_PROCESSOR') && PHOTO_ARCHIVE_PROCESSOR == 'imagick') {
                try {
                    $imagick = new \Imagick($source);
                    if ($imagick->getImageHeight() <= $imagick->getImageWidth()) {
                        $imagick->resizeImage($max_x, 0, \Imagick::FILTER_LANCZOS, 1);
                    } else {
                        $imagick->resizeImage(0, $max_y, \Imagick::FILTER_LANCZOS, 1);
                    }
                    //$imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    //$imagick->setImageCompressionQuality(75);
                    $imagick->stripImage();
                    $imagick->writeImage($destination);
                    $imagick->destroy();
                    return true;
                } catch (\Exception $e) {
                    echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - ERROR: thumbnail not created: '.str_replace(PHOTO_ARCHIVE_PATH, '', $destination),4096) : '';
                    return false;
                }
            } else {
                list($source_image_width, $source_image_height, $source_image_type) = @getimagesize($source);
                switch ($source_image_type) {
                    case IMAGETYPE_GIF:
                        $source_gd_image = imagecreatefromgif($source);
                        break;
                    case IMAGETYPE_JPEG:
                        $source_gd_image = imagecreatefromjpeg($source);
                        break;
                    case IMAGETYPE_PNG:
                        $source_gd_image = imagecreatefrompng($source);
                        break;
                    default:
                        $source_gd_image = false;
                }

                if ($source_gd_image == false) {
                    return false;
                }

                $source_aspect_ratio = $source_image_width / $source_image_height;
                $thumbnail_aspect_ratio = $max_x / $max_y;
                if ($source_image_width <= $max_x && $source_image_height <= $max_y) {
                    $thumbnail_image_width = $source_image_width;
                    $thumbnail_image_height = $source_image_height;
                } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                    $thumbnail_image_width = (int) ($max_y * $source_aspect_ratio);
                    $thumbnail_image_height = $max_y;
                } else {
                    $thumbnail_image_width = $max_x;
                    $thumbnail_image_height = (int) ($max_x / $source_aspect_ratio);
                }
                $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
                imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0,0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                imagejpeg($thumbnail_gd_image, $destination, 90);
                imagedestroy($source_gd_image);
                imagedestroy($thumbnail_gd_image);

                echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Created file: '.str_replace(PHOTO_ARCHIVE_PATH, '', $destination),4096) : '';

                return true;
            }
        }
    }

    public function _create_thumbs_iterator($folder, $echo)
    {
        $m = new \F13Dev\PhotoArchive\App\Models\Database();

        global $raw_extensions;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $path => $object) {
            echo ($echo) ? str_pad('.', 4096) : '';
            if (substr($path, -1) != '.') {

                $file = basename($path);
                $folder = str_replace($file, '', str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, '', $path));

                $ifd = @exif_read_data(PHOTO_ARCHIVE_FOLDER_PATH.$folder.$file, 'IFD0');
                $exif_data = '<strong>File: </strong>'.$file.'<br>';
                $exif_data .= '<strong>Folder: </strong>'.$folder.'<br><br>';
                if (@array_key_exists('Make', $ifd)) {
                    $exif_data .= '<strong>Make: </strong>:'.$ifd['Make'].'<br>';
                }
                if (@array_key_exists('Model', $ifd)) {
                    $exif_data .= '<strong>Model: </strong>:'.$ifd['Model'].'<br>';
                }
                if (@array_key_exists('ExposureTime', $ifd)) {
                    $exif_data .= '<strong>Exposure: </strong>:'.$ifd['ExposureTime'].'<br>';
                }
                if (@array_key_exists('ApertureFNumber', $ifd['COMPUTED'])) {
                    $exif_data .= '<strong>Aperture: </strong>:'.$ifd['COMPUTED']['ApertureFNumber'].'<br>';
                }
                if (@array_key_exists('ISOSpeedRatings', $ifd)) {
                    $exif_data .= '<strong>ISO: </strong>:'.$ifd['ISOSpeedRatings'].'<br>';
                }
                if (@array_key_exists('DateTime', $ifd)) {
                    $date = \DateTime::createFromFormat('Y:m:d H:i:s', $ifd['DateTime']);
                    $exif_data .= ($date) ? '<strong>Date: </strong>:'.$date->format('F, j Y - H:i').'<br>' : '';
                }
                if (@array_key_exists('Copyright', $ifd) && !empty(trim($ifd['Copyright']))) {
                    $exif_data .= '<strong>Copyright: </strong>'.$ifd['Copyright'].'<br>';
                }

                $ext = explode('.', $file);
                $ext_original = end($ext);

                $raw = '';

                $base_file = PHOTO_ARCHIVE_IMAGES_FOLDER.'/'.ltrim($folder).rtrim($file, $ext_original);
                foreach ($raw_extensions as $raw_ext) {
                    if (is_file($base_file.$raw_ext)) {
                        $raw = rtrim($file, $ext_original).$raw_ext;
                        break;
                    }
                }
    
                if (empty($raw)) {
                    foreach ($raw_extensions as $raw_ext) {
                        if (is_file($base_file.strtoupper($raw_ext))) {
                            $raw = rtrim($file, $ext_original).strtoupper($raw_ext);
                            break;
                        }
                    }
                }

                //echo ($echo) ? 'File: '.$path.'<br>' : '';
                //echo ($echo) ? 'Folder: '.$path.'<br>' : '';
                $m->insert_file($folder, $file, $exif_data, $raw);

                $thumb = str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_THUMB_FOLDER, $path);
                $mid = str_replace(PHOTO_ARCHIVE_IMAGES_FOLDER, PHOTO_ARCHIVE_MID_FOLDER, $path);

                $this->_create_image($path, $thumb, THUMB_X, THUMB_Y, $echo);
                $this->_create_image($path, $mid, MID_X, MID_Y, $echo);
                $this->_create_video($path, $echo);
            }
            echo ($echo) ? str_pad('<script>window.scrollTo(0,document.body.scrollHeight);</script>', 4096) : '';
            set_time_limit(600);
        }
    }

    public function _unused_thumbs_iterator()
    {

    }

    public function resync_thumbs($folder = DIRECTORY_SEPARATOR, $echo = true)
    {
        // Set folder to a relative path
        $folder = rtrim(PHOTO_ARCHIVE_IMAGES_FOLDER.$folder, DIRECTORY_SEPARATOR);
        // Check if ffmpeg is installed
        echo ($echo) ? str_pad('<style>body { background: #000; color: #00aa00; } </style>', 4096) : '';
        echo ($echo) ? str_pad(date('Y-m-d H:i:s').' - Starting sync', 4096) : '';
        echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Checking folders', 4096) : '';
        $this->_create_folders_iterator($folder, $echo);
        echo ($echo) ? str_pad('<br>'.date('Y-m-d H:i:s').' - Checking images', 4096) : '';
        $this->_create_thumbs_iterator($folder, $echo);
        //$this->_unused_thumbs_iterator();

        echo ($echo) ? '<br><br>Job complete' : '';
    }
}