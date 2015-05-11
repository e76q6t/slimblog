<?php
namespace app\controllers\admin;

use app\models\Image;

class ImageController extends \SlimController\SlimController
{
    public function indexAction()
    {
        $this->app->response()->header('Content-Type', 'application/json');

        $filenames = Image::filenames();
        echo json_encode(['images' => $filenames]);
    }

    public function uploadAction()
    {
        if (empty($_FILES['image']['tmp_name'])) {
            return $this->app->halt(400);
        }

        try {
            $filename = Image::upload($_FILES['image']['tmp_name']);
        } catch (\Exception $e) {
            $this->app->log->error($e->getMessage());
            return $this->app->halt(400);
        }
    }

    public function deleteAction()
    {
        $filename = $this->request()->post('image');
        if (!$filename) {
            return $this->app->halt(400);
        }

        $filepath = PUBLIC_PATH . '/img/uploads/' . $filename;
        $thumbpath = PUBLIC_PATH . '/img/uploads/thumbnails/' . $filename;

        try {
            unlink($filepath);
            unlink($thumbpath);
        } catch (\Exception $e) {
            return $this->app->halt(400);
        }
    }
}
