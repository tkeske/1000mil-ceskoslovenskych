<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

use Nette\Application\UI\Form;
use App\Category;


class KategoriePresenter extends RestrictedPresenter
{
    public function createComponentKategorieForm(){

    	$form = new Form;
    	$form->setRenderer(new \Bs3FormRenderer());

    	$form->addText("jmeno", "Název kategorie")->addRule(Form::FILLED, "Pole musí být vyplněno");
    	$form->addSubmit("submit", "Přidat kategorii");

    	$form->onSuccess[] = [$this, "kategorieFormOnSuccess"];

    	return $form;
    }

    public function kategorieFormOnSuccess(Form $form, $values){

    	$kategorie = new Category;
    	$kategorie->jmeno = $values["jmeno"];
    	
    	$this->em->persist($kategorie);
    	$this->em->flush();

        $this->flashMessage("Kategorie úspěšně přidána.");
        $this->redirect("Admin:kategorie");
    }

    public function actionDelete($id){
        $cat = $this->em->getRepository("App\Category")->find($id);

        if ($cat){

            $this->em->remove($cat);
            $this->em->flush();
        }

        $this->flashMessage("Kategorie úspěšně odebrána.");
        $this->redirect("Kategorie:odebrat");
    }

    public function renderOdebrat($page=1){
        $cat = $this->em->createQuery('SELECT c FROM App\Category c')
                    ->getResult();

        $count = count($cat);

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($count);
        $paginator->setItemsPerPage(10);
        $paginator->setPage($page);

        $cat = $this->em->createQuery('SELECT c FROM App\Category c ORDER BY c.id DESC')
                    ->setFirstResult($paginator->getOffset())
                    ->setMaxResults($paginator->getLength())
                    ->getResult();

        $this->template->count = $count;
        $this->template->cat = $cat;
        $this->template->paginator = $paginator;
        $this->template->user = $this->getUser();
    }
}