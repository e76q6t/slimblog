<?php
namespace app\models;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property string $title
 * @property string $content
 * @property string $created_at
 * @property string $updated_at
 */
class Post extends Model
{
    const STATUS_PUBLIC = 1;
    const STATUS_DRAFT = 2;

    public static $statuses = [
        self::STATUS_PUBLIC => '公開',
        self::STATUS_DRAFT => '下書き',
    ];

    public static $labels = [
        'status' => '状態',
        'title' => 'タイトル',
        'content' => '本文',
    ];

    public static $rules = [
        'required' => [
            ['user_id'], ['status'], ['title'], ['slug'], ['content'],
        ],
        'numeric' => [
            ['user_id'],
            ['status'],
        ],
        'exists' => [
            ['user_id', 'users', 'id']
        ],
        'unique' => [
            ['slug', 'posts', 'slug']
        ],
        'in' => [
            ['status', [1, 2]]
        ],
        'lengthMax' => [
            ['title', 255],
            ['slug', 255],
        ],
        'slug' => [
            ['slug']
        ],
    ];

    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'status',
        'slug',
        'title',
        'content',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'status' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // delete時にタグの紐づけを解除する
        static::deleting(function($post) {
            $post->tags()->detach();
        });
    }

    public function user()
    {
        return $this->belongsTo('app\models\User');
    }

    public function tags()
    {
        return $this->belongsToMany('app\models\Tag');
    }

    public function comments()
    {
        return $this->hasMany('app\models\Comment');
    }

    public function approvedComments()
    {
        return $this->comments()->where('comments.status', Comment::STATUS_APPROVED);
    }

    /**
     * コメント数のリレーション
     */
    public function commentsCount()
    {
        return $this->hasOne('app\\models\\Comment')
            ->selectRaw('post_id, count(*) as aggregate')
            ->groupBy('post_id');
    }

    /**
     * commentsCountのゲッター
     * $post->commentsCount でコメント数を取得できるようにする
     *
     * @return integer
     */
    public function getCommentsCountAttribute()
    {
        if (!array_key_exists('commentsCount', $this->relations)) {
            $this->load('commentsCount');
        }

        $related = $this->getRelation('commentsCount');

        return ($related) ? (int) $related->aggregate : 0;
    }

    public function scopePublic($query)
    {
        return $query->where('status', static::STATUS_PUBLIC);
    }

    /**
     * 公開状態か調べる
     *
     * @return bool
     */
    public function isPublic()
    {
        return $this->status === static::STATUS_PUBLIC;
    }

    /**
     * データベースにモデルを保存する。タグの保存、紐付けも同時にする。
     *
     * @param array $attributes Postモデル属性の連想配列
     * @param array $tagNames
     * @return void
     */
    public function saveWithTags($attributes, $tagNames)
    {
        $this->fill($attributes);
        $rules = static::$rules;
        if (!$this->isNew()) {
            unset($rules['unique']);
        }

        $this->validate($rules);
        if (!$this->isNew()) {
            $this->validateSlug();
        }

        if ($this->hasErrors()) {
            throw new \RuntimeException;
        }

        try {
            DB::beginTransaction();

            $tags = Tag::findOrCreate($tagNames);

            $tagIds = array_map(function($tag) {
                return $tag->id;
            }, $tags);

            if (!$this->save() or !$this->tags()->sync($tagIds)) {
                throw new \RuntimeException;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * 公開状態の件数を返す
     *
     * @return integer
     */
    public static function publicCount($tag = null)
    {
        $query = static::query()->public();

        if ($tag !== null) {
            $query->whereHas('tags', function($q) use ($tag) {
                $q->where('name', $tag);
            });
        }

        return $query->count();
    }

    /**
     * ページ数と表示する件数からデータを返す
     *
     * @param int $page ページ数
     * @param int $num 件数
     * @param string $tag タグ名
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function paginate($page, $num, $tag = null, $with = [])
    {
        $query = static::query()->public();

        if ($page - 1 > 1) {
            $query->offset($num * ($page - 1));
        }

        foreach ($with as $w) {
            $query->with($w);
        }

        if ($tag) {
            $query->whereHas('tags', function($q) use ($tag) {
                $q->where('name', $tag);
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->take($num)
            ->get();
    }

    public function validateSlug()
    {
        $post = static::where('slug', $this->slug)->first();
        if ($post and !$this->isNew() and $post->id != $this->id) {
            $this->addError('slug', $this->label('slug') . 'はすでに使用されています。');
            return false;
        }
        return true;
    }

    /**
     * created_atの新しい順にnum件取得する
     *
     * @param int $num
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function recents($num)
    {
        return static::query()
            ->public()
            ->orderBy('created_at', 'desc')
            ->take($num)
            ->get();
    }

    /**
     * 1つ新しい記事を返す
     *
     * @return app\models\Post
     */
    public function findPrevious()
    {
        return static::query()
            ->public()
            ->where('created_at', '>', $this->created_at)
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * 1つ古い記事を返す
     *
     * @return app\models\Post|null
     */
    public function findNext()
    {
        return static::query()
            ->public()
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
