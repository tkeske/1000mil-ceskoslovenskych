<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

use Nette;

class CounterPresenter extends RestrictedPresenter
{
	public function renderDefault(){

		$vse = $this->em->getRepository('App\Counter')->findAll();

		$uipCelkem = $this->em->createQuery('SELECT COUNT(DISTINCT u.ipAdresa) FROM App\Counter u')->getResult();

		$uipZaMesic = $this->em->createQuery('SELECT COUNT(DISTINCT u.ipAdresa) AS pocet
		FROM App\Counter u
		WHERE u.datum > ?1')->setParameter(1, time() - 30 * 86400)->getResult();

		$this->template->uipZaMesic = $uipZaMesic[0]["pocet"];

		$this->template->uipCelkem = $uipCelkem[0][1];

		$this->template->vse = count($vse);
	}
}