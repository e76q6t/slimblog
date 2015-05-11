<?php
namespace app\models;

class Image
{
    /**
     * 画像ファイル名の配列を返す
     *
     * @return array
     */
    public static function filenames()
    {
        $ignores = ['.', '..'];
        $thumbnailPath = PUBLIC_PATH . '/img/uploads/thumbnails/';

        $files = [];
        foreach (scandir($thumbnailPath) as $file) {
            if (in_array($file, $ignores) or is_dir($thumbnailPath . $file)) {
                continue;
            }

            $files[] = [
                'name' => $file,
                'timestamp' => filemtime($thumbnailPath . $file),
            ];
        }

        // 新しい順にソート
        usort($files, function($a, $b) {
            if ($a['timestamp'] === $b['timestamp']) {
                return 0;
            }
            return ($a['timestamp'] < $b['timestamp']) ? 1 : -1;
        });

        return array_map(function($file) {
            return $file['name'];
        }, $files);
    }

    /**
     * ファイルをアップロードする
     *
     * @return string
     */
    public static function upload($tmpName)
    {
        $filename = uniqid() . '.png';

        $img = \Slim\Slim::getInstance()->image->make($tmpName);
        $img->save(PUBLIC_PATH . '/img/uploads/' . $filename);

        // サムネイルを保存
        $img->widen(100);
        $img->save(PUBLIC_PATH . '/img/uploads/thumbnails/' . $filename);

        return $filename;
    }

}
