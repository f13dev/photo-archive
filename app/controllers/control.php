<?php namespace F13Dev\PhotoArchive\App\Controllers;

class Control
{
    public $request_method;

    public function __construct()
    {
        $this->request_method = ($_SERVER['REQUEST_METHOD'] === 'POST') ? INPUT_POST : INPUT_GET;
    }

    public function photo_archive()
    {
        $dir = filter_input($this->request_method, 'dir');
        $dir = str_replace('>', DIRECTORY_SEPARATOR, (string) $dir);
        if (empty($dir) || $dir[0] !== DIRECTORY_SEPARATOR) {
            $dir = DIRECTORY_SEPARATOR.$dir;
        } 
        $ajax = filter_input($this->request_method, 'ajax');

        if (!empty($ajax)) {
            $a = new Ajax();
            return $a->do_ajax($ajax);
            die;
        }

        $dir = urldecode($dir);
        if (empty($dir)) {
            $dir = '/';
        }

        $m = new \F13Dev\PhotoArchive\App\Models\Directory();
        $items = $m->get_items($dir);

        $v = new \F13Dev\PhotoArchive\App\Views\Photo_archive(array(
            'items' => $items,
            'dir' => $dir,
            'container' => true,
        ));

        return $v->display();
    }

    public function search_tags()
    {
        //$terms = filter_input($this->request_method, 'terms', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        //if (empty($terms)) {
        //    return $this->photo_archive();
        //}
        //$term = implode(',', $terms);

        $dir = filter_input($this->request_method, 'term');

        //$dir = implode(', ', $terms);

        $m = new \F13Dev\PhotoArchive\App\Models\Database();

        $results = $m->search($dir);

        $items = new \stdClass();
        $items->images = $results;
        $items->parent = '/';

        $v = new \F13Dev\PhotoArchive\App\Views\Photo_archive(array(
            'items' => $items,
            'dir' => 'Search',
            'container' => true,
        ));

        return $v->display();        
    }

    public function load_folder()
    {
        $folder = filter_input($this->request_method, 'folder');

        $folder = urldecode($folder);
        if (empty($folder)) {
            $folder = '/';
        }

        $m = new \F13Dev\PhotoArchive\App\Models\Directory();
        $items = $m->get_items($folder);

        $v = new \F13Dev\PhotoArchive\App\Views\Photo_archive(array(
            'items' => $items,
            'dir' => $folder,
            'container' => false,
        ));

        return $v->display();
    }

    public function generate_image()
    {
        $gallery = filter_input($this->request_method, 'gallery');
        $file = filter_input($this->request_method, 'file');

        $original_path = PHOTO_ARCHIVE_IMAGES_FOLDER.$gallery.$file;
        $mid_path = PHOTO_ARCHIVE_MID_FOLDER.$gallery.$file;
        $thumb_path = PHOTO_ARCHIVE_THUMB_FOLDER.$gallery.$file;

        $r = new \F13Dev\PhotoArchive\App\Controllers\Resync();

        $ext = explode('.', $original_path);
        $ext = strtolower(end($ext));

        $vid = false;
        if ($ext == 'mp4' || $ext == 'mov') {
            $vid = true;
            // Generate video thumb
            $thumb_path = $thumb_path.'.png';
            $r->_create_video_image($original_path, $thumb_path);
        } else {
            // Generate image thumb
            $r->_create_image($original_path, $thumb_path, THUMB_X, THUMB_Y, false);
            $r->_create_image($original_path, $mid_path, MID_X, MID_Y, false);
        }

        $mid_url = (file_exists($mid_path)) ? PHOTO_ARCHIVE_MID_URL.ltrim($gallery, '/').$file : PHOTO_ARCHIVE_IMAGES_URL.$gallery.$file;
        $thumb_url = (file_exists($thumb_path)) ? PHOTO_ARCHIVE_THUMB_URL.ltrim($gallery, '/').$file.($vid ? '.png' : '') : $mid_url;

        return json_encode(array(
            'mid' => $mid_url,
            'thumb' => $thumb_url,
        ));

        return $original_path;
    }

    public function edit_tags()
    {
        $file = filter_input($this->request_method, 'file');
        $folder = filter_input($this->request_method, 'folder');
        $number = (int) filter_input($this->request_method, 'number');
        $submit = (int) filter_input($this->request_method, 'submit');
        $tags = filter_input($this->request_method, 'tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        $m = new \F13Dev\PhotoArchive\App\Models\Database();

        if ($submit && validate_token('edit_tags'.$file)) {
            $m->update_tags($folder, $file, $tags);

            $t = '';
            if (is_array($tags) && !empty($tags)) {
                foreach ($tags as $tag) {
                    $t .= '<span class="tag">'.esc_out($tag).'</span>';
                }
            }
            return $t;
        }

        $db_tags = $m->select_tags_by_file($folder, $file);

        $tags = array();
        foreach ($db_tags as $tag) {
            if (is_array($tag) && array_key_exists('tag', $tag)) {
                $tags[] = $tag['tag'];
            }
        }

        $v = new \F13Dev\PhotoArchive\App\Views\Photo_archive(array(
            'tags' => $tags,
            'file' => $file,
            'folder' => $folder,
            'submit' => $submit,
            'number' => $number,
        ));

        return $v->edit_tags();
    }

    public function edit_description()
    {
        $file = filter_input($this->request_method, 'file');
        $folder = filter_input($this->request_method, 'folder');
        $description = filter_input($this->request_method, 'description');
        $number = (int) filter_input($this->request_method, 'number');
        $submit = (int) filter_input($this->request_method, 'submit');

        $m = new \F13Dev\PhotoArchive\App\Models\Database();

        if ($submit && validate_token('edit_description'.$file)) {
            if ($m->insert_description($folder, $file, $description)) {
                return esc_out($description);
            }
        }

        $i = $m->select_description($folder, $file);

        $description = (is_array($i) && array_key_exists('description', $i)) ? $i['description'] : '';
        
        $v = new \F13Dev\PhotoArchive\App\Views\Photo_archive(array(
            'description' => $description,
            'file' => $file,
            'folder' => $folder,
            'submit' => $submit,
            'number' => $number,
        ));

        return $v->edit_description();
    }

    public function suggest_tag()
    {
        $text = filter_input($this->request_method, 'text');

        $m = new \F13Dev\PhotoArchive\App\Models\Database();

        $tags = $m->select_tag_search($text);

        $v = '';
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $v .= '<div>'.esc_out($tag['tag']).'</div>';
            }
        }

        return $v;
    }

    public function search()
    {
        $terms = filter_input($this->request_method, 'tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $term = implode(',', $terms);
        
        $m = new \F13Dev\PhotoArchive\App\Models\Database();

        $results = $m->search($term);

        $items = new \stdClass();
        $items->images = $results;
        $items->parent = '/';

        $v = new \F13Dev\PhotoArchive\App\Views\Photo_archive(array(
            'items' => $items,
            'dir' => 'Search',
            'container' => false,
        ));

        return $v->display();
    }
} 
