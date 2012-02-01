<?php
/**
 * Weegbo PagingExtension class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @link http://weegbo.com/
 * @copyright Copyright &copy; 2008-2011 Inspirativ
 * @license http://weegbo.com/license/
 *
 * Extension for create paging
 *
 * @package system.base.extensions
 * @since 0.8
 */
class PagingExtension {

    /**
     * Return paging array.
     *
     * @access public
     * @param int $count_records total records number
     * @param int $count_on_page records number on page
     * @param int $count_pages   pages number in paging
     * @param int $page          current page
     * @return array|NULL
     */
    public function getPages($count_records, $count_on_page, $count_pages, $page) {
        if ($count_on_page > 0)
            $count = ceil($count_records / $count_on_page);
        // if we have 1 page - don't show paging
        if ($count <= 1)
            return NULL;
        if ($page < 1)
            $page = 1;
        if ($page > $count)
            $page = $count;
        if ($count_pages > $count)
            $count_pages = $count;

        if ($count_pages % 2 == 1) {
            $left_pages = ($count_pages - 1) / 2;
            $right_pages = $left_pages;
        }
        else {
            $left_pages = $count_pages / 2;
            $right_pages = $left_pages - 1;
        }

        $start = $page - $left_pages;
        $finish = $page + $right_pages;

        if ($start <= 1) {
            $start = 1;
            $finish = $count_pages;
        }
        if ($finish >= $count) {
            $finish = $count;
            $start = $count - ($count_pages - 1);
        }

        $pages = array();
        for ($i = $start; $i <= $finish; $i++) {
            if ($i != $page)
                $pages[$i] = 0;
            else
                $pages[$i] = 1;
        }

        $start = ($start > 1) ? 1 : 0;
        $finish = ($finish < $count) ? $count : 0;
        $priv = ($page > 1) ? ($page - 1) : 0;
        $next = ($page < $count) ? ($page + 1) : 0;

        return array(
            'start' => $start,
            'priv' => $priv,
            'pages' => $pages,
            'next' => $next,
            'finish' => $finish,
            'page' => $page,
            'count' => $count
        );
    }
}
