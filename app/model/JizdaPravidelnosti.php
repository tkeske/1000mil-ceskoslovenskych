<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class JizdaPravidelnosti extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Zavodnik", inversedBy="pravidelnost")
     * @ORM\JoinColumn(name="zavodnik_id", referencedColumnName="id")
     */
    protected $zavodnik;

    /**
     * @ORM\Column(type="string")
     */
    protected $vyslednyCas;
            
    /**
     * @ORM\Column(type="integer")
     */
    protected $trBody;
    
}