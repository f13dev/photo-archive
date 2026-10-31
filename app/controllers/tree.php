<?php namespace F13Dev\PhotoArchive\App\Controllers;

class Tree 
{
    public function family_tree()
    {
        $m = new \F13Dev\PhotoArchive\App\Models\Tags();
        $data = $m->select_all_tags(true);

        $v = new \F13Dev\PhotoArchive\App\Views\Tree(array(
            'data' => $data,
        ));

        return $v->family_tree();
    }
}