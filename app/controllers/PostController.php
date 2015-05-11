<?php
namespace app\controllers;

use app\models\Post;
use app\models\Comment;
use app\utils\Arr;
use app\utils\Pagination;

class PostController extends \SlimController\SlimController
{
    public function indexAction($tag = null)
    {
        $page = $this->request()->get('page', 1);
        $num = $this->app->params['posts.per_page'];
        $posts = Post::paginate($page, $num, $tag, ['user', 'tags', 'comments']);
        $pagination = new Pagination($page, $num, Post::publicCount($tag));

        return $this->render('posts/index', [
            'posts' => $posts,
            'pagination' => $pagination,
            'tag' => $tag,
        ]);
    }

    public function viewAction($slug)
    {
        $post = $this->loadModel('slug', $slug, ['user', 'tags', 'comments']);
        return $this->render('posts/view', [
            'post' => $post,
            'nextPost' => $post->findNext(),
            'previousPost' => $post->findPrevious(),
        ]);
    }

    public function addCommentAction($id)
    {
        $post = $this->loadModel($id);
        $comment = new Comment([
            'post_id' => $post->id,
            'status' => Comment::STATUS_UNAPPROVED,
            'username' => $this->request()->post('username'),
            'content' => $this->request()->post('content'),
        ]);

        if (!$comment->validate()) {
            $errors = $comment->errors();
            json_encode(Arr::flatten($errors));
            $this->app->response->setStatus(400);
            return;
        }

        if (!$comment->save()) {
            $error = sprintf(
                '[コメントの保存に失敗] post_id: %s, username: %s, content: %s',
                $comment->id, $comment->username, $comment->content);
            $this->app->log->error($error);
            $this->app->halt(400);
        }
    }

    /**
     * Postモデルを返す。無い場合または公開状態でない場合は404を返す。
     *
     * @param string|integer $key
     * @param string|integer|null $value
     * @param array $with
     * @return app\models\Post;
     */
    protected function loadModel($key, $value = null, $with = [])
    {
        $query = Post::query()->public();

        foreach ($with as $w) {
            $query->with($w);
        }

        if ($value) {
            $query->where($key, $value);
        } else {
            $query->where('id', $key);
        }

        $post = $query->first();

        if (!$post){
            $this->app->notFound();
        }

        return $post;
    }
}
