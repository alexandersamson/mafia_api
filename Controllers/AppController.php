<?php

namespace Main\Controllers;

use Main\Services\GlobalsService;
use Main\Services\JsonBuilderService;
use Main\Services\SL;

class AppController
{

    /**
     * PUBLIC API METHOD
     * @return bool
     */
    public function getPublicApiKey(){
        JsonBuilderService::getInstance()->add(SL::Services()->validationService->getApiKey(), GlobalsService::$jbData);
        return true;
    }
}