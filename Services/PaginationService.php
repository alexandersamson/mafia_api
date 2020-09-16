<?php

namespace Main\Services;
use Main\Models\Pagination;

class PaginationService
{
    /**
     * @param int $page
     * @return int
     */
    public function getSkipsFromPageNumber($page){
        if(!SL::Services()->validationService->validateParams(["int" => [$page]]) || $page < 1) {
            return null;
        }
        return (int)(GlobalsService::getInstance()->getResultsPerPage() * $page) - GlobalsService::getInstance()->getResultsPerPage();
    }

    public function getTake(){
        return GlobalsService::getInstance()->getResultsPerPage();
    }

    public function getPaginationObject($name, $currentPage, $currentCount, $totalCount){
        if(!SL::Services()->validationService->validateParams(["int" => [$currentPage,$totalCount,$currentCount], "string" =>[$name]],__METHOD__) || $currentPage < 1 || $totalCount < 1 || $totalCount < 1) {
            return null;
        }
        $pagination = new Pagination();
        $pagination->objectName = $name;
        $pagination->page = $currentPage;
        $pagination->currentItems = $currentCount;
        $pagination->nextPage = ($currentPage * $this->getTake()) < $totalCount ? ($currentPage + 1) : null;
        $pagination->previousPage = $currentPage > 1 ? ($currentPage - 1) : null;
        $pagination->itemsPerPage = $this->getTake();
        $pagination->totalItems = $totalCount;
        $pagination->totalPages = ceil($totalCount/$this->getTake());
        return $pagination;
    }
}