<?php
namespace app\models;

use app\utils\String;

/**
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 */
class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'name'
    ];

    /**
     * タグ名の配列から既存のレコードのインスタンスまたは新しいインスタンスを返す
     *
     * @param array $names タグ名の配列
     * @return array Tagインスタンスの配列
     */
    public static function findOrCreate($names = [])
    {
        $existingTags = static::whereIn('name', $names)->get()->all();

        $existingNames = [];
        foreach ($existingTags as $tag) {
            $existingNames[] = $tag->name;
        }

        $newNames = $existingNames ? array_diff($names, $existingNames) : $names;

        $newTags = [];
        foreach ($newNames as $name) {
            $newTags[] = static::create(['name' => $name], true);
        }

        return array_merge($existingTags, $newTags);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = String::mb_trim($value);
    }

    public function posts()
    {
        return $this->belongsToMany('app\models\Post');
    }

    /**
     * すべてのタグの名前と使用回数を、使用回数が多い順に連想配列で返す
     * [
     *   [
     *     'name' => 'hoge',
     *     'cnt' => 10,
     *   ],
     *   [
     *     'name' => 'fuga',
     *     'cnt' => 8,
     *   ],
     *   ...
     * ]
     *
     * @return array
     */
    public static function getWithCount()
    {
        $query = <<<SQL
SELECT tags.name AS name, count(tags.id) AS cnt
FROM tags, post_tag
WHERE tags.id = post_tag.tag_id GROUP BY tags.id, tags.name ORDER BY cnt DESC
SQL;
        return static::resolveConnection()->select($query);
    }
}
