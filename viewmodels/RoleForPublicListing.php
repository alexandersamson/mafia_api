<?php


class RoleForPublicListing
{
    public $id;
    public $rid;
    public $name;
    public $type;
    public $balancePower;
    public $description;
    public $imageUrl;
    public $faction;
    public $abilities;


    public function __construct(Role $role = null)
    {
        if(isset($role)){
            $this->id = (int)$role->id ?? null;
            $this->rid = (string)$role->rid ?? null;
            $this->name = (string)$role->name ?? null;
            $this->type = (string)$role->type ?? null;
            $this->balancePower = (int)$role->balancePower ?? null;
            $this->description = (string)$role->description ?? null;
            $this->imageUrl = (string)$role->imageUrl ?? null;
            $this->faction = (object)SL::Services()->roleService->getFactionForRole($role, FactionForPublicListing::class) ?? null;
            $this->abilities = (array)SL::Services()->roleService->getAbilitiesForRole($role, AbilityForPublicListing::class) ?? null;
        }
    }
}