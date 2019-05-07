<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model\Service;

use App\Body;

/**
 * Description of BonifikaceService
 *
 * @author tomas
 */
class BonifikaceService {
    
    private $em;
    
    public function __construct(\Doctrine\ORM\EntityManager $em){
        $this->em = $em;
    }
    
    public function pridejBody($zavod,$zavodnik,$stanovenyCas,$etapa,$prijezd,$odjezd,$cas){
        if ($stanovenyCas){
  
            if ($cas < $prijezd){
                $body = new Body();
                $body->zavodnik = $zavodnik;
                $body->etapa = $etapa;
                $body->body = 500;
                $body->zavod = $zavod;
                $this->em->persist($body);
            } 
            
            else if ($cas > $prijezd && $cas < $odjezd) {
                $body = new Body();
                $body->zavodnik = $zavodnik;
                $body->etapa = $etapa;
                $body->zavod = $zavod;
                $body->body = 0;
                $this->em->persist($body);
            }
            
            else if ($cas > $odjezd){
                $cast = $cas->getTimestamp();
                $odjezdt = $odjezd->getTimestamp();
                
                $minutes = ($cast - $odjezdt) / 60;
                $penalizace = 0;
                
                for($i=0; $i<$minutes; $i++){
                  $penalizace = $penalizace + 300;
                }
                
                $body = new Body();
                $body->zavodnik = $zavodnik;
                $body->zavod = $zavod;
                $body->etapa = $etapa;
                $body->body = $penalizace;
                $this->em->persist($body);
                
            }
            
            
            $this->em->flush();
        }
    }
}
