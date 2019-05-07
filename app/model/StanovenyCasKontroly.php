<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class StanovenyCasKontroly extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Zavodnik", inversedBy="casy")
     * @ORM\JoinColumn(name="zavodnik_id", referencedColumnName="id")
     */
    protected $zavodnik;

    /**
     * @ORM\ManyToOne(targetEntity="Etapa", inversedBy="stanoveneCasy")
     * @ORM\JoinColumn(name="etapa_id", referencedColumnName="id")
     */
    protected $etapa;

    
     /**
     * @ORM\Column(type="datetime")
     */
    protected $prijezd;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $odjezd;
    
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $start;
    
}