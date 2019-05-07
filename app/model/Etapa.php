<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Etapa extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    
    /**
     * @ORM\Column(length= 150)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Zavod", inversedBy="etapy")
     * @ORM\JoinColumn(name="zavod_id", referencedColumnName="id")
     */
    protected $ref;
    
    /**
     * @ORM\OneToMany(targetEntity="Cas", mappedBy="etapa", cascade="remove" ,fetch="EXTRA_LAZY")
     */
    protected $casy;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Projel", mappedBy="etapa", cascade="remove" ,fetch="EXTRA_LAZY")
     */
    protected $projel;
    
    
    /**
     * @ORM\OneToMany(targetEntity="StanovenyCasKontroly", mappedBy="etapa", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $stanoveneCasy;
    
    /**
     * @ORM\OneToMany(targetEntity="Body", mappedBy="etapa", cascade="remove",fetch="EXTRA_LAZY")
     * @ORM\Column(nullable=true)
     */
    protected $body;
    
    /**
     * @ORM\OneToMany(targetEntity="Historie", mappedBy="etapa", cascade="remove",fetch="EXTRA_LAZY")
     */
    protected $historie;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $externi;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $bezcasu;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $obyc;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $jizdaPravidelnosti;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $dovednostniKontrola;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $jeUkoncena;
    
    public function __construct(){
        $this->casy = new \Doctrine\Common\Collections\ArrayCollection;
        $this->stanoveneCasy = new \Doctrine\Common\Collections\ArrayCollection;
    }
    
    public function getCasy(){
        return $this->casy;
    }
    
    public function getStanoveneCasy(){
        return $this->stanoveneCasy;
    }
    
    public function setCasy($casy){
        $this->casy = $casy;
    }
    
    public function setStanoveneCasy($stcasy){
        $this->stanoveneCasy = $stcasy;
    }
    
}