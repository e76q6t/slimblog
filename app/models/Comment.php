<?php
namespace app\models;

/**
 * @property integer $id
 * @property integer $post_id
 * @property integer $status
 * @property string $username
 * @property string $content
 * @property string $created_at
 * @property string $updated_at
 */
class Comment extends Model
{
    const STATUS_UNAPPROVED = 0;
    const STATUS_APPROVED = 1;

    protected $table = 'comments';

    public static $labels = [
        'username' => 'お名前',
        'content' => 'コメント',
    ];

    public static $rules = [
        'required' => [
            ['post_id'], ['status'], ['content'],
        ],
        'lengthMax' => [
            ['username', 20],
            ['content', 2000],
        ]
    ];

    protected $fillable = [
        'post_id',
        'status',
        'username',
        'content',
    ];

    protected $casts = [
        'id' => 'integer',
        'post_id' => 'integer',
        'status' => 'integer',
    ];

    public function post()
    {
        return $this->belongsTo('app\models\Post');
    }

    /**
     * 承認済みか調べる
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === static::STATUS_APPROVED;
    }

    /**
     * 承認待ちのコメントをすべて返す
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUnapproved()
    {
        return static::where('status', static::STATUS_UNAPPROVED)->get();
    }

    /**
     * コメントの状態を承認済みにして保存する
     *
     * @return bool
     */
    public function approve()
    {
        $this->status = static::STATUS_APPROVED;
        return $this->save();
    }
}
