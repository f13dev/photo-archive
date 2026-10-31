<?php namespace F13Dev\PhotoArchive\App\Views;

class Tags extends \F13Dev\PhotoArchive\App\Views\Photo_archive
{
    public function __construct($params = array())
    {
        parent::__construct($params);
    }

    public function tags()
    {
        $v = '<div id="tags_container">';
            foreach ($this->data as $i => $t) {
                $v .= '<form class="ajax-form" data-url="'.PHOTO_ARCHIVE_AJAX.'do=update_tag_data" method="POST" data-target="#result'.$t['id'].'">';
                    $v .= '<div>';

                        if ($t['image']) {
                            $img_url = PHOTO_ARCHIVE_IMAGES_URL.$t['image'];
                            //if (file_exists(PHOTO_ARCHIVE_THUMB_FOLDER.$t['image'])) {
                            //    $img_url = PHOTO_ARCHIVE_THUMB_URL.$t['image'];
                            //} else 
                            if (file_exists(PHOTO_ARCHIVE_MID_FOLDER.$t['image'])) {
                                $img_url = PHOTO_ARCHIVE_MID_URL.$t['image'];
                            }

                            $v .= '<img src="'.$img_url.'" style="position: absolute; left: 336px; height: 276px; width: 276px; object-fit: cover;">';
                        }

                        $v .= '<div id="result'.$t['id'].'"></div>';
                        $v .= '<input type="hidden" name="ajax" value="true">';
                        $v .= '<input type="hidden" name="id" value="'.$t['id'].'">';
                        $v .= '<input type="hidden" name="do" value="update_tag_data">';

                        $v .= '<label for="tag'.$t['id'].'">Tag</label> ';
                        $v .= '<input type="text" id="tag'.$t['id'].'" name="tag" value="'.esc_out($t['tag']).'">';
                        $v .= '<div class="options">';
                            $v .= '<label for="mother'.$t['id'].'">Mother</label>';
                            $v .= '<select name="mother" id="mother'.$t['id'].'">';
                                $v .= '<option></option>';
                                foreach ($this->data as $m) {
                                    $v .= '<option value="'.$m['id'].'" '.($t['mother_tag'] == $m['id'] ? 'selected="selected"' : '').'>'.esc_out($m['tag']).'</option>';
                                }
                            $v .= '</select><br>';
                            $v .= '<label for="father'.$t['id'].'">Father</label>';
                            $v .= '<select name="father" id="father'.$t['id'].'">';
                                $v .= '<option></option>';
                                foreach ($this->data as $f) {
                                    $v .= '<option value="'.$f['id'].'" '.($t['father_tag'] == $f['id'] ? 'selected="selected"' : '').'>'.esc_out($f['tag']).'</option>';
                                }
                            $v .= '</select><br>';
                            $v .= '<label for="date_of_birth'.$t['id'].'">Date of birth</label>';
                            $v .= '<input type="date" name="date_of_birth" id="date_of_birth'.$t['id'].'" value="'.$t['date_of_birth'].'"><br>';
                            $v .= '<label for="date_of_death'.$t['id'].'">Date of death</label>';
                            $v .= '<input type="date" name="date_of_death" id="date_of_death'.$t['id'].'" value="'.$t['date_of_death'].'"><br>';
                            $v .= '<label for="gender'.$t['id'].'">Gender</label>';
                            $v .= '<select name="gender" id="gender'.$t['id'].'">';
                                $v .= '<option></option>';
                                $v .= '<option value="male" '.($t['gender'] == 'male' ? 'selected="selected"' : '').'>Male</option>';
                                $v .= '<option value="female" '.($t['gender'] == 'female' ? 'selected="selected"' : '').'>Female</option>';
                                $v .= '<option value="pet" '.($t['gender'] == 'pet' ? 'selected="selected"' : '').'>Pet</option>';
                            $v .= '</select><br>';
                            $v .= '<label for="maiden_names'.$t['id'].'">Maiden names</label>';
                            $v .= '<input type="text" name="maiden_names" id="maiden_names'.$t['id'].'" value="'.$t['maiden_names'].'"><br>';
                            $v .= '<input type="submit">';
                        $v .= '</div>';
                    $v .= '</div>';
                $v .= '</form>';
                $v .= 'Used '.$t['used'].' times ';
                $v .= '<a class="ajax-link" href="#" data-href="'.PHOTO_ARCHIVE_URL.'?ajax=true&do=search&tags[]='.urlencode(esc_out($t['tag'])).'" data-term="'.esc_out($t['tag']).'" data-target="#container">Show results</a> ';
                if ($t['used'] == 0) {
                    $token = create_token('del-tag'.$t['id']);
                    $v .= '<a class="ajax-link" href="?page=tags&del='.$t['id'].'&token='.$token.'" data-tags="true" data-href="'.PHOTO_ARCHIVE_URL.'?ajax=true&do=tags&del='.$t['id'].'&token='.$token.'" data-target="#container">Delete tag</a>';
                }
                $v .= '<hr />';
            }
        $v .= '</div>';

        return (defined('AJAX') && AJAX) ? $v : $this->_container($v);

        $v = '<div id="tags_container">';
            $v .= '<form method="post" action="?page=tags">';
                $v .= '<input type="hidden" name="submit" value="1">';
                foreach ($this->data as $i => $t) {
                    $v .= '<div>';
                        $v .= '<input type="hidden" name="id[]" value="'.$t['id'].'">';
                        $v .= '<label for="tag'.$i.'">Tag</label> ';
                        $v .= '<input type="text" id="tag'.$i.'" name="tag[]" value="'.esc_out($t['tag']).'"> ';
                        $v .= 'Used '.$t['used'].' times ';
                        $v .= '<a class="ajax-link" href="#" data-href="'.PHOTO_ARCHIVE_URL.'?ajax=true&do=search&tags[]='.urlencode(esc_out($t['tag'])).'" data-term="'.esc_out($t['tag']).'" data-target="#container">Show results</a> ';
                        if ($t['used'] == 0) {
                            $token = create_token('del-tag'.$t['id']);
                            $v .= '<a class="ajax-link" href="?page=tags&del='.$t['id'].'&token='.$token.'" data-tags="true" data-href="'.PHOTO_ARCHIVE_URL.'?ajax=true&do=tags&del='.$t['id'].'&token='.$token.'" data-target="#container">Delete tag</a>';
                        }
                        $v .= '<hr />';
                    $v .= '</div>';
                }
            $v .= '</form>';
        $v .= '</div>';

        return (defined('AJAX') && AJAX) ? $v : $this->_container($v);
    }
}