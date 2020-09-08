<?php


class AbilityForPublicListing
{
    public $id;
    public $aid;
    public $name;
    public $type;
    public $description;
    public $mustBeActivated;
    public $canUseAt;


    public function __construct(Ability $ability = null)
    {
        if(isset($ability)){
            $this->id = (int)$ability->id ?? null;
            $this->aid = (string)$ability->aid ?? null;
            $this->name = (string)$ability->name ?? null;
            $this->type = (string)$ability->type ?? null;
            $this->description = (string)$ability->description ?? null;
            $this->mustBeActivated = (bool)$ability->mustBeActivated ?? null;
            $this->canUseAt = (array)$ability->canUseAt ?? null;
        }
    }
}