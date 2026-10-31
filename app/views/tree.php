<?php namespace F13Dev\PhotoArchive\App\Views;

class Tree 
{
    public function __construct($params = array())
    {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function family_tree()
    {
        $v = '<link rel="stylesheet" href="/app/css/f13tree.css?v='.PHOTO_ARCHIVE_VERSION.'">';
        $v .= '<div style="position: absolute; top: 114px; bottom: 36px; left: 0; right: 0; overflow: auto; padding: 10px;">';
            $v .= '<div id="zoom">';
                $v .= '<div id="zoomout">Out</div> ';
                $v .= '<div id="zoomin">In</div>';
            $v .= '</div>';
            $v .= '<div style="width: 800vw; height: 50vh; margin-top: -10px; margin-left: -10px; width: max-content;" class="tree" id="tree_recursive" ></div>';
        $v .= '</div>';

        $v .= '<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>';
        $v .= '<script src="/app/js/f13tree.js?v='.PHOTO_ARCHIVE_VERSION.'"></script>';

        $v .= '<script>';
            $members = array();
            foreach ($this->data as $d) {
                $members[$d['id']] = $d;
                $members[$d['id']]['pids'] = array();
            }

            foreach ($members as $d) {
                if ($d['mother_tag'] && $d['father_tag']) {
                    if (!in_array($d['father_tag'], $members[$d['mother_tag']]['pids'])) {
                        $members[$d['mother_tag']]['pids'][] = $d['father_tag'];
                    }
                    if (!in_array($d['mother_tag'], $members[$d['father_tag']]['pids'])) {
                        $members[$d['father_tag']]['pids'][] = $d['mother_tag'];
                    }
                }
            }

            foreach ($members as $m) {
                if (!$m['father_tag'] && !$m['mother_tag'] && empty($m['pids'])) {
                    unset($members[$m['id']]);
                }
            }

            // Create JS array
            $v .= 'let data = [];';

            foreach ($members as $d) {
                $v .= 'data['.$d['id'].'] = {
                    id: "'.$d['id'].'",
                    name: "'.$d['tag'].'",
                    mother: "'.$d['mother_tag'].'",
                    father: "'.$d['father_tag'].'",
                    gender: "'.$d['gender'].'",
                    dob: "'.$d['date_of_birth'].'",
                    dod: "'.$d['date_of_death'].'",
                    image: "'.'https://photos.f13.uk/archive/thumbs/'.ltrim($d['image'], DIRECTORY_SEPARATOR).'",
                    search_url: "'.PHOTO_ARCHIVE_URL.'search/'.urlencode($d['tag']).'",
                    pids: []
                };';
                foreach ($d['pids'] as $pid) {
                    $v .= 'data['.$d['id'].']["pids"]['.$pid.'] = '.$pid.';';
                } 
            }

            $v .= 'let tree = new Family_Tree(data);';

            $v .= 'tree.recursiveShow(17);';

        $v .= '</script>';

        $view = new \F13Dev\PhotoArchive\App\Views\Photo_archive();

        return $view->_container($v);
    }

    public function family_tree_original()
    {
        $v = '<div style="width: calc(100% + 20px); height: calc(100vh - 139px); margin-top: -10px; margin-left: -10px;" id="tree"></div>';

        $v .= '<script>';
            $v .= 'FamilyTree.templates.tommy_female.html = \'<foreignobject class="node" x="10" y="10" width="200" height="100">{val}</foreignobject>\';';
            $v .= 'FamilyTree.templates.tommy_male.html = \'<foreignobject class="node" x="10" y="10" width="200" height="100">{val}</foreignobject>\';';
            $v .= 'var family = new FamilyTree(document.getElementById("tree"), {';
                $v .= 'anim: {func: FamilyTree.anim.outBack, duration: 500},';
                $v .= 'mode: "dark",';
                $v .= 'editForm: {';
                    $v .= 'buttons: {';
                        $v .= 'edit: null,';
                        $v .= 'share: null,';
                    $v .= '}';
                $v .= '},';
                $v .= 'menu: {';
                    $v .= 'pdf: { text: "Export PDF" },';
                    $v .= 'svg: { text: "Export SVG" },';
                    $v .= 'csv: { text: "Export CSV" },';
                    $v .= 'json: { text: "Export JSON" }';
                $v .= '},';

                $v .= 'nodeBinding: {';
                    $v .= 'field_0: "name",';
                    $v .= 'img_0: "img",';
                    $v .= 'html: "html",';
                $v .= '},';
                $v .= 'nodes: [';
                
                    $members = array();
                    // Dupliacte data for working copy
                    foreach ($this->data as $d) {
                        $members[$d['id']] = $d;
                        $members[$d['id']]['pids'] = array();
                    }

                    // Add partner IDs based on childrens parents
                    foreach ($members as $d) {
                        if ($d['mother_tag'] && $d['father_tag']) {
                            if (!in_array($d['father_tag'], $members[$d['mother_tag']]['pids'])) {
                                $members[$d['mother_tag']]['pids'][] = $d['father_tag'];
                            }
                            if (!in_array($d['mother_tag'], $members[$d['father_tag']]['pids'])) {
                                $members[$d['father_tag']]['pids'][] = $d['mother_tag'];
                            }
                        }
                    }

                    // Remove members with no connection 
                    foreach ($members as $m) {
                        if (!$m['father_tag'] && !$m['mother_tag'] && empty($m['pids'])) {
                            unset($members[$m['id']]);
                        }
                    }

                    // Create the JS
                    $i = '';
                    foreach ($members as $m) {
                        $img_url = '';
                        if ($m['image']) {
                            $img_url = PHOTO_ARCHIVE_IMAGES_URL.$m['image'];
                            if (file_exists(PHOTO_ARCHIVE_MID_FOLDER.$m['image'])) {
                                $img_url = PHOTO_ARCHIVE_MID_URL.$m['image'];
                            }
                        }
                        $i .= '{';
                            $i .= 'id: '.$m['id'].',';
                            $i .= (!empty($m['pids'])) ? 'pids: ['.implode(',',$m['pids']).'],' : '';
                            $i .= ($m['mother_tag']) ? 'mid: '.$m['mother_tag'].',' : '';
                            $i .= ($m['father_tag']) ? 'fid: '.$m['father_tag'].',' : '';
                            $i .= ($m['gender']) ? 'gender: "'.$m['gender'].'",' : '';
                            $i .= ($m['date_of_birth']) ? 'birthDate: "'.$m['date_of_birth'].'",' : '';
                            $i .= (isset($img_url) && $img_url) ? 'img: "'.$img_url.'",' : '';
                            $i .= 'link: "'.PHOTO_ARCHIVE_URL.'search/'.str_replace(' ', '+', $m['tag']).'",';
                            $i .= ($m['maiden_names']) ? 'maiden_names: "'.$m['maiden_names'].'",' : '';
                            $i .= 'html: "<div style=\"line-height: 16px; font-weight: bold; color: #fff;\">';
                                $i .= '<a style=\"color: #fff\" href=\"'.PHOTO_ARCHIVE_URL.'search/'.str_replace(' ', '+', $m['tag']).'\">Photos ('.$m['used'].')</a><br>';
                                $i .= ($m['date_of_birth'] ? 'Birth: '.date('F j Y', strtotime($m['date_of_birth'])).'<br>' : '');
                                $i .= ($m['date_of_death'] ? 'Death: '.date('F j Y', strtotime($m['date_of_death'])).'<br>' : '');
                                $i .= ($m['maiden_names'] ? 'Other names: '.$m['maiden_names'].'<br>' : '');
                            $i .= '</div>",';
                            $i .= 'images: '.$m['used'].',';
                            $i .= 'name: "'.$m['tag'].'"';
                        $i .= '},'."\r\n";
                    }

                    $i = rtrim($i, ',');

                    $v .= $i;
                
                $v .= ']';
            $v .= '});';

            $v .= 'family.on(\'field\', function(sender, args) {';
                $v .= 'if (args.name == "birthDate" && args.value) {';
                    $v .= 'var date = new Date(args.value);';
                    $v .= 'var string = date.toLocaleDateString();';
                    $v .= 'var maiden = args.data["maiden_names"];';
                    $v .= 'if (maiden) { string = string + " ("+maiden+")"; };';
                    $v .= 'args.value = string;';
                $v .= '};';
            $v .= '});';

            $v .= 'family.editUI.on(\'show\', function(sender, nodeId){';
                $v .= 'return false;';
            $v .= '});';
        $v .= '</script>';        

        $view = new \F13Dev\PhotoArchive\App\Views\Photo_archive();

        return $view->_container($v);
    }
}