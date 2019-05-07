<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Category extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $jmeno;

	/**
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="category")
     */
    protected $categories;

    public function __construct(){
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection;
    }

    public function getArticles(){
        return $this->categories;
    }
}