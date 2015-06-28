<?php
namespace Frogsystem\Legacy\Controllers;

use Frogsystem\Legacy\AdminPageRenderer;
use Frogsystem\Legacy\Services\Lang;
use Frogsystem\Metamorphosis\Controller;
use Frogsystem\Metamorphosis\Response\View;
use StringCutter;

class AdminController extends Controller
{
    public function assets()
    {
        $imgct = function ($name) {
            switch (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
                case 'png':
                    return 'image/png';
                case 'gif':
                    return 'image/gif';
                case 'jpg':
                case 'jpeg':
                default:
                    return 'image/jpeg';
            }
        };
        serve_asset('css', 'text/css');
        serve_asset('js', 'application/javascript');
        serve_asset('images', $imgct);
        serve_asset('editor', $imgct);
        serve_asset('html-editor', $imgct);
        serve_asset('icons', $imgct);

    }

    public function index(View $view, AdminPageRenderer $renderer)
    {
        global $FD;

        // Renderer
        $view->setRenderer($renderer);

        // Content
        $page = $this->detectPage();
        $content = $this->getPageContent($page['file']);
        $leftmenu = $this->getLeftMenu($page['menu']);
        $default_menu = $renderer->render('main/default_menu', []);

        // display page
        return $view->render('main/full', [
            'title' => $page['title'],
            'title_short' => StringCutter::cut($FD->config('title'), 50, '...'),
            'version' => $FD->config('version'),
            'virtualhost' => $FD->config('virtualhost'),
            'admin_link_to_page' => $FD->text('menu', 'admin_link_to_page'),
            'topmenu'=> get_topmenu($page['menu']),
            'log_link' => (is_authorized() ? 'logout' : 'login'),
            'log_image' => (is_authorized() ? 'logout.gif' : 'login.gif'),
            'log_text' => (is_authorized() ? $FD->text("menu", "admin_logout_text") :  $FD->text("menu", "admin_login_text")),
            'leftmenu' => !empty($leftmenu) ? $leftmenu : $default_menu
        ]);
    }

    protected function getLeftMenu($menu)
    {
        return get_leftmenu($menu, ACP_GO);
    }

    protected function getPageContent($file)
    {
        ob_start();
        require(FS2ADMIN . '/' . $file);
        return ob_get_clean();
    }

    protected function detectPage()
    {
        global $FD;
        ##################################
        ### START OF DETECTING SUBPAGE ###
        ##################################

        // security functions
        !isset($_REQUEST['go']) ? $_REQUEST['go'] = null : 1;
        $go = $_REQUEST['go'];

        // get page-data from database
        $acp_arr = $FD->db()->conn()->prepare(
            'SELECT page_id, page_file, P.group_id AS group_id, menu_id
                FROM ' . $FD->env('DB_PREFIX') . 'admin_cp P, ' . $FD->env('DB_PREFIX') . 'admin_groups G
                WHERE P.`group_id` = G.`group_id` AND P.`page_id` = ? AND P.`page_int_sub_perm` != 1');
        $acp_arr->execute(array($go));
        $acp_arr = $acp_arr->fetch(PDO::FETCH_ASSOC);

        // if page exisits
        if (!empty($acp_arr)) {

            // if page is start page
            if ($acp_arr['group_id'] == -1) {
                $acp_arr['menu_id'] = $acp_arr['page_file'];
                $acp_arr['page_file'] = $acp_arr['page_id'] . '.php';
            }

            //if popup
            if ($acp_arr['group_id'] == 'popup') {
                define('POPUP', true);
                $title = $FD->text("menu", 'page_title_' . $acp_arr['page_id']);
            } else {
                define('POPUP', false);
                $title = $FD->text("menu", 'group_' . $acp_arr['group_id']) . ' &#187; ' . $FD->text("menu", 'page_title_' . $acp_arr['page_id']);
            }

            // get the page-data
            $PAGE_DATA_ARR = createpage($title, has_perm($acp_arr['page_id']), $acp_arr['page_file'], $acp_arr['menu_id']);

            // Get Special Page Lang-Text-Files
            $page_lang = new Lang($FD->config('language_text'), 'admin/' . substr($acp_arr['page_file'], 0, -4));
            $common_lang = $FD->text['admin'];

            // initialise templatesystem
            $adminpage = new \adminpage($acp_arr['page_file'], $page_lang, $common_lang);

        } else {
            $PAGE_DATA_ARR['created'] = false;
            define('POPUP', false);
        }

        // logout
        if ($PAGE_DATA_ARR['created'] === false && $go == 'logout') {
            setcookie('login', '', time() - 3600, '/');
            $_SESSION = array();
            $PAGE_DATA_ARR = createpage($FD->text("menu", "admin_logout_text"), true, 'admin_logout.php', 'dash');
        } // login
        elseif ($PAGE_DATA_ARR['created'] === false && ($go == 'login' || empty($go))) {
            $go = 'login';
            $PAGE_DATA_ARR = createpage($FD->text("menu", "admin_login_text"), true, 'admin_login.php', 'dash');
        } // error
        elseif ($PAGE_DATA_ARR['created'] === false) {
            $go = '404';
            $PAGE_DATA_ARR = createpage($FD->text("menu", "admin_error_page_title"), true, 'admin_404.php', 'error');
        }


        // Define Constant
        define('ACP_GO', $go);

        return $PAGE_DATA_ARR;
        ################################
        ### END OF DETECTING SUBPAGE ###
        ################################
    }

    protected function login()
    {
        ######################
        ### START OF LOGIN ###
        ######################

        if (isset($_POST['stayonline']) && $_POST['stayonline'] == 1) {
            admin_set_cookie($_POST['username'], $_POST['userpassword']);
        }

        if (isset($_COOKIE['login']) && $_COOKIE['login'] && !is_authorized()) {
            $userpassword = substr($_COOKIE['login'], 0, 32);
            $username = substr($_COOKIE['login'], 32, strlen($_COOKIE['login']));
            admin_login($username, $userpassword, TRUE);
        }

        if (isset($_POST['login']) && $_POST['login'] == 1 && !is_authorized()) {
            admin_login($_POST['username'], $_POST['userpassword'], false);
        }

        ####################
        ### END OF LOGIN ###
        ####################
    }
}
