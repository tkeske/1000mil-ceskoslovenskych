<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Historie extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $cislo;

     /**
     * @ORM\Column(type="datetime")
     */
    protected $puvodniCas;
    
      /**
     * @ORM\Column(type="datetime")
     */
    protected $zmenenyCas;
    
      /**
     * @ORM\ManyToOne(targetEntity="Etapa", inversedBy="historie")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="id")
     */
    protected $etapa;

}