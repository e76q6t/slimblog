<?php
namespace app\commands;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class User implements Command
{
    public static $environments = ['development', 'production'];

    public static function run($args)
    {
        static::create($args);
    }

    public static function create($args)
    {
        if (count($args) !== 1) {
            echo "usage: php command.php user:create <env>\n";
            exit;
        }

        $env = $args[0];
        Database::initCapsule($env);

        echo "username:\n";
        $username = trim(fgets(STDIN));

        echo "display_name:\n";
        $display_name = trim(fgets(STDIN));

        echo "password:\n";
        $password = trim(fgets(STDIN));

        $user = \app\models\User::create([
            'username' => $username,
            'display_name' => $display_name,
            'password' => $password
        ]);
    }
}

