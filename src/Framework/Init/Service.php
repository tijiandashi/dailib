<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:14
 */
namespace Dai\Lib\Framework\Init;

use DaiBk\Library\Runmode;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Mvc\Router\Annotations as RouterAnnotations;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Events\Manager as EventsManager;

class Service
{

    public function init(&$di)
    {
        $runMode = Runmode::get();

        /**
         * Shared configuration service
         */
        $di->setShared('config', function () use ($runMode) {
            return new \Phalcon\Config\Adapter\Ini(APP_PATH ."/config/". $runMode. "/config.ini");
        });

        /**
         * Setting up the view component
         */
        $di->setShared('view', function () {
            $config = $this->getConfig();

            $view = new View();
            $view->setDI($this);
            $view->setViewsDir(BASE_PATH.$config->application->viewsDir);

            $view->registerEngines([
                '.volt' => function ($view) {
                    $config = $this->getConfig();

                    $volt = new VoltEngine($view, $this);

                    $volt->setOptions([
                        'compiledPath' => BASE_PATH.$config->application->cacheDir,
                        'compiledSeparator' => '_'
                    ]);
                    return $volt;
                },
                '.phtml' => PhpEngine::class
            ]);

            return $view;
        });

        /**
         * Start the session the first time some component request the session service
         */
        $di->setShared('session', function () {
            $session = new SessionAdapter();
            $session->start();

            return $session;
        });



        $di->setShared('router', function () use ($di) {
            $router = new RouterAnnotations(false);

            $config =  $this->getConfig();
            $routerConfigs =  explode(",", $config->router->list);
            foreach ($routerConfigs as $module) {
                $router->addResource(ucfirst($module), '/'.lcfirst($module));
            }
            return $router;
        });


        $di->setShared( 'dispatcher', function () use ($di){
            $eventsManager = new EventsManager();
            // 添加插件到dispatch事件中
            $config = $this->getConfig();
            $plugins = explode(",", $config->plugin->list);
            foreach ($plugins as $plugin) {
                $pluginName = "\\".PRJ_NS."\\Plugin\\".$plugin;
                if( class_exists($pluginName)) {
                    $eventsManager->attach('dispatch', new $pluginName());
                }
            }
            $dispatcher = new MvcDispatcher();
            $dispatcher->setEventsManager($eventsManager);
            return $dispatcher;
        });


        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di->setShared('db', function () use ($di) {
            $config = $this->getConfig();
            $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
            $params = [
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->dbname,
                'charset'  => $config->database->charset
            ];
            return new $class($params);
        });
    }
}