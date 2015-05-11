<?php
namespace app\models;

use Valitron\Validator;

/**
 * Eloquentにvalidateメソッドを追加する
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    public static $rules = [];

    public static $labels = [];

    protected $errors = [];

    protected $scenario = 'default';

    /**
     * 属性を検証する
     *
     * @param array $rules
     * @return bool
     */
    public function validate($rules = [])
    {
        $v = new Validator($this->attributes);
        $v->labels(static::$labels);

        $rules = empty($rules) ? static::$rules : $rules;
        $v->rules($rules);

        if ($v->validate()) {
            return true;
        }

        $this->errors = $v->errors();

        return false;
    }

    /**
     * バリデーションエラーを返す
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * カラム名のラベルを返す
     *
     * @param string $column
     * @return string
     */
    public function label($column)
    {
        if (isset(static::$labels[$column])) {
            return static::$labels[$column];
        }
        return $column;
    }


    /**
     * データベースに保存する
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [], $validate = false)
    {
        if ($validate and !$this->validate()) {
            return false;
        }
        return parent::save($options);
    }

    /**
     * データベースに保存してモデルのインスタンスを返す
     *
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes, $validate = false)
    {
        $model = new static($attributes);

        $model->save([], $validate);

        return $model;
    }

    /**
     * バリデーションエラーを追加する
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addError($key, $value)
    {
        $this->errors[$key][] = $value;
    }

    /**
     * バリデーションエラーがあるか調べる
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * モデルが保存前のものか調べる
     *
     * @return bool
     */
    public function isNew()
    {
        return !$this->exists;
    }
}
