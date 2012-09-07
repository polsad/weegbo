<?php
/**
 * TestController class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package system.controller
 * @copyright Copyright &copy; 2008-2012 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */
require_once(Config::get('path/base').'controller.class.php');
class TestController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->displayPage('pages/tests/index.tpl');
    }

    public function acl() {
        $config = array(
            'anonymous' => array(
                'allow' => array(
                    'page', 'news'
                ),
                'deny' => '*'
            ),
            'users' => array(
                'allow' => array(
                    'page', 'page/edit',
                    'news', 'news/add', 'news/edit', 'news/delete',
                    'login/logout'
                ),
                'deny' => '*'
            ),
            'admin' => array(
                'allow' => '*'
            )
        );
        $this->load->extension('acl', 'acl', $config);
        $users = array('anonymous', 'users', 'admin', 'faik');
        $resources = array(
            'page', 'news',
            'page/add', 'page/edit', 'page/delete',
            'news/add', 'news/edit', 'news/delete',
            'user/add', 'user/delete', 'login/logout'
        );
        $res = array();
        for ($j = 0; $j < sizeof($resources); $j++) {
            for ($i = 0; $i < sizeof($users); $i++) {
                $res[$j][$i] = $this->acl->checkAccess($users[$i], $resources[$j]);
            }
        }
        $this->view->assign('res', (array) $res);
        $this->view->assign('users', (array) $users);
        $this->view->assign('resources', (array) $resources);
        $this->displayPage('pages/tests/acl.tpl');
    }

    public function ajax() {
        $this->load->extension('ajax');
        if ($this->ajax->isAjax()) {
            $this->ajax->send(array('ajax' => 'true'));
        }
        else {
            $this->displayPage('pages/tests/ajax.tpl');
        }
    }

    public function crypt() {
        $cryptKey = 'd8eSJE9#FKDdie09';
        $this->load->extension('crypt', 'crypt', array('key' => $cryptKey));

        $data = array(
            'user' => 'UserName',
            'password' => 'Password'
        );
        $this->view->assign('sdata', $data);
        $res = $this->crypt->encrypt($data);
        $this->view->assign('cdata', $res);
        $res = $this->crypt->decrypt($res);
        $this->view->assign('fdata', $res);
        $this->displayPage('pages/tests/crypt.tpl');
    }

    public function dom() {
        $this->load->extension('dom');
        $root = $this->dom->add('root');
        for ($i = 0; $i <= 10; $i++) {
            $item = $this->dom->add('item', $root);
            $this->dom->add('number', $item, $i);
            for ($j = 1; $j < 4; $j++) {
                $this->dom->add('string', $item, md5(microtime()), array('random-number' => rand(0, 1000)));
            }
            $this->dom->addCdata('text', $item, '<sender>John Smith</sender>');
        }
        $res = $this->dom->getXml(true);
        header("Content-Type: text/xml; charset=utf-8");
        die($res);
    }

    public function logger() {
        $this->load->extension('logger', 'log', array('file' => PATH_ROOT.'examples/log.txt'));
        $this->log->add('all night');
        $this->log->add('autumn winds being heard');
        $this->log->add('behind the mountains');
        $this->log->add('Matsuo Basho');

        $this->view->assign('file', PATH_ROOT.'examples/log.txt');
        $this->view->assign('log', file(PATH_ROOT.'examples/log.txt'));
        $this->displayPage('pages/tests/logger.tpl');
    }

    public function file() {
        $this->load->extension('file');
        $res = array();
        // Create subdirs test/1 and test/2
        $this->file->mkdir(PATH_ROOT.'examples/file/1');
        $this->file->mkdir(PATH_ROOT.'examples/file/2');
        $this->file->mkdir(PATH_ROOT.'examples/file/3');
        $res[] = $this->file->ls(PATH_ROOT.'examples/file/');
        // Copy index.php to test
        copy(PATH_ROOT.'index.php', PATH_ROOT.'examples/file/index.php');
        $res[] = $this->file->ls(PATH_ROOT.'examples/file/');
        // Rename index.php to index.txt
        $this->file->mv(PATH_ROOT.'examples/file/index.php', PATH_ROOT.'examples/file/index.txt');
        // Remove test/2
        $this->file->rmdir(PATH_ROOT.'examples/file/2');
        $res[] = $this->file->ls(PATH_ROOT.'examples/file/');
        // Copy test/index.txt to test/1
        copy(PATH_ROOT.'examples/file/index.txt', PATH_ROOT.'examples/file/1/index.txt');
        copy(PATH_ROOT.'examples/file/index.txt', PATH_ROOT.'examples/file/3/index.txt');
        $res[] = $this->file->ls(PATH_ROOT.'examples/file/1/');
        // Delete not empty dir test/1
        $this->file->rmdir(PATH_ROOT.'examples/file/1');
        $res[] = $this->file->ls(PATH_ROOT.'examples/file/');
        // Get file name & extension
        $res[] = $this->file->getFileName(PATH_ROOT.'examples/index.txt');
        // Get file name & extension
        $res[] = $this->file->getFileName('index.txt');

        $this->view->assign('res', $res);
        $this->displayPage('pages/tests/file.tpl');
    }

    public function upload() {
        if ($this->input->checkPost('action', true, false)) {
            $this->load->extension('file');
            $check = $this->file->uploadCheckFile($_FILES['upload']);
            $error = '';

            if ($check == UPLOAD_ERR_OK) {
                if ($this->file->checkFileType($_FILES['upload']['type'], array('jpg', 'gif', 'png'))) {
                    $file = $this->file->getFileName($_FILES['upload']['name']);
                    if (false === $this->file->uploadSaveFile($_FILES['upload']['tmp_name'], PATH_ROOT.'examples/upload/image.'.$file['ext'])) {
                        $error = "Can't save uploaded file.";
                    }
                }
                else {
                    $error = "File must be JPG, GIF, PNG";
                }
            }
            else {
                switch ($check) {
                    case UPLOAD_ERR_OK: $error = "No errors."; break;
                    case UPLOAD_ERR_INI_SIZE: $error = "Larger than upload_max_filesize."; break;
                    case UPLOAD_ERR_FORM_SIZE: $error = "Larger than form MAX_FILE_SIZE."; break;
                    case UPLOAD_ERR_NO_FILE: $error = "Empty file."; break;
                    case UPLOAD_ERR_PARTIAL: $error = "Partial upload."; break;
                    case UPLOAD_ERR_NO_FILE: $error = "No file."; break;
                    case UPLOAD_ERR_NO_TMP_DIR: $error = "No temporary directory."; break;
                    case UPLOAD_ERR_CANT_WRITE: $error = "Can't write to disk."; break;
                    case UPLOAD_ERR_EXTENSION: $error = "File upload stopped by extension."; break;
                }
            }
            $this->view->assign('error', $error);
            $this->view->assign('file', $_FILES['upload']);
            $res = $this->file->ls(PATH_ROOT.'examples/upload/');
            $this->view->assign('ls', $res);
        }
        $this->displayPage('pages/tests/file-upload.tpl');
    }

    public function ftp() {
        $this->load->extension('ftp', 'ftp', array('host' => 'ftp.secureftp-test.com', 'user' => 'test', 'password' => 'test'));
        $res = array();
        $res[] = $this->ftp->dirname();
        $res[] = $this->ftp->ls();
        $res[] = $this->ftp->get('pigs.xml', PATH_ROOT.'examples/ftp/pigs.xml');
        $this->ftp->chdir('subdir2');
        $res[] = $this->ftp->ls();
        $this->ftp->close();

        $this->view->assign('res', $res);
        $this->displayPage('pages/tests/ftp.tpl');
    }

    public function session() {
        $res = array();
        $this->load->extension('session');

        $res[] = $this->session->sessionId();
        $this->session->set('test', 'Test text');
        $this->session->set('array', array(1, 2, 3, 4, 5));
        $res[] = $_SESSION;
        $this->session->delete('test');
        $res[] = $_SESSION;
        $res[] = ($this->session->check('test') == true) ? 'true' : 'false';
        $res[] = ($this->session->check('array') == true) ? 'true' : 'false';

        $this->view->assign('res', $res);
        $this->displayPage('pages/tests/session.tpl');
    }

    public function message() {
        $res = array();
        $messages = array(
            'name' => 'Weegbo Framework'
        );
        $this->load->extension('message', 'message', $messages);
        $res[] = $this->message->get('name');
        $res[] = $this->message->get('text-1');
        $this->message->setConfig('test.php');
        $res[] = $this->message->get('text-1');
        $res[] = $this->message->get('texts/text-1');

        $this->view->assign('res', $res);
        $this->displayPage('pages/tests/messages.tpl');
    }

    public function image() {
        $this->load->extension('image', 'image');
        $imgs = array('img-1.jpg', 'img-2.jpg', 'img-3.jpg');
        $path = PATH_ROOT.'examples/image/';
        foreach ($imgs as $v) {
            $this->image->resize($path.$v, $path.'1-'.$v, 200, 200);
        }
        foreach ($imgs as $v) {
            $this->image->resize($path.$v, $path.'2-'.$v, 200, 200, false);
        }
        foreach ($imgs as $v) {
            $this->image->thumb($path.$v, $path.'3-'.$v, 100, 100);
        }
        foreach ($imgs as $v) {
            $this->image->crop($path.$v, $path.'4-'.$v, 100, 50, 100, 100);
        }
        foreach ($imgs as $v) {
            $this->image->textWatermark($path.$v, $path.'5-'.$v, 'Watermark', array('font-color' => 'ff0000', 'align' => 'center', 'valign' => 'center'));
        }
        foreach ($imgs as $v) {
            $this->image->textWatermark($path.$v, $path.'6-'.$v, 'Watermark', array('font' => $path.'drakoheart-leiend-regular.ttf', 'font-size' => 24, 'font-color' => '00ff00', 'align' => 'center', 'valign' => 'center'));
        }
        foreach ($imgs as $v) {
            $this->image->imageWatermark($path.$v, $path.'7-'.$v, $path.'copy.png', array('align' => 'center', 'valign' => 'center', 'opacity-mode' => true));
        }

        $this->view->assign('imgs', $imgs);
        $this->displayPage('pages/tests/image.tpl');
    }

    public function paging() {
        $this->load->extension('paging');

        $res = array();
        $res[] = $this->paging->getPages(160, 10, 5, 9);
        $res[] = $this->paging->getPages(100, 10, 5, 2);
        $res[] = $this->paging->getPages(100, 10, 7, 11);
        $res[] = $this->paging->getPages(60, 20, 3, 1);


        $this->view->assign('res', $res);
        $this->displayPage('pages/tests/paging.tpl');
    }

    public function rest() {
        $res = array();
        $url = Config::get('path/host').'test/restsrv/';
        $data = array('id' => 100, 'test' => 'Text message');

        $this->load->extension('rest', 'rest', array('user' => 'test', 'password' => 'test'));

        $this->rest->sendRequest($url, 'GET', $data);
        $res[] = $this->rest->getResponseInfo();
        $res[] = $this->rest->getResponseBody();

        $this->rest->sendRequest($url, 'POST', $data);
        $res[] = $this->rest->getResponseInfo();
        $res[] = $this->rest->getResponseBody();

        $this->rest->sendRequest($url, 'PUT', $data);
        $res[] = $this->rest->getResponseInfo();
        $res[] = $this->rest->getResponseBody();

        $this->rest->sendRequest($url, 'DELETE');
        $res[] = $this->rest->getResponseInfo();
        $res[] = $this->rest->getResponseBody();

        $this->view->assign('res', $res);
        $this->displayPage('pages/tests/rest.tpl');
    }

    public function restsrv() {
        $this->load->extension('rest');
        $method = $this->rest->getRequestMethod();
        echo $method.'<br>';
        switch ($method) {
            case 'GET':
                print_r($_GET);
                break;
            case 'POST':
                print_r($_POST);
                break;
            case 'PUT':
                print_r($this->rest->getRequestPutData());
                break;
            case 'DELETE':
                break;
        }
        exit();
    }

    public function validator() {
        $this->load->extension('validator');
        if (true == $this->input->checkPost('save', true, false)) {
            /**
             * Emial
             */
            $email = $this->input->post('email', 'string', null);
            if ($email != null) {
                $this->validator->rule($email, 'email', 'Invalid Email. ', 'email');
            }
            else {
                $this->validator->setError('Enter Email. ', 'email');
            }
            /**
             * Login
             */
            $login = $this->input->post('login', 'string', null);
            if ($login != null) {
                if ($this->validator->minmaxlen($login, 4, 16, 'Login must be 4 - 16 symbols. ', 'login')) {
                    $this->validator->rule($login, 'login', 'Login must consist a-z,A-Z,0-9,._- ', 'login');
                }
            }
            else {
                $this->validator->setError('Enter Login. ', 'login');
            }
            /**
             * Terms
             */
            $terms = $this->input->checkPost('terms', true, false);
            $this->validator->checked($terms, 'Must agree with Terms of Use. ', 'terms');
            /**
             * Pass
             */
            $pass = $this->input->post('pass', 'string', null);
            $rpass = $this->input->post('rpass', 'string', null);

            if ($pass != null) {
                $res1 = $this->validator->rule($pass, 'password', 'Invalid pass. ', 'pass');
            }
            else {
                $res1 = false;
                $this->validator->setError('Enter password. ', 'pass');
            }
            if ($rpass != null) {
                $res2 = $this->validator->rule($rpass, 'password', 'Invalid pass. ', 'rpass');
            }
            else {
                $res2 = false;
                $this->validator->setError('Enter password. ', 'rpass');
            }
            if ($res1 == true && $res2 == true) {
                $check = $this->validator->compare(array($pass, $rpass));
                if ($check == false) {
                    $this->validator->setError('Passwords not compare. ', 'pass');
                    $this->validator->setError('Passwords not compare. ', 'rpass');
                }
            }
            $comment = $this->input->post('comment', 'string', null);
            if ($comment == null) {
                $this->validator->setError('Enter comment. ', 'comment');
            }
            else {
                $this->validator->minmaxlen($comment, 20, 1000, 'Min 20, max 1000 symbols. ', 'comment');
            }
            if (!$this->validator->validate()) {
                $this->view->assign('email', $email);
                $this->view->assign('login', $login);
                $this->view->assign('terms', $terms);
                $this->view->assign('pass', $pass);
                $this->view->assign('rpass', $rpass);
                $this->view->assign('comment', $comment);

                $errors = $this->validator->getErrors();
                $this->view->assign('error', $errors);
            }
        }
        $this->displayPage('pages/tests/validator.tpl');
    }
}
