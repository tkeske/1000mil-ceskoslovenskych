<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class LoginPresenter extends PublicPresenter
{

    protected function createComponentLoginForm(){

        $form = new Form;
        $form->setRenderer(new \Bs3FormRenderer());
        $form->addText('email', 'E-mail:')->addRule(Form::FILLED, "Pole musí být vyplněno.");
                                            //->addRule(Form::EMAIL, "Zadejte prosím platný email.");

        $form->addPassword('pass', 'Heslo:')->addRule(Form::FILLED, "Pole musí být vyplněno.");
        $form->addSubmit('log', 'Login');

        $form->onValidate[] = [$this, 'loginFormValidate'];
        $form->onSuccess[] = [$this, 'loginFormSuccess'];

        return $form;
    }

    public function loginFormValidate(Form $form, $values){

        try{
            $this->getUser()->login($values["email"], $values["pass"]);
        } catch (\Exception $e){
            $form->addError($e->getMessage());
        }   
    }

    public function loginFormSuccess(Form $form, $values){
        
        $userEntity = $this->em->getRepository('App\User')->find($this->getUser()->id);
        
        if ($userEntity->role == "kontrola"){
            $this->redirect('Admin:etapa', $userEntity->etapa->id);
        } else {
            $this->flashMessage('Vítejte v administraci.');
            $this->redirect('Admin:default');
        }
    }
}