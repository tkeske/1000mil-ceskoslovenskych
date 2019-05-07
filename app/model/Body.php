<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Body extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

     /**
     * @ORM\Column(type="integer")
     */
    protected $body;
    
    /**
     * @ORM\ManyToOne(targetEntity="Etapa", inversedBy="body", cascade="remove")
     * @ORM\JoinColumn(name="etapa_id", referencedColumnName="id",nullable=true, onDelete="SET NULL")
     */
    protected $etapa;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="Zavod", inversedBy="body")
     * @ORM\JoinColumn(name="zavod_id", referencedColumnName="id")
     */
    protected $zavod;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="Zavodnik", inversedBy="body")
     * @ORM\JoinColumn(name="zavodnik_id", referencedColumnName="id")
     */
    
    protected $zavodnik;

}