<?php
namespace app\services;

class PostService
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function saveWithTags(array $postData, array $tagNames)
    {

    }
}
