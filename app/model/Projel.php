<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Projel extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zavodnik", inversedBy="projel")
     * @ORM\JoinColumn(name="zavodnik_id", referencedColumnName="id")
     */
    protected $zavodnik;

    /**
     * @ORM\ManyToOne(targetEntity="Etapa", inversedBy="projel")
     * @ORM\JoinColumn(name="etapa_id", referencedColumnName="id")
     */
    protected $etapa;
    
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $projel;
    
}