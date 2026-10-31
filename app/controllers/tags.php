<?php namespace F13Dev\PhotoArchive\App\Controllers;

class Tags
{
    public $request_method;

    public function __construct()
    {
        $this->request_method = ($_SERVER['REQUEST_METHOD'] === 'POST') ? INPUT_POST : INPUT_GET;
    }

    public function tags()
    {
        $m = new \F13Dev\PhotoArchive\App\Models\Tags();

        $del = filter_input($this->request_method, 'del');

        if ($del && validate_token('del-tag'.$del)) {
            $m->delete_tag($del);
        }

        $data = $m->select_all_tags(true);

        $v = new \F13Dev\PhotoArchive\App\Views\Tags(array(
            'data' => $data,
        ));

        return $v->tags();
    }

    public function update_tag_data()
    {
        $id = filter_input($this->request_method, 'id');
        $tag = filter_input($this->request_method, 'tag');
        $mother = (int) filter_input($this->request_method, 'mother');
        $father = (int) filter_input($this->request_method, 'father');
        $date_of_birth = filter_input($this->request_method, 'date_of_birth');
        $date_of_death = filter_input($this->request_method, 'date_of_death');
        $gender = filter_input($this->request_method, 'gender');
        $maiden_names = filter_input($this->request_method, 'maiden_names');

        $date_of_birth = (!$date_of_birth || $date_of_birth == '0000-00-00') ? NULL : $date_of_birth;
        $date_of_death = (!$date_of_death || $date_of_death == '0000-00-00') ? NULL : $date_of_death;

        $mother = ($mother) ? $mother : NULL;
        $father = ($father) ? $father : NULL;

        $m = new \F13Dev\PhotoArchive\App\Models\Tags();
        $resp = $m->update_tag($id, $tag, $mother, $father, $date_of_birth, $date_of_death, $gender, $maiden_names);

        return ($resp) ? 'Tag data updated' : 'Error: Could not update tag data';
    }
}