<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Cas extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

     /**
     * @ORM\Column(type="datetime")
     */
    protected $cas;
    
    /**
     * @ORM\ManyToOne(targetEntity="Etapa", inversedBy="casy")
     * @ORM\JoinColumn(name="etapa_id", referencedColumnName="id")
     */
    protected $etapa;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="Zavodnik", inversedBy="casy")
     * @ORM\JoinColumn(name="zavodnik_id", referencedColumnName="id")
     */
    protected $zavodnik;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="Zavod", inversedBy="casy")
     * @ORM\JoinColumn(name="zavod_id", referencedColumnName="id")
     */
    protected $zavod;
    
    
    public function __construct(){
        $this->historie = new \Doctrine\Common\Collections\ArrayCollection;
    }

    public function setHistorie($historie){
        $this->historie = $historie;
    }

    public function getHistorie(){
        return $this->historie;
    }

}