<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Zavod extends \Kdyby\Doctrine\Entities\BaseEntity
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
     * @ORM\OneToMany(targetEntity="Etapa", mappedBy="ref", cascade="remove", fetch="EXTRA_LAZY")
     */
    protected $etapy;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Cas", mappedBy="zavod", cascade="remove", fetch="EXTRA_LAZY")
     */
    protected $casy;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Body", mappedBy="zavod", cascade="remove", fetch="EXTRA_LAZY")
     */
    protected $body;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="BalikZavodu", inversedBy="zavody")
     * @ORM\JoinColumn(name="balik_id", referencedColumnName="id")
     */
    protected $balik;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $start;
    
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $externi;

    public function __construct(){
        $this->etapy = new \Doctrine\Common\Collections\ArrayCollection;
    }

    public function setEtapy($etapy){
        $this->etapy = $etapy;
    }

    public function getEtapy(){
        return $this->etapy;
    }
    
}