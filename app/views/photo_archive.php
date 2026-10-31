<?php namespace F13Dev\PhotoArchive\App\Views;

class Photo_archive
{
    public $label_close;
    public $label_download;
    public $label_download_raw;
    public $label_info;
    public $label_next;
    public $label_photo_archive;
    public $label_previous;

    public function __construct($params = array())
    {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        $this->label_close = 'Close';
        $this->label_download = 'Download original file';
        $this->label_download_raw = 'Download raw file';
        $this->label_info = 'Info';
        $this->label_next = 'Next';
        $this->label_photo_archive = 'Photo archive';
        $this->label_previous = 'Previous';
    }

    public function _human_size($bytes)
    {
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
        return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
    }

    public function _container($content)
    {
        $v = '<!DOCTYPE html>';
        $v .= '<html lang="en">';
            $v .= '<head>';
                $v .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
                $v .= '<link rel="shortcut icon" href="'.PHOTO_ARCHIVE_URL.'app/image/favicon.ico">';
                $v .= '<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>';
                $v .= '<script src="'.PHOTO_ARCHIVE_URL.'app/js/photo_archive'.(PHOTO_ARCHIVE_MINIFY ? '.min' : '').'.js?v='.PHOTO_ARCHIVE_VERSION.'" id="pa_script" data-base_url="'.PHOTO_ARCHIVE_URL.'"></script>';
                $v .= '<script src="'.PHOTO_ARCHIVE_URL.'app/js/familytree.js?v='.PHOTO_ARCHIVE_VERSION.'"></script>';
                $v .= '<script>image_url = "'.rtrim(PHOTO_ARCHIVE_IMAGES_URL, '/').'";</script>';
                $v .= '<script>mid_url = "'.rtrim(PHOTO_ARCHIVE_MID_URL, '/').'";</script>';
                $v .= '<script>thumb_url = "'.rtrim(PHOTO_ARCHIVE_THUMB_URL, '/').'";</script>';
                $v .= '<script>base_url = "'.PHOTO_ARCHIVE_URL.'";</script>';
                $v .= '<link rel="stylesheet" href="'.PHOTO_ARCHIVE_URL.'app/css/photo_archive'.(PHOTO_ARCHIVE_MINIFY ? '.min' : '').'.css?v='.PHOTO_ARCHIVE_VERSION.'">';
                $v .= '<title>'.$this->label_photo_archive.'</title>';
            $v .= '</head>';
            $v .= '<body>';
                $v .= '<header>';
                    $v .= '<a href="'.PHOTO_ARCHIVE_URL.'"><h1>Photo archive</h1></a>';

                    if (!(PHOTO_ARCHIVE_USE_DB && PHOTO_ARCHIVE_USE_ACCOUNTS) || (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true)) {
                        $v .= '<span id="viewing">Viewing: /</span>';
                        $v .= '<span id="file_count">0</span>';
                        $v .= '<nav>';
                            $page = filter_input(INPUT_GET, 'page');
                            $v .= '<ul>';
                                $v .= '<li>';
                                    $v .= '<a href="'.PHOTO_ARCHIVE_URL.'" '.($page != 'tree' && $page != 'tags' ? 'class="current"' : '').'>Photos</a>';
                                $v .= '</li>';
                                $v .= '<li>';
                                    $v .= '<a href="'.PHOTO_ARCHIVE_URL.'tags" '.($page == 'tags' ? 'class="current"' : '').'>Tags</a>';
                                $v .= '</li>';
                                $v .= '<li>';
                                    $v .= '<a href="'.PHOTO_ARCHIVE_URL.'tree" '.($page == 'tree' ? 'class="current"' : '').'>Family tree</a>';
                                $v .= '</li>';
                                $v .= '<li>';
                                    $v .= '<a href="'.PHOTO_ARCHIVE_URL.'?logout=1">Log out</a>';
                                $v .= '</li>';
                            $v .= '</ul>';
                        $v .= '</nav>';
                        if (PHOTO_ARCHIVE_USE_DB) {
                            $v .= '<img src="'.PHOTO_ARCHIVE_URL.'app/image/search.svg" id="search-toggle" title="Search...">';
                            $v .= '<form id="search" method="POST" data-href="'.PHOTO_ARCHIVE_URL.'">';
                                $v .= '<input type="hidden" name="ajax" value="true">';
                                $v .= '<input type="hidden" name="do" value="search">';
                                $v .= '<input type="text" name="search_term" autocomplete="off" data-ajax="'.PHOTO_ARCHIVE_AJAX.'">';
                                $v .= '<input type="submit" value="Go">';
                                $v .= '<div id="search_term_suggest"></div>';
                            $v .= '</form>';
                        }
                    }
                $v .= '</header>';

                $v .= '<div id="container">';
                    $v .= $content;
                $v .= '</div>';

                $v .= '<footer style="color: #fff; text-align: center; line-height: 30px;">';
                    if (!(PHOTO_ARCHIVE_USE_DB && PHOTO_ARCHIVE_USE_ACCOUNTS) || (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true)) {
                        $v .= 'Photo storage: ';
                        $v .= $this->_human_size(disk_total_space(PHOTO_ARCHIVE_FOLDER));
                        $v .= ' | Free space: ';
                        $v .= $this->_human_size(disk_free_space('/media/reddeath'))  ;
                        //$v .= ' | <a href="?page=tags" class="ajax-link" data-tags="true" data-href="'.PHOTO_ARCHIVE_URL.'/?ajax=true&do=tags" data-target="#container" style="color: #fff">Tags</a>';
                        //$v .= ' | <a href="/tree" style="color: #fff;">Family tree</a>';
                        //$v .= ' | <a href="?logout=1" style="color: #fff;">Logout</a>';
                        $v .= ' | <a href="'.PHOTO_ARCHIVE_URL.'/?ajax=1&do=resync_thumbs" id="sync" class="resync" data-target="#container" data-href="'.PHOTO_ARCHIVE_AJAX.'do=resync_thumbs" style="color: #fff;">Re-sync gallery</a>';
                    }
                $v .= '</footer>';

                $v .= '<div id="lightbox">';
                    $v .= '<img id="lightbox-next" class="ajax-image" data-lightbox_nav="true" src="'.PHOTO_ARCHIVE_URL.'app/image/arrow-right-circle.svg" title="'.$this->label_next.'">';
                    $v .= '<img id="lightbox-prev" class="ajax-image" data-lightbox_nav="true" src="'.PHOTO_ARCHIVE_URL.'app/image/arrow-left-circle.svg" title="'.$this->label_previous.'">';
                    $v .= '<img id="lightbox-close" src="'.PHOTO_ARCHIVE_URL.'app/image/x-circle.svg" title="'.$this->label_close.'">';
                    $v .= '<a id="lightbox-download-link" download><img id="lightbox-download" src="'.PHOTO_ARCHIVE_URL.'app/image/download.svg" title="'.$this->label_download.'"></a>';
                    $v .= '<a id="lightbox-download-raw-link" download><img id="lightbox-download-raw" src="'.PHOTO_ARCHIVE_URL.'app/image/download-raw.svg" title="'.$this->label_download_raw.'"></a>';
                    $v .= '<img id="lightbox-info" src="'.PHOTO_ARCHIVE_URL.'app/image/info.svg" title="'.$this->label_info.'">';
                    $v .= '<div id="lightbox-container" style="transition: background-image 0.4s; background-size: cover; background-position: contain;">';
                        $v .= '<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; backdrop-filter: blur(50px); background: #fff5"></div>';
                        $v .= '<div id="lightbox-content" style="z-index: 2;">';
                        $v .= '</div>';
                    $v .= '</div>';
                    $v .= '<div id="lightbox-caption">';
                        $v .= '<div id="lightbox-caption-text"></div>';
                        if (PHOTO_ARCHIVE_USE_DB) {
                            $v .= '<hr class="caption-rule" />';
                            $v .= '<p>';
                                $v .= '<b>About <small>[ALT + A]</small>:</b>';
                                $v .= '<img src="'.PHOTO_ARCHIVE_URL.'app/image/pencil.svg" 
                                            style="height:14px; float: right; cursor: pointer;" 
                                            title="Edit description"
                                            id="edit_description"
                                            data-db-id=""
                                            data-file-name=""
                                            data-folder-name=""
                                            data-ajax-url="'.PHOTO_ARCHIVE_AJAX.'do=edit_description&"
                                \>';
                            $v .= '</p>';
                            $v .= '<div id="lightbox-caption-description"></div>';
                            $v .= '<hr class="caption-rule" />';
                            $v .= '<p>';
                                $v .= '<b>Tags [ALT + T]:</b>';
                                $v .= '<img src="'.PHOTO_ARCHIVE_URL.'app/image/pencil.svg" 
                                            style="height:14px; float: right; cursor: pointer;" 
                                            title="Edit tags"
                                            id="edit_tags"
                                            data-db-id=""
                                            data-file-name=""
                                            data-folder-name=""
                                            data-ajax-url="'.PHOTO_ARCHIVE_AJAX.'do=edit_tags&"
                                    >';
                            $v .= '</p>';
                            $v .= '<div id="lightbox-caption-tags"></div>';
                        }
                    $v .= '</div>';
                $v .= '</div>';
                $v .= '<div id="lightbox-loading">';
                    $v .= '<img id="lightbox-loading-image" src="'.PHOTO_ARCHIVE_URL.'app/image/loading.gif" alt="Loading...">';
                $v .= '</div>';
            $v .= '</body>';
        $v .= '</html>';

        return $v;
    }

    public function display()
    {
        $v = '';
        $v .= '<div id="folders_container">';
            if (!empty($this->items->parent)) {
                $v .= '<a href="?dir='.str_replace('/', '>', $this->items->parent).'" data-folder="'.$this->items->parent.'" class="ajax-link" data-target="#container" data-href="'.PHOTO_ARCHIVE_AJAX.'do=load_folder&folder='.$this->items->parent.'" title="View parent folder">';
                    $v .= '<div class="item">';
                        $v .= '<div class="folder folder-up"></div>';
                        $v .= '<div class="label">Parent folder</div>';
                    $v .= '</div>';
                $v .= '</a>';
            }

            if (property_exists($this->items, 'folders')) {
                foreach ($this->items->folders as $folder => $dir) {
                    $v .= '<a href="?dir='.$dir.'" data-folder="'.$dir.'" class="ajax-link" data-target="#container" data-href="'.PHOTO_ARCHIVE_AJAX.'do=load_folder&folder='.$dir.'" title="View '.$folder.'">';
                        $v .= '<div class="item">';
                            $v .= '<div class="folder"></div>';
                            $v .= '<div class="label">'.$folder.'</div>';
                        $v .= '</div>';
                    $v .= '</a>';
                }
            }
        $v .= '</div>';

        $v .= '<div class="images_container">';
            $total = count((array)$this->items->images);
            foreach ($this->items->images as $image) {
                $complete = false; // Guilty until proven innocent
                if (file_exists($image['paths']['thumb']) && ($image['type'] == 'video' || file_exists($image['paths']['mid']))) {
                    $complete = true;
                }
                $v .= '<a id="image-'.$image['number'].'" 
                        data-ext="'.$image['ext'].'" 
                        data-number="'.$image['number'].'" 
                        data-total="'.$total.'" 
                        class="ajax-image type_'.$image['type'].'" 
                        href="'.$image['mid'].'" 
                        title="View '.$image['name'].'" 
                        data-ajax="popup" 
                        data-gallery="'.$this->dir.'" 
                        data-file="'.$image['name'].'"
                        data-folder="'.$image['folder'].'"
                        data-exif="<p><b>'.$image['number'].' of '.$total.'</b></p>'.$image['exif'].'"
                        data-description="'.base64_encode(htmlentities($image['description'], ENT_QUOTES)).'"
                        data-tags="'.base64_encode(json_encode($image['tags'])).'"
                        data-db-id="'.$image['db_id'].'"
                        data-raw="'.$image['raw'].'"
                    >';
                    $v .= '<div class="item">';
                        $v .= ($complete) ? '' : '<div class="requires_generation"></div>';
                        $v .= '<div class="image '.(!$complete ? 'missing-image' : '').'" '.($complete ? 'style="background-image: url(\''.str_replace(array(' ', '\'', '"'), array('%20', '%27', '%22'), $image['thumb']).'\');"' : '').'>';
                        $v .= '</div>';
                        $v .= '<div class="label">'.$image['name'].'</div>';
                    $v .= '</div>';
                $v .= '</a>';
            }
        $v .= '</div>';

        $v .= '<script>document.getElementById("file_count").innerHTML = '.$total.';</script>';
        return (($this->container) ? $this->_container($v) : $v);
    }

    public function edit_description()
    {
        $v = '<form id="edit_description_form" class="ajax-form" data-target="#lightbox-caption-description" method="POST" data-url="'.PHOTO_ARCHIVE_URL.'">';
            $v .= '<input type="hidden" name="submit" value="1">';
            $v .= '<input type="hidden" name="ajax" value="true">';
            $v .= '<input type="hidden" name="do" value="edit_description">';
            $v .= '<input type="hidden" name="file" value="'.$this->file.'">';
            $v .= '<input type="hidden" name="folder" value="'.$this->folder.'">';
            $v .= '<input type="hidden" name="number" value="'.$this->number.'">';
            $v .= '<input type="hidden" name="token" value="'.create_token('edit_description'.$this->file).'">';
            $v .= '<textarea name="description" id="about_input" style="width: 100%; height: 100px; resize: vertical;">'.esc_out($this->description).'</textarea>';
            $v .= '<input type="submit" value="Save" >';
            $v .= ($this->submit) ? 'Submitted' : '';
        $v .= '</form>';

        return $v;
    }

    public function edit_tags()
    {
        $v = '<form id="edit_tags_form" class="ajax-form" data-target="#lightbox-caption-tags" method="POST" data-url="'.PHOTO_ARCHIVE_URL.'" autocomplete="off">';
            $v .= '<input type="hidden" name="submit" value="1">';
            $v .= '<input type="hidden" name="ajax" value="true">';
            $v .= '<input type="hidden" name="do" value="edit_tags">';
            $v .= '<input type="hidden" name="file" value="'.$this->file.'">';
            $v .= '<input type="hidden" name="folder" value="'.$this->folder.'">';
            $v .= '<input type="hidden" name="number" value="'.$this->number.'">';
            $v .= '<input type="hidden" name="token" value="'.create_token('edit_tags'.$this->file).'">';

            $v .= '<div id="add_tags">';
                foreach ($this->tags as $tag) {
                    $v .= '<span>';
                        $v .= '<input type="hidden" name="tags[]" value="'.esc_out($tag).'">';
                        $v .= esc_out($tag);
                    $v .= '</span>';
                }
                $v .= '<input type="text" value="" data-ajax="'.PHOTO_ARCHIVE_AJAX.'" placeholder="Add a tag" id="add_tags_input" style="box-sizing: border-box;">';
                $v .= '<div id="add_tags_suggest"></div>';
            $v .= '</div>';

            $v .= '<input type="submit" value="Save" >';
            $v .= ($this->submit) ? 'Submitted' : '';
        $v .= '</form>';

        return $v;
    }
}
