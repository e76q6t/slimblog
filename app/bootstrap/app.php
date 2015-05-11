<?php

define('ROOT_PATH', realpath(__DIR__ . '/../../'));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

mb_internal_encoding('utf-8');

ini_set('session.hash_function', 1);
session_cache_limiter(false);
session_start();

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Intervention\Image\ImageManager;
use Valitron\Validator;
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;


$app = new \SlimController\Slim([
    'view' => new \Slim\Views\Twig,
    'templates.path' => APP_PATH . '/templates',
    'controller.class_prefix' => '\\app\\controllers',
    'controller.class_suffix' => 'Controller',
    'controller.method_suffix' => 'Action',
    'controller.template_suffix' => 'html',
    'log.enabled' => true,
    'log.level' => \Slim\Log::WARN,
    'log.writer' => new \Slim\Extras\Log\DateTimeFileWriter([
        'path' => ROOT_PATH .'/logs',
        'name_format' => 'Y-m-d',
        'message_format' => '%label% - %date% - %message%'
    ]),
]);


/**
 * コンテナに追加
 */
$app->container->singleton('auth', function() {
    return new app\services\Auth;
});
$app->container->singleton('image', function($c) {
    return new ImageManager(['driver' => 'imagick']);
});
$app->container->singleton('user', function($c) {
    return $c['auth']->getCurrentUser();
});
$app->container->singleton('panel', function() use ($app) {
    return new app\Panel($app->view);
});
$app->params = require(APP_PATH . '/config/params.php');


$config = require(APP_PATH . '/config/' . $app->mode . '.php');


/**
 * Capsuleの初期化
 */
$event = new Dispatcher(new Container);
$event->listen('illuminate.query', function($query) use ($app) {
    $app->log->debug($query);
});
$db = new DB;
$db->addConnection($config['db']);
$db->setEventDispatcher($event);
//$db->setEventDispatcher(new Dispatcher(new Container));
$db->setAsGlobal();
$db->bootEloquent();


/**
 * Hook
 */
$app->hook('slim.before', function() use ($app, $config) {
    $app->view()->getEnvironment()->addGlobal('current_user', $app->user);

    /**
     * viewの設定
     */
    $view = $app->view();
    $view->parserOptions = [
        'debug' => $app->config('mode') === 'development',
    ];
    $view->parserExtensions = [
        new \Slim\Views\TwigExtension(),
    ];


    /**
     * Validatorの設定
     */
    Validator::lang('ja');

    Validator::addRule('exists', function($field, $value, array $params) {
        $table = $params[0];
        $column = $params[1];
        $count = DB::table($table)->where($column, $value)->count();
        return $count > 0;
    }, 'が正しくありません');

    Validator::addRule('unique', function($field, $value, array $params) {
        $table = $params[0];
        $column = $params[1];
        $count = DB::table($table)->where($column, $value)->count();
        return $count === 0;
    }, 'はすでに使用されています。');


    /**
     * Twigの設定
     */
    $twig = $view->getEnvironment();

    // markdownフィルターを追加
    $engine = new MarkdownEngine\MichelfMarkdownEngine();
    $twig->addExtension(new MarkdownExtension($engine));

    $twig->addGlobal('params', $app->params);
    $twig->addGlobal('post_status_list', app\models\Post::$statuses);
    $twig->addGlobal('panel', $app->panel);
});


/**
 * Middleware
 */
$app->add(new \app\middlewares\CsrfGuard());


/**
 * routes
 */
require(APP_PATH . '/bootstrap/routes.php');


return $app;
