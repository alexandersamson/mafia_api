<?php

namespace Main\Models;
class Pagination
{
    public $objectName = null;
    public $page = 1;
    public $totalPages = 0;
    public $nextPage = 0;
    public $previousPage = null;
    public $itemsPerPage = 0;
    public $currentItems = 0;
    public $totalItems = 0;
}