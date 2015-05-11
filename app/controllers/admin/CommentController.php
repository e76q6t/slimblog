<?php
namespace app\controllers\admin;

use app\models\Comment;

class CommentController extends \SlimController\SlimController
{
    public function indexAction()
    {
        $comments = Comment::getUnapproved()->toArray();
        return $this->render('admin/comments/index', [
            'comments' => $comments,
        ]);
    }

    public function approveAction($id)
    {
        $comment = $this->loadModel($id);
        if (!$comment->approve()) {
            return $this->app->halt(400);
        }
    }

    public function deleteAction($id)
    {
        $comment = $this->loadModel($id);
        if (!$comment->delete()) {
            return $this->app->halt(400);
        }
    }

    protected function loadModel($id)
    {
        $model = Comment::find($id);
        if (!$model) {
            return $this->app->notFound();
        }
        return $model;
    }
}
