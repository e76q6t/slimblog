<?php
namespace app;

use app\models\Post;
use app\models\Tag;

class Panel
{
    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function recentPosts($num = 5)
    {
        $posts = Post::recents($num);
        return $this->view->render('panels/recent-posts.html', [
            'posts' => $posts
        ]);
    }

    public function tags()
    {
        $tags = Tag::getWithCount();
        return $this->view->render('panels/tags.html', [
            'tags' => $tags,
        ]);
    }
}
