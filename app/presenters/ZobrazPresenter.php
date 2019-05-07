<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

use Nette\Application\UI\Form;
use App\Coment;


class ZobrazPresenter extends BasePresenter
{
    
    public function actionClanek($id){

    	$article = $this->findBySlug($id);
    	$coments = $this->em->getRepository('App\Coment')->findAll();

    	if ($article){
    		$this->template->article = $article;
    	} else {
    		$this->redirect("Homepage:default");
    	}
    }

    public function findBySlug($slug){
    	$a = $this->em->getRepository('App\Article')->findBy(array("slug" => $slug));

        if(!$a){
            $this->redirect("Homepage:default");
        } else {
            return $a[0];
        }
    }

    public function createComponentKomentareForm(){
    	$form = new Form;
    	$form->setRenderer(new \Bs3FormRenderer());
        $form->addText("nick", "Přezdívka:")->addRule(Form::FILLED, "Pole musí být vyplněno.");
    	$form->addTextArea("obsah", "Komentář")->addRule(Form::FILLED, "Pole musí být vyplněno.");
    	$form->addSubmit("send", "Odeslat", 100,80);

        $user = $this->getUser();

        if ($user->isLoggedIn()){
            $form->setDefaults(array('nick' => $user->getIdentity()->data[0]));
        }

    	$form->onSuccess[] = [$this, "komentareSuccess"];

    	return $form;
    }

    public function komentareSuccess(Form $form, $values){

    	$id = $this->getParameter("id");
    	$article = $this->findBySlug($id);

    	$coment = new Coment;
    	$coment->nick = $values["nick"];
    	$coment->obsah = $values["obsah"];
        $coment->status = "blocked";
        $coment->date = date("d-M-Y");
    	$coment->ref = $article;
    	
    	$this->em->persist($coment);
    	$this->em->flush();

        $this->flashMessage("Komentář byl uložen. Nyní čeká na schválení administrátorem.");
        $this->redirect("Zobraz:clanek", $id);
    }
}