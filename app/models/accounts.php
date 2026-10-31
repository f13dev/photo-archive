<?php namespace F13Dev\PhotoArchive\App\Models;

class Accounts extends \F13Dev\PhotoArchive\App\Models\Database
{
    public function __construct()
    {
        parent::__construct();
    }

    public function select_user_by_username($username)
    {
        $sql = 'SELECT id, username, password
                FROM accounts
                WHERE username = :username';
        $sth = $this->get_dbc()->prepare($sql);
        $sth->execute(array('username' => $username));
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function select_user_by_id($id)
    {
        $sql = 'SELECT id, username, password
                FROM accounts
                WHERE id = :id';
        $sth = $this->get_dbc()->prepare($sql);
        $sth->execute(array('id' => $id));
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function set_password($id, $old_password, $new_password)
    {
        $account = $this->select_user_by_id($id);

        if ($this->verify_password($account['password'], $old_password)) {
            $new_password = $this->_hash_password($new_password);

            $sql = 'UPDATE accounts
                    SET password = :password
                    WHERE id = :id';
            $sth = $this->get_dbc()->prepare($sql);
            $sth->execute(array('password' => $new_password, 'id' => $id));
            return (bool) $sth->row_count();
        }
        return false;
    }

    public function verify_password($hashed, $raw)
    {
        return (bool) $hashed == $this->_hash_password($raw);
    }

    public function _hash_password($password)
    {
        return hash('sha1', PHOTO_ARCHIVE_SALT.$password);
    }

    public function login($username, $password)
    {
        $account = $this->select_user_by_username($username);

        if ($account && $account['password'] == $this->_hash_password($password)) {
            return true;
        }
        return false;
    }

    public function generate_session_salt()
    {
        return bin2hex(random_bytes(32));    
    }

    public function generate_user_token()
    {
        return hash('sha1', $_SESSION['username'].$_SESSION['ID'].$_SESSION['salt'].PHOTO_ARCHIVE_SALT);
    }
}