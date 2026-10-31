<?php namespace F13Dev\PhotoArchive\App\Controllers;

class Accounts
{
    public $request_method;

    public function __construct()
    {
        $this->request_method = ($_SERVER['REQUEST_METHOD'] === 'POST') ? INPUT_POST : INPUT_GET;
    }

    public function login()
    {
        $submit = (int) filter_input($this->request_method, 'submit');

        $msg = '';

        if ($submit) {
            $username = filter_input($this->request_method, 'username');
            $password = filter_input($this->request_method, 'password');
            $msg = 'submit';
            $m = new \F13Dev\PhotoArchive\App\Models\Accounts();
            if ($m->login($username, $password)) {
                $account = $m->select_user_by_username($username);

                $_SESSION['loggedin'] = true;
                $_SESSION['ID'] = $account['id'];
                $_SESSION['username'] = $account['username'];
                $_SESSION['salt'] = $m->generate_session_salt();
                $_SESSION['usertoken'] = $m->generate_user_token();

                $currentURI = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"; //$_SERVER[REQUEST_URI]";
                
                die('<script>window.location.href = "'.$currentURI.'";</script>');
                $this->redirect($currentURI);
            } else {
                $msg = '<div class="error">The username and/or password were incorrect</div>';
            }
        }

        $v = new \F13Dev\PhotoArchive\App\Views\Accounts(array(
            'msg' => $msg,
        ));

        return $v->login();
    }

    public function redirect($url)
    {
        header('location'.$url);
        exit;
    }
}