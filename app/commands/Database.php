<?php
namespace app\commands;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class Database implements Command
{
    public static $environments = ['development', 'production'];

    public static function initCapsule($env)
    {
        /**
         * Capsuleの初期化
         */
        $config = require(APP_PATH . '/config/' . $env . '.php');
        $capsule = new Capsule;
        $capsule->addConnection($config['db']);
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        return $capsule;
    }

    public static function run($args)
    {
        $env = $args[0];
        if (!in_array($env, static::$environments)) {
            throw new \RuntimeException('Invalid environment.');
        }

        $capsule = static::initCapsule($env);
        $schema = $capsule->schema();

        $schema->create('users', function($table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('display_name');
            $table->string('password');
            $table->timestamps();
        });

        $schema->create('posts', function($table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->integer('status')->index();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        $schema->create('comments', function($table) {
            $table->increments('id');
            $table->integer('post_id')->index();
            $table->integer('status')->index();
            $table->string('username');
            $table->text('content');
            $table->timestamps();
        });

        $schema->create('tags', function($table) {
            $table->increments('id');
            $table->string('name')->index();
            $table->timestamps();
        });

        $schema->create('post_tag', function($table) {
            $table->increments('id');
            $table->integer('post_id')->index();
            $table->integer('tag_id')->index();

            $table->unique(['post_id', 'tag_id']);
        });
    }

    public static function drop_all_tables($args)
    {
        $env = $args[0];
        $capsule = static::initCapsule($env);
        $schema = $capsule->schema();

        foreach (['users', 'posts', 'tags', 'comments', 'post_tag'] as $t) {
            if ($schema->hasTable($t)) {
                $schema->drop($t);
            }
        }
    }
}
