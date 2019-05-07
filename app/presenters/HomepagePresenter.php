<?php

/**
* @author Tomáš Keske
*/

namespace App\Presenters;

use App\Counter;

class HomepagePresenter extends BasePresenter
{
	public function startup(){
		parent::startup();

		//counter návštěv
		$pocitadlo = new Counter;
		$pocitadlo->ipAdresa = $this->getHttpRequest()->remoteAddress;
		$pocitadlo->datum = time();

		$this->em->persist($pocitadlo);
		$this->em->flush();
	}

	public function actionLogout(){
		$this->getUser()->logout(true);

		$this->flashMessage("Byl jste úspěšně odhlášen.");
		$this->redirect("Homepage:default");
	}

	public function renderDefault($page = 1){	
		//render článků pro úvodní stránku
		$clanky = $this->em->getRepository('App\Article')->findAll();

		$count = count($clanky);

		$paginator = new \Nette\Utils\Paginator;
		$paginator->setItemCount($count); // celkový počet článků
		$paginator->setItemsPerPage(3); // počet položek na stránce
		$paginator->setPage($page);

		$clanky = $this->em->createQuery('SELECT a FROM App\Article a ORDER BY a.id DESC')
							->setFirstResult($paginator->getOffset())
       						->setMaxResults($paginator->getLength())->getResult();

		$this->template->count = $count;
		$this->template->clanky = $clanky;
		$this->template->paginator = $paginator;
		$this->template->user = $this->getUser();
	}

	public function helper(){
		$posts = $this->em->createQuery('SELECT a FROM App\Article a ORDER BY a.id DESC')->getResult();

		$this->template->posts = $posts;
	}

	public function renderRss(){
		$this->helper();	
	}

	public function renderSitemap(){
		$this->helper();
	}
}