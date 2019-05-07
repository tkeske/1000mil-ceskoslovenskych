<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class KomentarePresenter extends RestrictedPresenter
{
    public function renderDefault(){

    	$coment = $this->em->createQuery('SELECT a FROM App\Coment a WHERE a.status = \'blocked\'')
    					   ->getResult();

    	$this->template->coment = $coment;
    }

    public function actionApprove($id){
    	$coment = $this->em->find('App\Coment', $id);
    	$coment->status = "approved";
    	
    	$this->em->persist($coment);
    	$this->em->flush();

        $this->flashMessage('Komentář byl schválen.');
    	$this->redirect("Komentare:default");
    }
}