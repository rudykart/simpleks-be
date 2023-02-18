<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public static function pagination($collection, $route, $perPage)
    {
        $total = $collection->count();
        $lastPage = (int) ceil($total / $perPage);
        $page = (int) request('page');
        $offset = $page == 1 ? 0 : ($page - 1) * $perPage;
        $to = $page == 0 || $page == 1 ? 1 : ($page - 1) * $perPage + 1;
        $currentPage = $page == 0 ? 1 : $page;

        $link = PageController::createLinks($route, $currentPage, $lastPage);

        // dd($link);
        $hasil = $collection
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // $data['last_page'] = $lastPage;
        $data['current_page'] = $currentPage;
        $data['data'] = $hasil;
        $data['first_page_url'] = $link['first_page_url'];
        $data['links'] = $link['links'];
        $data['last_page_url'] = $link['last_page_url'];
        $data['perPage'] = $perPage;
        $data['to'] = $to;
        $data['last_page'] = $lastPage;
        $data['prev_page_url'] = $link['prev_page_url'];
        $data['next_page_url'] = $link['next_page_url'];
        $data['total_data'] = $total;

        return (object) $data;
    }

    public static function createLinks($route, $cPage, $lastPage)
    {
        if ($lastPage <= 5) {
            for ($i = 1; $i <= $lastPage; $i++) {
                $link[] = [
                    // 'url' => url('/api/' . $route . '/' . '?page=' . $i),
                    'url' =>
                        $i == $cPage
                            ? null
                            : url('/api/' . $route . '/' . '?page=' . $i),
                    'label' => $i,
                    'active' => $i == $cPage ? true : false,
                ];
            }
        } elseif ($lastPage > 5) {
            if ($cPage <= 3) {
                $start = 1;
                $end = 5;
            } elseif ($cPage == $lastPage) {
                $start = $cPage - 4;
                $end = $lastPage;
            } elseif ($cPage <= $lastPage - 3) {
                $start = $cPage - 2;
                $end = $cPage + 2;
            } elseif ($cPage >= $lastPage - 3) {
                $start = $cPage - 2;
                $end = $lastPage;
            }

            for ($i = $start; $i <= $end; $i++) {
                $link[] = [
                    'url' => url('/api/' . $route . '/' . '?page=' . $i),
                    'label' => $i,
                    'active' => $i == $cPage ? true : false,
                ];
            }
        }

        $prev_page_url =
            $cPage <= 1
                ? null
                : url('/api/' . $route . '/' . '?page=' . $cPage - 1);
        $next_page_url =
            $cPage >= $lastPage
                ? null
                : url('/api/' . $route . '/' . '?page=' . $cPage + 1);

        $first_page_url = url('/api/' . $route . '/' . '?page=' . 1);
        $last_page_url = url('/api/' . $route . '/' . '?page=' . $lastPage);

        return $data = [
            'links' => $link,
            'prev_page_url' => $prev_page_url,
            'next_page_url' => $next_page_url,
            'first_page_url' => $first_page_url,
            'last_page_url' => $last_page_url,
        ];
    }
}