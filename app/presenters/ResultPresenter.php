<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Presenters;

use App\Model\Service\ResultService;

/**
 * Description of ResultPresenter
 *
 * @author tomas
 */
class ResultPresenter extends PublicPresenter {
    
    /**
     * @var ResultService @inject 
     */
    public $rs;
    
    public function renderDefault(){
        $zavody = $this->em->getRepository("App\BalikZavodu")->findAll();
        $this->template->zavody = $zavody;
    }
    
    public function renderZavod($id){
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        $zavody = $this->em->getRepository("App\Zavod")->findBy(["balik" => $id]);
        
         
        $this->rs->setPresenter($this);
        $this->rs->setBalik($balik);
        $this->rs->setZavody($zavody);
        
        $this->rs->newZavodHelper();
    }
    
    public function renderKategorie($id){
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        $zavody = $this->em->getRepository("App\Zavod")->findBy(["balik" => $id]);
        $this->rs->setPresenter($this);
        $this->rs->setBalik($balik);
        $this->rs->setZavody($zavody);
        
        
        $this->template->kategorieresults = $this->rs->getOrsKategorie($id);
        $this->rs->newZavodHelper();
    }
    
    public function renderEtapy($id){
        $zavody = $this->em->getRepository("App\Zavod")->findBy(array("balik" => $id));
        $this->template->zavody = $zavody;
    }
    
    public function renderEtapa($id){
        $zavod = $this->em->getRepository("App\Zavod")->find($id);
    
        
        foreach($zavod->balik->zavodnici  as $z){
            $ret[] = array("zavodnik" => $z, "body" => $z->body);
        }
        
        foreach($ret as $r){
            
            $bodu = 0;

            foreach($r["body"] as $k){
                if (isset($k->zavod->id)){
                    if ($k->zavod->id == $id){
                        $bodu = $bodu + $k->body;
                    }
                }
            }
            
            $ret2[] = array("zavodnik" => $r["zavodnik"], "body" => $bodu);
        }
        
        usort($ret2,  function ($a,$b) {

            return $a['body']>$b['body'];
        });
        
        foreach($ret2 as $key => $polozka){
            if ($polozka["zavodnik"]->status == "DSQ"){
                unset($ret2[$key]);
                array_push($ret2, $polozka);
            }
        }
        
        $this->template->results = $ret2;
    }
    
    public function renderTeamy($id){
       $teamy = $this->em->getRepository("App\ZnackovyTym")->findBy(array("balik" => $id));
       
       foreach($teamy as $team){
           if (!empty($team->zavodnici)){
               $tym[$team->jmenoTymu] = $team->zavodnici;
           }
       }
       
       if (isset($tym)){
           foreach($tym as $key => $t){
               
               $tymBody[$key] = 0;
               
               foreach($t as $zavodnik){
                   
                   $zavodnikBody = 0;
                   
                   foreach($zavodnik->body as $b){
                       $zavodnikBody = $zavodnikBody + $b->body;
                   }
                   
                   $tymBody[$key] = $tymBody[$key] + $zavodnikBody;
                   
               }
           }
       
       
            foreach($tym as $key => $zavodnici){
                foreach($tymBody as $k => $body){
                    if ($key == $k){
                        $tym[$key] = array("body" => $body, "zavodnici" => $zavodnici);
                    }
                }
            }
            
            
        uasort($tym,  function ($a,$b) {

            return $a['body']>$b['body'];
        });
            
            
       
       } else {
           $tym = array();
       }
       
       
       $this->template->tymy = $tym;
        
    }
    
    public function handleRefreshKategorie($id){
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        $zavody = $this->em->getRepository("App\Zavod")->findBy(["balik" => $id]);
        
        if ($this->isAjax()){
            $this->rs->setPresenter($this);
            $this->rs->setBalik($balik);
            $this->rs->setZavody($zavody);
        
        
            $this->template->kategorieresults = $this->rs->getOrsKategorie($id);
            $this->redrawControl("vysledkykategorie");
        }
    }
    
    public function handleRefresh($id){ 
       $zavody = $this->em->getRepository("App\Zavod")->findBy(["balik" => $id]);
       $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
       
       if ($this->isAjax()){
           $this->rs->setPresenter($this);
           $this->rs->setBalik($balik);
           $this->rs->setZavody($zavody);
           
           $this->rs->newZavodHelper();
           $this->redrawControl("vysledky");
       }
    }
}
