<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Obrazek extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $path;

    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="obrazy")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="id")
     */
    protected $refe;
}