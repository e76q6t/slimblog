<?php
namespace app\services;

use app\models\User;

class Auth
{
    const SESSION_KEY = 'userId';

    protected $userId;

    public function __construct()
    {
        if (isset($_SESSION[static::SESSION_KEY])) {
            $this->userId = (int) $_SESSION[static::SESSION_KEY];
        }
    }

    /**
     * ログイン中のユーザーIDを返す。ログインしていなければnullを返す
     *
     * @return null|integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * ユーザー名とパスワードを検証してセッションにユーザーIDを保存する
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login($username, $password)
    {
        $user = User::where('username', $username)->first();
        if ($user and $user->verifyPassword($password)) {
            session_regenerate_id(true);
            $_SESSION[static::SESSION_KEY] = (int) $user->id;
            return true;
        }
        return false;
    }

    /**
     * セッションを破棄する。
     *
     * @return void
     */
    public function logout()
    {
        $_SESSION = [];
        session_destroy();

        // cookieを削除
        $sessionName = session_name();
        if (isset($_COOKIE[$sessionName])) {
            setcookie($sessionName, '', time() - 3600, '/');
        }
        session_start();
    }

    /**
     * ログイン中のユーザーのモデルを返す。ログインしていなければnullを返す
     *
     * @return app\models\User|null
     */
    public function getCurrentUser()
    {
        return $this->userId ? User::find($this->userId) : null;
    }

    /**
     * ログインユーザーか調べる
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->userId !== null;
    }
}
