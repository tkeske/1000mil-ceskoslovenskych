<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class PezinskaBaba extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zavodnik", inversedBy="baba")
     * @ORM\JoinColumn(name="zavodnik_id", referencedColumnName="id")
     */
    protected $zavodnik;

    /**
     * @ORM\Column(type="string")
     */
    protected $casPrvniJizdy;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $casDruheJizdy;
    
    
    /**
     * @ORM\Column(type="string")
     */
    protected $rozdilCasu;
     
    /**
     * @ORM\Column(type="integer")
     */
    protected $trBody;
    
}