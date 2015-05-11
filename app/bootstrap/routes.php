<?php

$app->addRoutes([
    '/' => 'Post:index',
    '/posts' => 'Post:index',
    '/tags/:name' => 'Post:index',

    '/posts/:slug' => 'Post:view',
    '/posts/:id/addComment' => ['post' => 'Post:addComment']
]);

/**
 * 管理者用
 */
$app->addRoutes([
    '/admin/logout' => 'admin\Auth:logout',

    /**
     * posts
     */
    '/admin/posts' => 'admin\Post:index',
    '/admin/posts/new' => [
        'get'  => 'admin\Post:new',
        'post' => 'admin\Post:create',
    ],
    '/admin/posts/:id/edit' => [
        'get'  => 'admin\Post:edit',
        'post' => 'admin\Post:update',
    ],
    '/admin/posts/:id/delete' => 'admin\Post:delete',

    /**
     * comments
     */
    '/admin/comments' => 'admin\Comment:index',
    '/admin/comments/:id/approve' => ['post' => 'admin\Comment:approve'],
    '/admin/comments/:id/delete' => ['delete' => 'admin\Comment:delete'],

    /**
     * images
     */
    '/admin/images' => [
        'get'  => 'admin\Image:index',
        'post' => 'admin\Image:upload',
    ],
    '/admin/images/delete' => 'admin\Image:delete',
], function() use ($app) {
    if (!$app->auth->isLoggedIn()) {
        $from = $app->request->getPath();
        return $app->redirect('/admin/login?from=' . $from);
    }
});

$app->addRoutes([
    '/admin/login' => [
        'admin\Auth:login',
        function() use ($app) {
            if ($app->auth->isLoggedIn()) {
                $app->flash('warning', 'すでにログインしています');
                return $app->redirect('/');
            }
        }
    ]
]);
