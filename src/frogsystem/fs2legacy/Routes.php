<?php
namespace Frogsystem\FS2Core;

use Frogsystem\Frogsystem\LegacyConfig;
use Frogsystem\Frogsystem\LegacyText;
use Frogsystem\Frogsystem\Router;
use Frogsystem\Spawn\Contracts\PluggableContainer;
use Frogsystem\Spawn\Container;

class Routes extends Container implements PluggableContainer
{

    function __construct(Router $router, LegacyConfig $config, LegacyText $text)
    {
        $this->router = $router;
        $this->config = $config;
        $this->text = $text;
    }

    /**
     * Executed whenever a pluggable gets plugged in.
     * @return mixed
     */
    public function plugin()
    {
        // Route Urls
        $this->router->any('/admin', function () {
            include(__DIR__ . '/src/admin/admin.php');
        });
        $this->router->any('/', function () {

            // Constructor Calls
            global $APP;
            userlogin();
            setTimezone($this->config->cfg('timezone'));
            run_cronjobs();
            count_all($this->config->cfg('goto'));
            save_visitors();
            if (!$this->config->configExists('main', 'count_referers') || $this->config->cfg('main', 'count_referers') == 1) {
                save_referer();
            }
            set_style();
            $APP = load_applets();

            // Get Body-Template
            $theTemplate = new \template(); //todo: abstract templates in a simple way for now
            $theTemplate->setFile('0_main.tpl');
            $theTemplate->load('MAIN');
            $theTemplate->tag('content', $this->get_content($this->config->cfg('goto')));
            $theTemplate->tag('copyright', get_copyright());

            $template_general = (string) $theTemplate;
            // TODO: "Template Manipulation Hook"

            // Display Page
            echo tpl_functions_init(get_maintemplate($template_general));
        });

        $lang = $this->text;
        $this->router->fail(function (\Exception $exception) use ($lang) {
            // closures
            $header = function ($content, $replace = false, $http_response_code = null) {
                header($content, $replace, $http_response_code);
            };

            // log connection error
            error_log($exception->getMessage(), 0);

            // Set header
            $header(http_response_text(503), true, 503);
            $header('Retry-After: '.(string)(60*15)); // 15 Minutes

            // No-Connection-Page Template
            $template = '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
        <head>
            <title>' . $lang['frontend']->get("no_connection") . '</title>
        </head>
        <body>
            <p>
                <b>' . $lang['frontend']->get("no_connection_to_the_server") . '</b>
            </p>
        </body>
    </html>
        ';

            // Display No-Connection-Page
            echo $template;
        });
    }


    /**
     * Executed whenever a pluggable gets unplugged.
     * @return mixed
     */
    public function unplug()
    {
    }


    protected function get_content($GOTO)
    {
        global $FD;

        // Display Content
        initstr($template);

        // Script-File in /data/
        if (file_exists(__DIR__ . '/src/pages/' . $GOTO . '.php')) {
            include(__DIR__ . '/src/pages/' . $GOTO . '.php');
        } elseif (file_exists(__DIR__ . '/src/pages/' . $GOTO)) {
            include(__DIR__ . '/src/pages/' . $GOTO);
        } else {

            // Articles from DB
            $stmt = $FD->db()->conn()->prepare(
                'SELECT COUNT(article_id) FROM ' . $FD->env('DB_PREFIX') . 'articles
                       WHERE `article_url` = ? LIMIT 0,1');
            $stmt->execute(array($GOTO));
            $num = $stmt->fetchColumn();
            if ($num >= 1) {

                // Forward Aliases
                $alias = $FD->db()->conn()->query(
                    'SELECT alias_forward_to FROM ' . $FD->env('DB_PREFIX') . "aliases
                           WHERE `alias_active` = 1 AND `alias_go` = 'articles.php'");
                $alias = $alias->fetch(PDO::FETCH_ASSOC);
                if (!empty($alias)) {
                    $FD->setConfig('env', 'goto', $alias['alias_forward_to']);
                    include(__DIR__ . '/src/pages/' . $alias['alias_forward_to']);
                } else {
                    $FD->setConfig('env', 'goto', 'articles');
                    include(__DIR__ . '/src/pages/articles.php');
                }

                // File-Download
            } elseif ($GOTO == 'dl' && isset ($_GET['fileid']) && isset ($_GET['dl'])) {

                // 404-Error Page, no content found
            } else {
                $FD->setConfig('goto', '404');
                $FD->setConfig('env', 'goto', '404');
                include(__DIR__ . '/src/pages/404.php');
            }
        }

        // Return Content
        return $template;
    }
}