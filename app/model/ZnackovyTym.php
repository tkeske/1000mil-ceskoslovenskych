<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class ZnackovyTym extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $jmenoTymu;
    
    /**
     * @ORM\OneToMany(targetEntity="Zavodnik", mappedBy="tym", cascade="remove", fetch="EXTRA_LAZY")
     */
    protected $zavodnici;
    
    /**
     * @ORM\ManyToOne(targetEntity="BalikZavodu", inversedBy="teamy")
     * @ORM\JoinColumn(name="balik_id", referencedColumnName="id")
     */
    protected $balik;
    
    
}