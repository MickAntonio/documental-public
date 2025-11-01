<?php
use App\Models\Permissions\MenuPermission;
use App\Models\Permissions\ModelHasRole;
use App\Models\User;

function repaged($paginated){
    return [
        'data'=>$paginated->items(),
        'pagination'=>[
            'current_page' => $paginated->currentPage(),
            'first_page_url' => $paginated->url(1),
            'from' => $paginated->firstItem(),
            'last_page' => $paginated->lastPage(),
            'last_page_url' => $paginated->url($paginated->lastPage()),
            'links' => $paginated->linkCollection()->toArray(),
            'next_page_url' => $paginated->nextPageUrl(),
            'path' => $paginated->path(),
            'per_page' => $paginated->perPage(),
            'prev_page_url' => $paginated->previousPageUrl(),
            'to' => $paginated->lastItem(),
            'total' => $paginated->total(),
        ]
    ];
}

function repage($paginated){
    return [
        'data'=>$paginated->items(),
        'pagination'=>[
            'page' => $paginated->currentPage() > 0 ? $paginated->currentPage()-1 : $paginated->currentPage(),
            'size' => $paginated->perPage(),
            'startIndex' => $paginated->firstItem() > 0 ? $paginated->firstItem() - 1 : $paginated->firstItem(),
            'lastPage' => $paginated->lastPage() > 0 ? $paginated->lastPage() -1 : $paginated->lastPage(),
            'endIndex' => $paginated->lastItem() > 0 ? $paginated->lastItem()-1: $paginated->lastItem(),
            'length' => $paginated->total(),
        ]
    ];
}

function isNullOrEmpty($str){
    return ($str == null || trim($str) == '' || $str == 'null');
}
