<?php
namespace app\controllers\admin;

use app\models\Post;
use app\utils\Arr;
use app\utils\Pagination;

class PostController extends \SlimController\SlimController
{
    public function indexAction()
    {
        $page = $this->request()->get('page', 1);
        $posts = Post::paginate($page, 30);
        return $this->render('admin/posts/index', [
            'posts' => $posts,
            'pagination' => new Pagination($page, 50, Post::query()->public()->count()),
        ]);
    }

    public function newAction()
    {
        return $this->render('admin/posts/new', ['post' => new Post]);
    }

    public function createAction()
    {
        $post = new Post(['user_id' => $this->app->user->id]);
        return $this->save($post);
    }

    public function editAction($id)
    {
        $post = $this->loadModel($id);

        return $this->render('admin/posts/edit.html', [
            'data' => $post,
            'tags' => $post->tags,
        ]);
    }

    public function updateAction($id)
    {
        $post = $this->loadModel($id);
        return $this->save($post);
    }

    public function deleteAction($id)
    {
        $post = $this->loadModel($id);
        if (!$post->delete()) {
            return $this->app->halt(400);
        }
    }

    /**
     * idからPostモデルを返す。無い場合は404を返す。
     *
     * @param int $id
     * @return app\models\Post;
     */
    protected function loadModel($id)
    {
        if (!$post = Post::find($id)) {
            return $this->app->notFound();
        }
        return $post;
    }

    /**
     * 保存処理
     *
     * @param app\models\Post $post
     */
    protected function save($post)
    {
        $postData = $this->request()->post('Post', []);
        $tagNames = $this->request()->post('Tag', []);

        try {
            $post->saveWithTags($postData, $tagNames);
        } catch (\Exception $e) {
            if (!$post->hasErrors()) {
                $this->app->flash('danger', '記事の保存に失敗しました。');
                $this->app->log->error($e->getMessage());
            }

            $template = $post->isNew() ? 'new' : 'edit';

            return $this->render('admin/posts/' . $template, [
                'data' => $post,
                'tagNames' => $tagNames,
                'errors' => Arr::flatten($post->errors()),
            ]);
        }

        $this->app->flash('success', '記事を保存しました。');
        return $this->app->redirect('/admin/posts');
    }
}
