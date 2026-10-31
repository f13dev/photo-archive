<?php namespace F13Dev\PhotoArchive\App\Models;

class Tags extends \F13Dev\PhotoArchive\App\Models\Database
{
    public function __construct()
    {
        parent::__construct();
    }

    public function select_all_tags($image = false)
    {
        $sql = "SELECT db.id, db.tag, db.mother_tag, db.father_tag, db.step_mother_tag, db.step_father_tag, db.gender, db.date_of_birth, db.date_of_death, db.other_names, db.maiden_names, COUNT(ft.tag_id) as used ";
        if ($image) {
            $sql .= ", (
                    SELECT CONCAT(f.folder_name, f.file_name) 
                    FROM file_tag AS t
                    LEFT JOIN files AS f ON (f.id = t.file_id)
                    WHERE t.tag_id = db.id
                    ORDER BY RAND()
                    LIMIT 1
                ) AS image ";
        }
        $sql .= "FROM tags db
                LEFT JOIN file_tag AS ft ON (db.id = ft.tag_id)
                GROUP BY db.id
                ORDER BY db.tag;";

        $sth = $this->get_dbc()->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update_tag($id, $tag, $mother, $father, $date_of_birth, $date_of_death, $gender, $maiden_names)
    {
        $sql = "UPDATE tags 
                SET tag = :tag, mother_tag = :mother_tag, father_tag = :father_tag, date_of_birth = :date_of_birth, date_of_death = :date_of_death, gender = :gender, maiden_names = :maiden_names
                WHERE id = :id;";
        
        $sth = $this->get_dbc()->prepare($sql);

        $resp = $sth->execute(array(
            'id' => $id,
            'tag' => $tag,
            'mother_tag' => $mother,
            'father_tag' => $father,
            'date_of_birth' => $date_of_birth,
            'date_of_death' => $date_of_death,
            'gender' => $gender,
            'maiden_names' => $maiden_names,
        ));

        //$sth->debugDumpParams();

        return $resp;
    }

    public function delete_tag($id) 
    {
        $sql = "DELETE FROM tags
                WHERE id = :id;";
        
        $sth = $this->get_dbc()->prepare($sql);
        $sth->execute(array('id' => $id));
    }
} 