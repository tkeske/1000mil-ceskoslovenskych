<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

use Nette\Application\UI\Form;

class RubrikaPresenter extends BasePresenter
{
    
    public function actionList($id,$page=1){
   
        //select všech článků patřících do kategorie "id"
        //musíme zjistit count pronastavení paginatoru
    	$clanky = $this->em->createQuery('SELECT a,c FROM App\Article a JOIN a.category c WITH c.id=?1')
    				->setParameter(1,$id)
                    ->getResult();

        $count = count($clanky);

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($count);
        $paginator->setItemsPerPage(5);
        $paginator->setPage($page);

        //vyber články z db podle nastavení paginatoru a srovnej podle id
    	$clanky = $this->em->createQuery('SELECT a,c FROM App\Article a JOIN a.category c WITH c.id=?1 ORDER BY a.id DESC')
    				->setParameter(1,$id)
    				->setFirstResult($paginator->getOffset())
    				->setMaxResults($paginator->getLength())
                    ->getResult();

        //template render
        $this->template->count = $count;
        $this->template->articles = $clanky;
        $this->template->paginator = $paginator;
        $this->template->user = $this->getUser();
    }
}