<?php


class AbilityService
{

    /**
     * @param $id
     * @param string $model
     * @param false $deleted
     * @return mixed|null
     */
    public function getAbilityById($id, $model = Ability::class, $deleted = false){
        $id = intval($id);
        if(!SL::Services()->validationService->validateParams(["int" => [$id], "bool" => [$deleted]],__METHOD__)){
            return null;
        }
        /* @var $ability Ability */
        $ability = SL::Services()->objectService->getSingleObject(["id" => $id, "deleted" => $deleted], new Ability);
        if(!isset($ability) || !isset($ability->id)){
            return null;
        }
        if($model == Ability::class || $model == null) {
            return $ability;
        }
        return new $model($ability);
    }


    /**
     * @param array $ids
     * @param string $model
     * @param false $deleted
     * @return array|null
     */
    public function getAbilitiesByIds(Array $ids, $model = Ability::class, $deleted = false){
        if(!isset($ids) || !is_array($ids)){
            MessageService::getInstance()->add("error",__METHOD__." - No valid ids (Array of int) provided");
            return null;
        }
        $abilities = [];
        foreach ($ids as $id){
            $id = intval($id);
            if(!SL::Services()->validationService->validateParams(['int' => [$id]])){
                continue;
            }
            array_push($abilities, $this->getAbilityById($id, $model, $deleted));
        }
        return $abilities;
    }


    /**
     * @param Seat $seat
     * @param string $model
     * @param false $deleted
     * @return array|null
     */
    public function getAbilitiesBySeat(Seat $seat, $model = Ability::class, $deleted = false){
        if(!isset($seat) || !isset($seat->abilities) || !is_array($seat->abilities)){
            MessageService::getInstance()->add("error",__METHOD__." - No valid seat object provided");
            return null;
        }
        return $this->getAbilitiesByIds($seat->abilities, $model, $deleted);
    }


    /**
     * @param Role $role
     * @param string $model
     * @param false $deleted
     * @return array|null
     */
    public function getAbilitiesByRole(Role $role, $model = Ability::class, $deleted = false){
        if(!isset($role) || !isset($role->abilities) || !is_array($role->abilities)){
            MessageService::getInstance()->add("error",__METHOD__." - No valid role object provided");
            return null;
        }
        return $this->getAbilitiesByIds($role->abilities, $model, $deleted);
    }


    /**
     * @param array $abilities
     * @param string $model
     * @param false $deleted
     * @return array|null
     */
    public function getAbilitiesByAid(Array $abilities, $model = Ability::class, $deleted = false){
        if(!isset($abilities) || !is_array($abilities)){
            MessageService::getInstance()->add("error",__METHOD__." - No valid array object provided");
            return null;
        }
        $abilities = [];
        foreach ($abilities as $abilityId){
            $ability = $this->getAbilityById(intval($abilityId), $model, $deleted);
            if(!isset($ability)){
                MessageService::getInstance()->add("error",__METHOD__." - Could not get ability for id $abilityId");
                continue;
            }
        }
        return $abilities;
    }
}