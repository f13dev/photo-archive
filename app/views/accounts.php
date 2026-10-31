<?php namespace F13Dev\PhotoArchive\App\Views;

class Accounts extends \F13Dev\PhotoArchive\App\Views\Photo_archive
{
    public function __construct($params = array())
    {
        parent::__construct($params);
    }

    public function login()
    {
        $v = '<form class="login" method="post">';
            $v .= '<div>';
                $v .= '<input type="hidden" name="submit" value="1">';
                $v .= $this->msg;
                $v .= '<label for="username">Username</label><br>';
                $v .= '<input type="text" name="username" id="username" autocomplete="off"><br>';
                $v .= '<label for="password">Password</label><br>';
                $v .= '<input type="password" name="password" id="password" autocomplete="off"><br>';
                $v .= '<input type="submit" value="Login">';
            $v .= '</div>';
        $v .= '</form>';

        return $this->_container($v);
    }
}