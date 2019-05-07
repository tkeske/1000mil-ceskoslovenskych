<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

class RestrictedPresenter extends BasePresenter
{

    public function startup(){
        parent::startup();

        $user = $this->getUser();

        //zamezení přístupu na chráněné stránky neoprávněným
        if (!$user->isLoggedIn()){
            $this->redirect("Homepage:default");
        }

        if (!$user->isInRole("admin") && !$user->isInRole("kontrola")){
            $this->redirect("Homepage:default");
        }
    }
}