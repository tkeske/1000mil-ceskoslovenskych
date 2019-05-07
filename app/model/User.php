<?php

namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class User extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $email;

     /**
     * @ORM\Column(type="text")
     */
    protected $password;

    /**
     * @ORM\Column(type="string")
     */
    protected $role;
    
    
    /**
     * @ORM\OneToOne(targetEntity="Zavod")
     * @ORM\JoinColumn(name="zavod_id", referencedColumnName="id")
     */
    protected $zavod;
   
    /**
     * @ORM\OneToOne(targetEntity="Etapa")
     * @ORM\JoinColumn(name="etapa_id", referencedColumnName="id")
     */
    protected $etapa;

}