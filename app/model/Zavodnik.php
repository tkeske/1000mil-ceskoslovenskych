<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Zavodnik extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $jmeno;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $znacka;
    
    
    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $status;
    

    /**
     * @ORM\Column(type="integer")
     */
    protected $cislo;
    
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $objem;
    
    /**
     * @ORM\OneToMany(targetEntity="Cas", mappedBy="zavodnik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $casy;
    
    /**
     * @ORM\OneToMany(targetEntity="PezinskaBaba", mappedBy="zavodnik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $baba;
    
    /**
     * @ORM\OneToMany(targetEntity="DemontazKola", mappedBy="zavodnik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $demontaz;
    
    /**
     * @ORM\OneToMany(targetEntity="Projel", mappedBy="zavodnik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $projel;

    /**
     * @ORM\OneToMany(targetEntity="JizdaPravidelnosti", mappedBy="zavodnik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $pravidelnost;
    
    /**
     * @ORM\OneToMany(targetEntity="StanovenyCasKontroly", mappedBy="zavodnik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $stanoveneCasy;
    
    /**
     * @ORM\OneToMany(targetEntity="Body", mappedBy="zavodnik", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $body;
    
   
    /**
     * @ORM\ManyToOne(targetEntity="BalikZavodu", inversedBy="zavodnici")
     * @ORM\JoinColumn(name="zavod_id", referencedColumnName="id")
     */
    protected $zavod;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="ZnackovyTym", inversedBy="zavodnici")
     * @ORM\JoinColumn(name="tym_id", referencedColumnName="id")
     */
    protected $tym;
    
    
}