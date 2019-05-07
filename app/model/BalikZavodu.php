<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BalikZavodu
 *
 * @author tomas
 */

/**
 * @ORM\Entity
 */
class BalikZavodu extends \Kdyby\Doctrine\Entities\BaseEntity {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    
    /**
     * @ORM\Column(length=150)
     */
    protected $balikname;

    
    /**
     * @ORM\OneToMany(targetEntity="Zavodnik", mappedBy="zavod", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $zavodnici;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Zavod", mappedBy="balik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $zavody;
    
    
   /**
     * @ORM\OneToMany(targetEntity="ZnackovyTym", mappedBy="balik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $teamy;
    
    
}
