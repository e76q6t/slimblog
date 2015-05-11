<?php
namespace app\utils;

class Pagination
{
    public $page;  // 現在のページ数
    public $num;   // １ページに表示する件数
    public $total; // 全件数

    /**
     * @param int $page 現在のページ数
     * @param int $perPage 1ページに表示する件数
     * @param int $total 全件数
     */
    public function __construct($page, $perPage, $total)
    {
        $this->page = (int) $page;
        $this->perPage = (int) $perPage;
        $this->total = (int) $total;
    }

    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \RuntimeException('存在しないプロパティです。');
        }
        return $this->$name;
    }

    public function pages()
    {
        return (int) ceil($this->total / $this->perPage);
    }

    public function hasPrevious()
    {
        return $this->page > 1;
    }

    public function hasNext()
    {
        return $this->page < $this->pages();
    }

    public function getPages($diff = 2)
    {
        $first = 1;
        $last = $this->pages();
        $between = $diff * 2 + 1;

        if ($last <= $between + 2) {
            return range(1, $last);
        }

        $begin = $this->page - $diff;
        $end = $this->page + $diff;

        if ($begin <= $first) {
            $end = $first + $diff * 2;
            $begin = $first + 1;
        }

        if ($end >= $last - 1) {
            $begin -= $end - ($last - 1);
            $end = $last - 1;
        }

        $pages = [1];

        for ($i = 2; $i <= $last; $i++) {
            if ($begin !== 2 and $i === $begin - 1) {
                $pages[] = null;
                continue;
            }

            if ($i === $end + 1 and $i !== $last) {
                $pages[] = null;
                continue;
            }

            if ($begin <= $i and $i <= $end) {
                $pages[] = $i;
            }
        }

        $pages[] = $last;

        return $pages;
    }
}
