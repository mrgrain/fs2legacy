<?php
namespace Frogsystem\Legacy\Controllers;

use Frogsystem\Legacy\AdminPageRenderer;
use Frogsystem\Legacy\Services\Config;
use Frogsystem\Legacy\Services\Database;
use Frogsystem\Legacy\Services\Lang;
use Frogsystem\Legacy\Services\Text;
use Frogsystem\Metamorphosis\Controller;
use Frogsystem\Metamorphosis\Response\View;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;
use StringCutter;
use Zend\Diactoros\Response;

class AdminController extends Controller
{

    protected $config;
    protected $text;
    protected $db;

    function __construct(Config $config, Text $text, Database $db)
    {
        $this->config = $config;
        $this->text = $text;
        $this->db = $db;
    }

    public function assets(Response $response, $asset)
    {
        $filesystem = new Filesystem(new Adapter(FS2ADMIN));
        $expires = 8640000;

        try {
            // generate cache data
            $tsstring = gmdate('D, d M Y H:i:s ', $filesystem->getTimestamp($asset)) . 'GMT';
            $etag = md5($filesystem->read($asset));
            $mimetype = $filesystem->getMimetype($asset);
            if (0 !== strpos($mimetype, 'image')) {
                $mimetype = 'text/css';
            }

            $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
            $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;

            if ((($if_none_match && $if_none_match == "\"{$etag}\"") || (!$if_none_match)) &&
                ($if_modified_since && $if_modified_since == $tsstring)
            ) {
                return $response->withStatus('304');

            } else {
                $response = $response
                    ->withHeader('Last-Modified', $tsstring)
                    ->withHeader('ETag', "\"{$etag}\"");
            }

            // send out content type, expire time and the file
            $response->getBody()->write($filesystem->read($asset));
            return $response
                ->withHeader('Expires', gmdate('D, d M Y H:i:s ', time() + $expires) . 'GMT')
                ->withHeader('Content-Type', $mimetype)
                ->withHeader('Pragma', 'cache')
                ->withHeader('Cache-Control', 'cache');

        } catch (FileNotFoundException $e) {
            throw $e;
        }
    }

    public function index(AdminPageRenderer $renderer)
    {
        // Renderer
        $view = new View($renderer);

        // Content
        $page = $this->detectPage();
        $this->text['page'] = $page['template']->getLang();
        $content = $this->getPageContent($page['file'], $page['template']);
        $leftMenu = $this->getLeftMenu($page['menu']);
        $default_menu = $renderer->render('main.tpl/default_menu', []);

        // display page
        return $view->render('main.tpl/full', [
            'title' => $page['title'],
            'title_short' => StringCutter::cut($this->config->config('title'), 50, '...'),
            'version' =>$this->config->config('version'),
            'virtualhost' => $this->config->config('virtualhost'),
            'admin_link_to_page' => $this->text['menu']->get('admin_link_to_page'),
            'topmenu'=> get_topmenu($page['menu']),
            'log_link' => (is_authorized() ? 'logout' : 'login'),
            'log_image' => (is_authorized() ? 'logout.gif' : 'login.gif'),
            'log_text' => (is_authorized() ? $this->text['menu']->get("admin_logout_text") : $this->text['menu']->get("admin_login_text")),
            'leftmenu' => (!empty($leftMenu) ? $leftMenu : $default_menu),
            'content' => $content,
        ]);
    }

    protected function getLeftMenu($menu)
    {
        return get_leftmenu($menu, ACP_GO);
    }

    protected function getPageContent($file, $adminpage)
    {
        global $FD;
        ob_start();
        require(FS2ADMIN . '/' . $file);
        return ob_get_clean();
    }

    protected function detectPage()
    {
        // security functions
        if (!isset($_REQUEST['go'])) {
            $_REQUEST['go'] = null;
        }
        $go = $_REQUEST['go'];

        // get page-data from database
        $acp_arr = $this->db->conn()->prepare(
            <<<SQL
SELECT `page_id`, `page_file`, P.`group_id` AS `group_id`, `menu_id`
FROM `{$this->db->getPrefix()}admin_cp` P, `{$this->db->getPrefix()}admin_groups` G
WHERE P.`group_id` = G.`group_id` AND P.`page_id` = ? AND P.`page_int_sub_perm` != 1
SQL
        );
        $acp_arr->execute(array($go));
        $acp_arr = $acp_arr->fetch(\PDO::FETCH_ASSOC);

        // if page exists
        if (!empty($acp_arr)) {

            // if page is start page
            if ($acp_arr['group_id'] == -1) {
                $acp_arr['menu_id'] = $acp_arr['page_file'];
                $acp_arr['page_file'] = $acp_arr['page_id'] . '.php';
            }

            //if popup
            if ($acp_arr['group_id'] == 'popup') {
                define('POPUP', true);
                $title = $this->text['menu']->get('page_title_' . $acp_arr['page_id']);
            } else {
                define('POPUP', false);
                $title = $this->text['menu']->get('group_' . $acp_arr['group_id']) . ' &#187; ' . $this->text['menu']->get('page_title_' . $acp_arr['page_id']);
            }

            // get the page-data
            $PAGE_DATA_ARR = createpage($title, has_perm($acp_arr['page_id']), $acp_arr['page_file'], $acp_arr['menu_id']);

        } else {
            $PAGE_DATA_ARR['created'] = false;
            define('POPUP', false);
        }

        // logout
        if ($PAGE_DATA_ARR['created'] === false && $go == 'logout') {
            setcookie('login', '', time() - 3600, '/');
            $_SESSION = array();
            $PAGE_DATA_ARR = createpage($this->text['menu']->get("admin_logout_text"), true, 'admin_logout.php', 'dash');
        } // login
        elseif ($PAGE_DATA_ARR['created'] === false && ($go == 'login' || empty($go))) {
            $go = 'login';
            $PAGE_DATA_ARR = createpage($this->text['menu']->get("admin_login_text"), true, 'admin_login.php', 'dash');
        } // error
        elseif ($PAGE_DATA_ARR['created'] === false) {
            $go = '404';
            $PAGE_DATA_ARR = createpage($this->text['menu']->get("admin_error_page_title"), true, 'admin_404.php', 'error');
        }

        // Get Special Page Lang-Text-Files
        $page_lang = new Lang($this->config->config('language_text'), 'admin/' . substr($PAGE_DATA_ARR['file'], 0, -4));
        $common_lang = $this->text['admin'];

        // initialise template system
        $PAGE_DATA_ARR['template'] = new \adminpage($PAGE_DATA_ARR['file'], $page_lang, $common_lang);


        // Define Constant
        define('ACP_GO', $go);

        return $PAGE_DATA_ARR;
    }

    protected function login()
    {
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
    }
}
