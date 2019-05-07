<?php

namespace App\Model\Service;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResultService
 *
 * @author tomas
 */
class ResultService {
    
    private $em;
    
    private $p;
    
    private $zavod;
    
    private $zavody = array();
    
    private $balik;
    
    public function __construct(\Doctrine\ORM\EntityManager $em){
        $this->em = $em;

    }
    
    public function setPresenter($p){
        $this->p = $p;
    }
    
    public function setZavod($zavod){
        $this->zavod = $zavod;
    }
    
    public function setZavody($zavody){
        $this->zavody = $zavody;
    }
    
    public function setBalik($balik){
        $this->balik = $balik;
    }
    
    /**
     * 
     * Get overall results 
     * @return int
     */
    public function getOrsNew(){
       

        foreach($this->zavody as $z){
            $ids[] = $z->id;
        }
        
       
      
        if (isset($ids)){
            $str = join(",", $ids);
            $sql = "SELECT a FROM App\Cas a WHERE a.zavodnik = ?1 AND a.zavod NOT IN ('".$str."') ORDER BY a.cas ASC";
        } else {
            $sql  = "SELECT a FROM App\Cas a WHERE a.zavodnik = ?1 ORDER BY a.cas ASC";
        }
        
        $cnt = 0;

        foreach($this->zavody as $zav)
        {
            
           $cnt++;
            
            foreach($zav->balik->zavodnici as $zavodnik){     

                $casy =  $this->em->createQuery($sql)
                          ->setParameter(1, $zavodnik->id)
                          ->getResult();
                
                $body = $zavodnik->body;
                $bf = 0;
                
                foreach($body as $polozka){
                    $bf = $bf + $polozka->body;
                }
                
               // bdump($casy);
                
                
                //bdump($body);
                
                //foreach($casy as $cas){
                    
                  //  if (isset($cas[0]->cas)){
                    //    $soucetEtap[] = $cas[0]->cas->getTimestamp();
                if (!empty($casy)){
                    $finishDT = end($casy)->cas;
                } else {
                    $finishDT = null;
                }
                
                //break;
                    //}
                //}

               // if (isset($soucetEtap)) {
                //    $s = array_sum($soucetEtap);
                if ($cnt == 1){
                    
                    
                    //if (!empty($casy) && !empty($body)){
                        $poradi[] = array("body" => $bf, "zavodnik" => $zavodnik, "finishDT" => $finishDT);
                   // }
                }
               // $soucetEtap = null ; 
            }
        }

        
        if (isset($poradi)){
            
            //bdump($poradi);


            usort($poradi,  function ($a,$b) {

                if($a['body'] == $b['body']){
                    return $a['finishDT']>$b['finishDT'];
                } else {
                    return $a['body']>$b['body'];
                }

            });
            
            foreach($poradi as $key => $polozka){
                if ($polozka["zavodnik"]->status == "DSQ"){
                    unset($poradi[$key]);
                    array_push($poradi, $polozka);
                }
            }
            
            return $poradi;
        }
    }
    
    public function getOverallResults(){
                
        foreach($this->zavod->balik->zavodnici as $zavodnik){       
    
            
            $casy =  $this->em->createQuery('SELECT a, sum(a.cas) FROM App\Cas a WHERE a.zavodnik = ?1 ORDER BY a.cas ASC')
                      ->setParameter(1,$zavodnik->id)
                      ->getResult();
            
            
            
            foreach($casy as $cas){
                if (isset($cas[0]->cas)){
                    $soucetEtap[] = $cas[0]->cas->getTimestamp();
                    $finishDT = end($casy)[0]->cas;
                }
            }
            
            
            if (isset($soucetEtap)) {
                $s = array_sum($soucetEtap);

                $poradi[strval($s)] = array("zavodnik" => $zavodnik, "finishDT" => $finishDT);
                
            }
            
            $soucetEtap = null ; 
        }
        
        if (isset($poradi)){
            
            ksort($poradi);
            
           foreach($poradi as $polozka){
                foreach($polozka["zavodnik"]->body as $bod){
                    $a[] = $bod->body;
                }
                
                if (isset($a)){
                    $b = array_sum($a);
                } else {
                    $b = 0;
                }
                

                $cc[] = array("body" => $b, "polozka" => $polozka);
                
                $a = null;
           }
           
           
    //        usort($cc,  function ($a,$b) {
    //            //bdump($a["polozka"]);
    //             return $a['polozka']['finishDT']>$b['polozka']['finishDT'];
    //        });

            usort($cc,  function ($a,$b) {

                if($a['body'] == $b['body']){
                    return $a['polozka']['finishDT']>$b['polozka']['finishDT'];
                } else {
                    return $a['body']>$b['body'];
                }

            });
            
            return $cc;
        }
    }
    
    public function historiePrujezdu($etapa){
        
        //bdump($etapa);
         //foreach($this->zavod->balik->zavodnici as $zavodnik){       
    
            
            $casy =  $this->em->createQuery('SELECT a FROM App\Cas a WHERE a.etapa = ?1 ORDER BY a.cas DESC')
                      ->setParameter(1,$etapa->id)
                      ->getResult();
            
            
            foreach($casy as $cas){
                
                $body = 0;
                
                foreach($cas->zavodnik->body as $b){
                    if ($b->etapa->id == $etapa->id){
                        $body = $body + $b->body;
                    }
                }
                
                $result[] = array("prujezd" => $cas->cas, "zavodnik" => $cas->zavodnik, 
                                    "body" => $body);
            }
            
            if (!isset($result)){
                $result = [];
            }
       // }
            return $result;
    }
    
    public function getEtapaResults($etapaid){
        $casy =  $this->em->createQuery('SELECT a FROM App\Cas a WHERE a.etapa = ?1 ORDER BY a.cas ASC')
                      ->setParameter(1,$etapaid)
                      ->getResult();
        
        foreach($casy as $cas){
                         
            foreach($cas->zavodnik->body as $bod){
            
                
                if (!is_null($bod->etapa)){
                    if ($bod->etapa->id == $etapaid){

                        $a[] = $bod->body;
                    }
                }
            }
            
            if (isset($a)){
                $b = array_sum($a);
            } else {
                $b = 0;
            }
            
              
            $cc[] = array("body" => $b, "cas" => $cas);  
            
            $a = null;
              
        }
        
        if (!isset($cc)){
            $cc = array();
        }
        
        usort($cc,  function ($a,$b) {
             return $a['cas']->cas>$b['cas']->cas;
        });

//        usort($cc,  function ($a,$b) {
//            
//            if($a['body'] == $b['body']){
//                return $a['cas']->cas>$b['cas']->cas;
//            } else {
//                return $a['body']<$b['body'];
//            }
//             
//        });
    
        return $cc;
    }
    
    public function getEtapsNew($zavodid){
       
        
        $cc = $this->getOverallResults();
        $cb = array();
        
        if (isset($cc)){

            foreach($cc as $a){

                $test = false;

                foreach ($a["polozka"]["zavodnik"]->casy as $c){

                    if ($c->etapa->ref->id == $zavodid){
                        $test = true;
                    }
                }

                if ($test){
                    $cb[] = $a;
                }

            }
        }
        
      //  bdump($cc);
        
        return $cb;
    }
    
    
    public function getOrsKategorie($zavod){
       
        $do1000 = $this->em->createQuery('SELECT a FROM App\Zavodnik a WHERE a.objem < ?1 AND a.zavod = ?2 ORDER BY a.objem ASC')
                      ->setParameter(1,1000)
                      ->setParameter(2,$zavod)
                      ->getResult();
        
        $do1500 = $this->em->createQuery('SELECT a FROM App\Zavodnik a WHERE a.objem > ?1 AND a.objem < ?2 AND a.zavod = ?3 ORDER BY a.objem ASC')
                  ->setParameter(1,1000)
                  ->setParameter(2,1500)
                  ->setParameter(3,$zavod)
                  ->getResult();
        
        $do2000 = $this->em->createQuery('SELECT a FROM App\Zavodnik a WHERE a.objem > ?1 AND a.objem < ?2 AND a.zavod = ?3 ORDER BY a.objem ASC')
                  ->setParameter(1,1500)
                  ->setParameter(2,2000)
                  ->setParameter(3,$zavod)
                  ->getResult();
        
        $nad2000 = $this->em->createQuery('SELECT a FROM App\Zavodnik a WHERE a.objem > ?1 AND a.zavod = ?2 ORDER BY a.objem ASC')
                  ->setParameter(1,2000)
                  ->setParameter(2,$zavod)
                  ->getResult();
        
        foreach($do1000 as $zavodnik){
                
            $bf = 0;

            foreach($zavodnik->body as $polozka){
                $bf = $bf + $polozka->body;
            }

            $retDo1000[] = array("zavodnik" => $zavodnik, "body" => $bf);
        }
        
        if (isset($retDo1000)){
            usort($retDo1000,  function ($a,$b) {

                return $a['body']>$b['body'];
            });
        
        
            foreach($retDo1000 as $key => $polozka){
                if ($polozka["zavodnik"]->status == "DSQ"){
                    unset($retDo1000[$key]);
                    array_push($retDo1000, $polozka);
                }
            }
        }
        
        foreach($do1500 as $zavodnik){
                
            $bf = 0;

            foreach($zavodnik->body as $polozka){
                $bf = $bf + $polozka->body;
            }

            $retDo1500[] = array("zavodnik" => $zavodnik, "body" => $bf);
        }
        
        if (isset($retDo1500)){
            usort($retDo1500,  function ($a,$b) {

                return $a['body']>$b['body'];
            });


            foreach($retDo1500 as $key => $polozka){
                if ($polozka["zavodnik"]->status == "DSQ"){
                    unset($retDo1500[$key]);
                    array_push($retDo1500, $polozka);
                }
            }
        }
        
        foreach($do2000 as $zavodnik){
                
            $bf = 0;

            foreach($zavodnik->body as $polozka){
                $bf = $bf + $polozka->body;
            }

            $retDo2000[] = array("zavodnik" => $zavodnik, "body" => $bf);
        }
        
        if (isset($retDo2000)){
            usort($retDo2000,  function ($a,$b) {

                return $a['body']>$b['body'];
            });

            foreach($retDo2000 as $key => $polozka){
                if ($polozka["zavodnik"]->status == "DSQ"){
                    unset($retDo2000[$key]);
                    array_push($retDo2000, $polozka);
                }
            }
        }
        
        foreach($nad2000 as $zavodnik){
                
            $bf = 0;

            foreach($zavodnik->body as $polozka){
                $bf = $bf + $polozka->body;
            }

            $retNad2000[] = array("zavodnik" => $zavodnik, "body" => $bf);
        }
        
        if (isset($retNad2000)){
            usort($retNad2000,  function ($a,$b) {

                return $a['body']>$b['body'];
            });

            foreach($retNad2000 as $key => $polozka){
                if ($polozka["zavodnik"]->status == "DSQ"){
                    unset($retNad2000[$key]);
                    array_push($retNad2000, $polozka);
                }
            }
        }
        
        if (isset($retDo1000, $retDo1500, $retDo2000, $retNad2000)){
            return array("do1000" => $retDo1000, "do1500" => $retDo1500, 
                "do2000" => $retDo2000, "nad2000" => $retNad2000);
        } else {
            return array();
        }
    }
    
    public function zavodHelper(){
        $results = $this->getOverallResults($this->zavod);
        $this->p->template->zavod = $this->zavod;
        $this->p->template->results = $results;
    }
    
    public function historieHelper($etapa){
        $results = $this->historiePrujezdu($etapa);
        $this->p->template->zavod = $this->zavod;
        $this->p->template->results = $results;
    }
    
    public function newZavodHelper(){
        $results = $this->getOrsNew($this->zavody);
        $this->p->template->zavod = $this->balik;
        $this->p->template->results = $results;
    } 
    
    
    public function pezinskaBabaVysledky(){
        $pbs = $this->em->getRepository("App\PezinskaBaba")->findAll();
        
        foreach($pbs as $p){
            $ret[] = array("zavodnik" => $p->zavodnik , 
                "body" => $p->trBody, "1jizda" => $p->casPrvniJizdy,
                "2jizda" =>  $p->casDruheJizdy, "rozdil" => $p->rozdilCasu);
        }
        
        if (isset($ret)){
            usort($ret,  function ($a,$b) {

                return $a['body']>$b['body'];
            });
            
            return $ret;
        } else {
            return array();
        }
    }
    
    public function jizdaPravidelnostiVysledky(){
        $jps = $this->em->getRepository("App\JizdaPravidelnosti")->findAll();
        
        foreach($jps as $p){
            $ret[] = array("zavodnik" => $p->zavodnik , 
                "body" => $p->trBody, "cas" => $p->vyslednyCas);
        }
        
        if (isset($ret)){
            usort($ret,  function ($a,$b) {

                return $a['body']>$b['body'];
            });
            
            return $ret;
        } else {
            return array();
        }
    }
    
    public function demontazKolaVysledky(){
        $dm = $this->em->getRepository("App\DemontazKola")->findBy(array(), array("id" => "DESC"));
        
        foreach($dm as $d){
            $ret[] = array("zavodnik" => $d->zavodnik , 
                "body" => $d->trBody, "cas" => $d->casDemontaze);
        }
        
        if (isset($ret)){
//            usort($ret,  function ($a,$b) {
//
//                return $a['body']>$b['body'];
//            });
//            
            return $ret;
        } else {
            return array();
        }
    }
}
