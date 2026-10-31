<?php namespace F13Dev\PhotoArchive\App\Models;

use Exception;
use PDOException;

class Database 
{
    private $dbc;

    public function __construct()
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            try {
                $this->dbc = new \PDO('mysql:host='.PHOTO_ARCHIVE_DB_HOST.';dbname='.PHOTO_ARCHIVE_DB_DATABASE, PHOTO_ARCHIVE_DB_USER, PHOTO_ARCHIVE_DB_PASSWORD);
            } catch (Exception $e) {
                echo "<span class='database-error'>Database connection failed: ".$e->getMessage()."</span>";
                die();
            } catch(PDOException $e) {
                die ($e);
            }
        }
    }

    public function get_dbc()
    {
        return $this->dbc;
    }

    public function select_folder_data($folder)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $sql = 'SELECT id, folder_name, file_name, description, exif_data, raw_file_name
                FROM files
                WHERE folder_name = :folder';
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('folder' => $folder));
            $resp = $sth->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($resp as $key => $image) {
                $resp[$key]['tags'] = $this->select_tags($image['id']);
            }

            return $resp;
        }

        return array();
    }

    public function select_file_data($folder, $file)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $sql = "SELECT db.id, db.folder_name, db.file_name, db.description, exif_data, raw_file_name
                    FROM files db
                    WHERE db.folder_name = :folder AND db.file_name = :file";
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('folder' => $folder, 'file' => $file));
            $resp = $sth->fetch(\PDO::FETCH_ASSOC);

            if ($resp) {
                $resp['tags'] = $this->select_tags($resp['id']);
                return $resp;
            }
            return false;
        }
    }

    public function select_tags($file_id) 
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $sql = "SELECT db.file_id, db.tag_id, t.tag
                    FROM file_tag db
                    LEFT JOIN tags AS t ON (t.id = db.tag_id)
                    WHERE db.file_id = :file_id
                    ORDER BY t.tag";
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('file_id' => $file_id));
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function update_tags($folder, $file, $tags)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            // get the file ID
            $id = $this->insert_file($folder, $file);
            if (!$id) {
                return;
            }
            // Remove current tags
            $sql = "DELETE FROM file_tag
                    WHERE file_id = :id;";
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('id' => $id));
            // Insert new tags
            // Foreach tag run a similar query as get the file ID
            if (is_array($tags) && !empty($tags)) {
                foreach ($tags as $tag) {
                    $tag_id = $this->insert_new_tag(trim($tag));
                    if ($tag_id) {
                        // Insert the file_tag
                        $sql = "INSERT INTO file_tag
                                    (file_id, tag_id)
                                VALUES
                                    (:file_id, :tag_id);";
                        $sth = $this->dbc->prepare($sql);
                        $sth->execute(array('file_id' => $id, 'tag_id' => $tag_id));
                    }
                }
            }
        }
    }

    public function select_tag_search($text)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $sql = "SELECT db.tag
                    FROM tags db
                    WHERE db.tag LIKE :text
                    LIMIT 5;";
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('text' => '%'.$text.'%'));
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function select_tags_by_file($folder, $file)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $sql = "SELECT db.file_id, db.tag_id, t.tag
                    FROM file_tag db
                    LEFT JOIN tags AS t ON (t.id = db.tag_id)
                    LEFT JOIN files AS f ON (f.id = db.file_id)
                    WHERE f.folder_name = :folder AND f.file_name = :file;";
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('folder' => $folder, 'file' => $file));
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function insert_file($folder, $file, $exif = null, $raw = null)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $sql = "SELECT db.id
                    FROM files db
                    WHERE db.file_name = :file_name AND db.folder_name = :folder_name";
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('file_name' => $file, 'folder_name' => $folder));
            $resp = $sth->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($resp)) { 
                $sql = "INSERT INTO `files`
                            (`file_name`, `folder_name`, `exif_data`, `raw_file_name`)
                        VALUES
                            (?, ?, ?, ?);";
                $sth = $this->dbc->prepare($sql);
                $sth->execute(array($file, $folder, $exif, $raw));
                return $this->dbc->lastInsertId();
            } else {
                return $resp[0]['id'];
            }
        }
        return false;
    }

    public function insert_new_tag($tag)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $sql = "SELECT db.id
                    FROM tags db
                    WHERE db.tag = :tag;";
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('tag' => $tag));
            $resp = $sth->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($resp)) {
                $sql = "INSERT INTO tags
                            (tag)
                        VALUES
                            (:tag);";
                $sth = $this->dbc->prepare($sql);
                $sth->execute(array('tag' => $tag));
                return $this->dbc->lastInsertId();
            } else {
                return $resp[0]['id'];
            }
        }
        return false;
    }

    public function insert_tag($folder, $file, $tag)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            // Ensure the record exists
            $this->insert_file($folder, $file);
        }
    }

    public function insert_description($folder, $file, $description)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            // Ensure the record exists
            $this->insert_file($folder, $file);
            $sql = "UPDATE files
                    SET description = :description
                    WHERE file_name = :file_name AND folder_name = :folder_name;";
            $sth = $this->dbc->prepare($sql);
            return ($sth->execute(array('description' => $description, 'file_name' => $file, 'folder_name' => $folder)));
        }
    }

    public function select_description($folder, $file)
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $sql = "SELECT db.description
                    FROM files db
                    WHERE db.folder_name = :folder_name AND db.file_name = :file_name;";
            $sth = $this->dbc->prepare($sql);
            $sth->execute(array('folder_name' => $folder, 'file_name' => $file));
            return $sth->fetch(\PDO::FETCH_ASSOC);
        }
    }

    public function search($term) 
    {
        if (PHOTO_ARCHIVE_USE_DB) {
            $terms = explode(',', $term);

            $sql = "SELECT f.id, f.file_name, f.folder_name, f.description, f.exif_data, f.raw_file_name
            FROM file_tag ft 
            LEFT JOIN files AS f ON (f.id = ft.file_id)
            JOIN tags AS t ON (t.id = ft.tag_id)
            GROUP BY ft.file_id
            HAVING ";
            $params = array();
            foreach ($terms as $i => $t) {
                if (!empty(trim($t))) {
                    $sql .= " SUM(CASE WHEN t.tag LIKE :term".$i." THEN 1 ELSE 0 END) > 0 AND ";
                    $params['term'.$i] = '%'.trim($t).'%';
                }
            }
            $sql = substr($sql, 0, -4).";";

            $sth = $this->dbc->prepare($sql);
            $sth->execute($params);
            $resp = $sth->fetchAll(\PDO::FETCH_ASSOC);

            $count = 1;
            $images = array();
            foreach ($resp as $i => $image) {
                $image['tags'] = $this->select_tags($image['id']);
                $image_url = PHOTO_ARCHIVE_IMAGES_URL.$image['folder_name'].$image['file_name'];
                $images[basename($image_url)]['image'] = $image_url;
                $images[basename($image_url)] = \F13Dev\PhotoArchive\App\Models\Directory::image_array($image_url, $count, $resp, $image);
                $count++;
            }
            return $images;
        }
    }
}