<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Coment extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $nick;

    /**
     * @ORM\Column(type="text")
     */
    protected $obsah;

    /**
     * @ORM\Column(type="text")
     */
    protected $status;

     /**
     * @ORM\Column(type="string")
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="coments")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="id")
     */
    protected $ref;

}