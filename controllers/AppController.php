<?php


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