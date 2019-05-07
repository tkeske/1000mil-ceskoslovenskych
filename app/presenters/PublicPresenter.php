<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

use Nette\Utils\Strings;

class PublicPresenter extends BasePresenter
{

    public function startup(){
        parent::startup();

        //zamezení přístupu na login a registraci
        //když je uživatel přihlášen
        
        $path =$this->getHttpRequest()->url->path;
        
        $bool = Strings::contains($path, "result");
        
        if ($path  !== "/" && !$bool){
            if ($this->getUser()->isLoggedIn()){
                $this->redirect("Admin:default");
            }
        }
    }
}