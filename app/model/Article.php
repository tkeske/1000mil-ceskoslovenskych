<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Article extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $title;

     /**
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(type="string")
     */
    protected $date;

    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="categories")
     */
    protected $category;

    /**
     * @ORM\OneToMany(targetEntity="Coment", mappedBy="ref", cascade="remove")
     */
    protected $coments;

    /**
     * @ORM\OneToMany(targetEntity="Obrazek", mappedBy="refe", cascade="remove")
     */
    protected $obrazy;

    public function __construct(){
        $this->category = new \Doctrine\Common\Collections\ArrayCollection;
        $this->coments = new \Doctrine\Common\Collections\ArrayCollection;
        $this->obrazy = new \Doctrine\Common\Collections\ArrayCollection;
    }

    public function setCategory($category){
        $this->category = $category;
    }

    public function setComent($coment){
        $this->coments = $coment;
    }

    public function getObrazy(){
        return $this->obrazy;
    }

    public function setObrazy($obrazy){
        $this->obrazy = $obrazy;
    }

    public function getCategory(){
        return $this->category;
    }

    public function getComents(){
        return $this->coments;
    }
}