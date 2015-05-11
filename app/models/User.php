<?php
namespace app\models;

use \Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $username
 * @property string $display_name
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 */
class User extends Model
{
    protected $table = 'users';

    protected $casts = [
        'id' => 'integer'
    ];

    protected $fillable = [
        'username',
        'display_name',
        'password',
    ];

    /**
     * パスワードのハッシュを返す
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * パスワードがハッシュにマッチするかを調べる
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * passwordのsetter。渡された値をハッシュにしてセットする
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = static::hashPassword($value);
    }

    public function posts()
    {
        return $this->hasMany('app\models\Post');
    }
}
