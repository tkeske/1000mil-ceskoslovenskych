<?php

/**
 * @author Tomáš Keske
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Cas;
use App\StanovenyCasKontroly;
use App\Zavod;
use App\Etapa;
use App\Zavodnik;
use App\Historie;
use App\Body;
use App\BalikZavodu;
use App\PezinskaBaba;
use App\Projel;
use App\JizdaPravidelnosti;
use App\DemontazKola;
use App\ZnackovyTym;
use App\Model\Service\ResultService;
use App\Model\Service\BonifikaceService;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class AdminPresenter extends RestrictedPresenter
{
    
    /**
     * @var ResultService @inject 
     */
    public $rs;
    

    /**
     *  @var BonifikaceService @inject 
     */
    public $bonifikace;
    
    public function actionLogout(){
        $this->getUser()->logout(true);

        $this->flashMessage("Byl jste úspěšně odhlášen.");
        $this->redirect("Result:default");
    }
    
    public function createComponentVe() {
        $form = new Form;
        
        $form->addText('jmeno', 'Jméno kontroly:');
        
        $form->addSubmit('submit', "Vytvořit kontrolu");
        
        $form->addCheckbox('bc', "Kontrola bez času");
        
        $form->addCheckbox("ev", "Pezinská baba");

        $form->addCheckbox("jp", "Jízda pravidelnosti");
        
        $form->addCheckbox("dk", "Dovednostní kontrola");

        $form->onSuccess[] = [$this, 'veSuccess'];

        return $form;
    }
    
    public function veSuccess($form, $values){
        $id = $this->getParameter("id");
        $zavod = $this->em->getRepository("App\Zavod")->find($id);
        $etapa = new Etapa();
        $etapa->name = $values->jmeno;
        $etapa->ref = $zavod;
        
        if ($values->ev){
            $etapa->externi = true;
        } else {
            $etapa->externi = false;
        }
        
        if ($values->bc){
            $etapa->bezcasu = true;
        } else {
            $etapa->bezcasu = false;
        }
        
        if ($values->jp){
            $etapa->jizdaPravidelnosti = true;
        } else {
            $etapa->jizdaPravidelnosti = false;
        }
        
        if ($values->dk){
            $etapa->dovednostniKontrola = true;
        } else {
            $etapa->dovednostniKontrola = false;
        }
        
        $etapa->jeUkoncena = false;
        
        if (!$values->ev && !$values->bc && !$values->jp && !$values->dk){
            $etapa->obyc = true;
        } else {
            $etapa->obyc = false;
        }
        
        $this->em->persist($etapa);
        $this->em->flush();
        
        $this->flashMessage("Kontrola úspěšně vytvořena.");
        $this->redirect("Admin:etapa", $etapa->getId());
         
    }
    
    public function createComponentPz() {
        $form = new Form;
        
        $form->addText('jmeno', 'Jméno:');
        
        $form->addText('cislo', 'Startovní číslo:');
        
        $form->addText('znacka', 'Značka vozu:');
        
        $form->addText('objem', 'Objem vozu:');
        
        $form->addSubmit('submit', "Přidat");
        
        $form->onValidate[] = [$this, 'pzValidate'];

        $form->onSuccess[] = [$this, 'pzSuccess'];

        return $form;
    }
    
    public function pzValidate($form, $values){
        
        $zavodid = $this->getParameter("id");
        
        $zavodnik = $this->em->getRepository("App\Zavodnik")
                 ->findBy(array("cislo" => $values->cislo, "zavod" => $zavodid));
        
        if (count($zavodnik) > 1) {
            $form->addError("Závodník s tímto startovním číslem již existuje.");
            $this->flashMessage("Závodník s tímto startovním číslem již existuje.", "danger");
        }
    }
    
    public function pzSuccess($form, $values){
        $id = $this->getParameter("id");
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        $zavodnik = new Zavodnik();
        $zavodnik->jmeno = $values->jmeno;
        $zavodnik->cislo = $values->cislo;
        $zavodnik->znacka = $values->znacka;
        $zavodnik->zavod = $balik;
        $zavodnik->objem = $values->objem;
        $this->em->persist($zavodnik);
        $this->em->flush();
        $this->redirect("Admin:balik", $id);
         
    }
    
    public function actionZavody(){
        $this->redirToEtapa();
    }
    
    public function actionImport($id){
        
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref;
        
        if (isset($zavod->body)){
        
            foreach($zavod->body as $polozka){
                $result[] = array("body" => $polozka->body, "zavodnik" => $polozka->zavodnik) ;
            }
        }
        
        if (isset($result)){
       
            usort($result,  function ($a,$b) {

                return $a['body']>$b['body'];

            });
            
            $this->template->poradi = $result;
        
        }
        
        $this->template->zavod = $zavod;
        $this->template->name = $zavod->name;
    }
    
    public function createComponentJP(){
        $form = new Form;
        
        $form->addUpload('file', 'Soubor s výsledky (.csv):');
        $form->addSubmit('submit', 'Upload');
        $form->onValidate[] = [$this, "jpValidate"];
        $form->onSuccess[] = [$this, "jpSuccess"];
        
        return $form;
    }
    
    public function jpValidate($form, $values){
        if (!$values->file->isOk()){
            $form->addError("Soubor není v pořádku");
        }
        
        $ext = pathinfo($values->file->name, PATHINFO_EXTENSION);
        
        if ($ext != "csv"){
            $form->addError("Prosím uploadujte jen .csv soubor.");
        }
    }
    
    public function jpSuccess($form, $values){
        
        $id = $this->getParameter("id");
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref;
        
        $qb = $this->em->createQueryBuilder();
                $qb->delete('App\Body', 'b');
                $qb->where('b.zavod = :zavod');
                $qb->andWhere('b.etapa is NULL');
                $qb->setParameter('zavod', $id);
        
        $qb->getQuery()->execute();
        
        $h = fopen($values->file->getTemporaryFile(), "r");
        $cnt = 0;
        
        while($row = fgetcsv($h)){
            if ($cnt > 0){
                    
                $data = explode(";", $row[0]);
                $cislo = $data[0];
                $vyslednyCas = $data[1];
                $trBody = $data[2];

                $bodyEn = new Body();
                $bodyEn->zavod = $zavod;

                $zavodnik = current($this->em->getRepository("App\Zavodnik")
                                ->findBy(array("cislo" => $cislo, "zavod" => $zavod->id)));

                $bodyEn->etapa = $etapa;

                if ($zavodnik){
                    $bodyEn->zavodnik = $zavodnik;
                    $bodyEn->body = intval($trBody);
                    $this->em->persist($bodyEn);

                    $jp = new JizdaPravidelnosti();
                    $jp->zavodnik = $zavodnik;
                    $jp->vyslednyCas = $vyslednyCas;
                    $jp->trBody = $trBody;
                    $this->em->persist($jp);
                }

            }
            
            $cnt++;
        }
        
        fclose($h);
        $this->em->flush();
        
        $this->flashMessage("Výsledky byly úspěšně importovány.", "info");
        $this->redirect("Admin:etapa", $etapa->id);
    }
    
    public function createComponentUploadCsv(){
        $form = new Form;
        
        $form->addUpload('file', 'Soubor s výsledky (.csv):');
        $form->addSubmit('submit', 'Upload');
        $form->onValidate[] = [$this, "uploadCsvValidate"];
        $form->onSuccess[] = [$this, "uploadCsvSuccess"];
        
        return $form;
    }
    
    public function uploadCsvValidate($form, $values){
        if (!$values->file->isOk()){
            $form->addError("Soubor není v pořádku");
        }
        
        $ext = pathinfo($values->file->name, PATHINFO_EXTENSION);
        
        if ($ext != "csv"){
            $form->addError("Prosím uploadujte jen .csv soubor.");
        }
    }
    
    public function uploadCsvSuccess($form, $values){
        
        $id = $this->getParameter("id");
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref;
        
        $qb = $this->em->createQueryBuilder();
                $qb->delete('App\Body', 'b');
                $qb->where('b.zavod = :zavod');
                $qb->andWhere('b.etapa is NULL');
                $qb->setParameter('zavod', $id);
        
        $qb->getQuery()->execute();
        
        $h = fopen($values->file->getTemporaryFile(), "r");
        $cnt = 0;
        bdump($zavod);
        
        while($row = fgetcsv($h)){
            if ($cnt > 0){
                
                $data = explode(";", $row[0]);
                $cislo = $data[0];
                $casPrvniJizdy = $data[1];
                $casDruheJizdy = $data[2];
                $rozdilCasu = $data[3];
                $trBody = $data[4];

                $bodyEn = new Body();
                $bodyEn->zavod = $zavod;

                $zavodnik = current($this->em->getRepository("App\Zavodnik")
                                ->findBy(array("cislo" => $cislo, "zavod" => $zavod->id)));

                $bodyEn->etapa = $etapa;

                if ($zavodnik){
                    $bodyEn->zavodnik = $zavodnik;
                    $bodyEn->body = intval($trBody);
                    $this->em->persist($bodyEn);

                    $pb = new PezinskaBaba();
                    $pb->zavodnik = $zavodnik;
                    $pb->casPrvniJizdy = $casPrvniJizdy;
                    $pb->casDruheJizdy = $casDruheJizdy;
                    $pb->rozdilCasu = $rozdilCasu;
                    $pb->trBody = $trBody;
                    $this->em->persist($pb);
                }
               
            }
            
            $cnt++;
        }
        
        fclose($h);
        $this->em->flush();
        
        $this->flashMessage("Výsledky byly úspěšně importovány.", "info");
        $this->redirect("Admin:etapa", $etapa->id);
    }
    
    public function actionZavod($id){
        
        
        $this->redirToEtapa();
        
        $zavod = $this->em->getRepository("App\Zavod")->find($id);
        
//        if ($zavod->externi){
//            $this->redirect("Admin:import", $id);
//        }
        
        $this->getSession()->getSection("zavod")->zavod = $zavod;
        $start = $zavod->start;
        $this->template->id = $id;
        $this->template->zavod = $zavod;
        $this->template->etapy = $zavod->getEtapy();
        $this->template->name = $zavod->name;  
        
        $this->rs->setPresenter($this);
        $this->rs->setZavod($zavod);
      //  $this->rs->zavodHelper($id);
        
      
    
        $or = $this->rs->getEtapsNew($id);
        

        //$or = $this->rs->getOverallResults();
       
   
        $this->template->results = $or;
    }
    
    public function actionSmazatEtapu($zavod, $id){
        $etapa = $this->em->getRepository('App\Etapa')->find($id);
        
        $this->em->remove($etapa);

        $this->em->flush();
        $this->redirect("Admin:zavod", $zavod); 
    }
    
    public function createComponentVz() {
        $form = new Form;
        
        $form->addText('jmeno', 'Jméno etapy:');
        
        $form->addText('start', 'Čas startu:');
        
        //$form->addCheckbox('ec', ' Měřeno externí časomírou');
        
        $form->addSubmit('submit', "Vytvořit etapu");

        $form->onSuccess[] = [$this, 'vzSuccess'];

        return $form;
    }
    
    public function vzSuccess($form, $values){
        $id = $this->getParameter("id");
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        $zavod = new Zavod();
        $zavod->name = $values->jmeno;
        $date = new \DateTime($values->start);
        $zavod->start = $date;
        $zavod->balik = $balik;
        //$zavod->externi = false;
        
        $zavod->externi = false;
        
        
        $this->em->persist($zavod);
        $this->em->flush();
        $this->flashMessage("Etapa úspěšně vytvořena.");
        $this->redirect("Admin:balik", $id);
    }
    
    public function renderZavody(){
       $zavody = $this->em->getRepository("App\Zavod")->findAll();
       $this->template->zavody = $zavody;
        
    }
    
    public function createComponentStart(){
        $id = $this->getParameter("id");
        
        $zavod = $this->em->getRepository("App\Zavod")->find($id);
                
        $form = new Form;  
        
        $form->addText('cas', 'Čas:')->setValue($zavod->start->format("d.m.Y H:i"));
        
        $form->addSubmit('submit', 'Uložit čas');

        $form->onSuccess[] = [$this, 'startSuccess'];

        return $form;
    }
    
    public function startSuccess($form, $values){
        $id = $this->getParameter("id");
        $zavod = $this->em->getRepository("App\Zavod")->find($id);
        $zavod->start = new \DateTime($values->cas);
        $this->em->persist($zavod);
        $this->em->flush();
        $this->flashMessage("Čas startu úspěšně pozměněn.");
        $this->redirect("Admin:zavod", $id);
    }
   
    
    public function createComponentCas(){
        $form = new Form;  
        
        $form->addText('cislo', 'Číslo závodníka:');
        
        $form->addSubmit('submit', 'Uložit čas');

        $form->onSuccess[] = [$this, 'casSuccess'];
        
        $form->onValidate[] = [$this, 'casValidate'];

        return $form;
    }
    
    public function ulozCas($etapa, $zavodnik){
        $cas = new Cas();
        $cas->etapa = $etapa;
        $cas->zavod = $etapa->ref;
        $cas->zavodnik = $zavodnik;
        $cas->cas = new \DateTime("now");
        $this->em->persist($cas);
        $this->em->flush();
        
        return $cas;
    }
    
    public function casValidate($form, $values){
        $id = $this->getParameter("id");
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref->balik;
        
        $zavodnik = current($this->em->getRepository("App\Zavodnik")
                                ->findBy(array("cislo" => $values->cislo, "zavod" => $zavod->id)));
        
        if (!$zavodnik){
            $form->addError("Závodník s tímto číslem v závodě nefiguruje.");
            $this->flashMessage("Závodník s tímto číslem v závodě nefiguruje.", "danger");
        }
    }
    
    public function casSuccess($form, $values){
        $id = $this->getParameter("id");
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref->balik;
        $zavod2 = $etapa->ref;
        
        $zavodnik = current($this->em->getRepository("App\Zavodnik")
                                ->findBy(array("cislo" => $values->cislo, "zavod" => $zavod->id)));

        
        $stanovenyCas = current($this->em->getRepository('App\StanovenyCasKontroly')
                            ->findBy(array('etapa' => $etapa->id, 'zavodnik' => $zavodnik->id)));
        
        
        $prijezd = $stanovenyCas->prijezd;
        $odjezd = $stanovenyCas->odjezd;
        
       
        foreach($zavodnik->casy as $cas){
            $p[] = $cas->etapa->id;
        }
        
        if (isset($p)){
            $p = array_unique($p);

            if (count($zavodnik->casy) == 0 || !in_array($etapa->id, $p)) {
                $cas = $this->ulozCas($etapa, $zavodnik)->cas;
                $this->bonifikace->pridejBody($zavod2, $zavodnik, $stanovenyCas, $etapa, $prijezd, $odjezd, $cas);
                
                
            }
        } else {
            $cas = $this->ulozCas($etapa, $zavodnik)->cas;
            $this->bonifikace->pridejBody($zavod2, $zavodnik, $stanovenyCas, $etapa, $prijezd, $odjezd, $cas);
        }
        
        $this->redirect("Admin:etapa", $id);
       
    }
    
    
    public function createComponentPridatBody(){
        
        $id = $this->getParameter("id");
        
        $form = new Form;  
        
        $form->addText('cislo', 'Číslo závodníka:');
        
        $form->addText('body', 'Body');
        
        $zavody = $this->em->getRepository("App\Zavod")->findBy(array("balik" => $id));
        
        foreach($zavody as $zavod){
            $ret[$zavod->id] = $zavod->name;
        }
        
        if (isset($ret)){
             $form->addSelect("etapa", "Etapa", $ret)
                     ->setPrompt('--- Vyberte etapu ---');
        } else {
            $form->addSelect("etapa", "Etapa", array())
                    ->setPrompt('--- Vyberte etapu ---');
        }
        
        $form->addDependentSelectBox('kontrola', 'Kontrola', $form['etapa'])
                ->setDependentCallback(function ($values)  {
                    $data = new \NasExt\Forms\Controls\DependentSelectBoxData;
                    
                    bdump($values);
                    if (isset($values["etapa"])){
                         $etapy = $this->em->createQuery('SELECT e FROM App\Etapa e WHERE e.ref = ?1')
                                        ->setParameter(1, $values["etapa"])
                                        ->getResult();

                        foreach($etapy as $etapa){
                            $arr[$etapa->id] = $etapa->name;
                        }

                        if (isset($arr)){
                            $data->setItems($arr);
                        }

                    }

                    return $data;
            })
            ->setDisabledWhenEmpty(true)
            ->setPrompt('--- Vyberte kontrolu ---');
        
        
        
        $form->addSubmit('submit', 'Přidat Body');

        $form->onSuccess[] = [$this, 'pbSuccess'];
        
        $form->onValidate[] = [$this, 'pbValidate'];

        return $form;
    }
    
    public function pbSuccess($form, $values){
        $id = $this->getParameter("id");
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
       // bdump($etapa);
        $zavod = $balik;
        
        $zavodnik =  current($this->em->getRepository("App\Zavodnik")
                                ->findBy(array("cislo" => $values->cislo, "zavod" => $zavod->id)));
        
        $zavod = $this->em->getRepository("App\Zavod")->find($values->etapa);
        $etapa = $this->em->getRepository("App\Etapa")->find($values->kontrola);
                
        $body = new Body();
        $body->zavodnik = $zavodnik;
        $body->zavod = $zavod;
        $body->etapa = $etapa;
        $body->body =  $values->body;
        
        $this->em->persist($body);
        $this->em->flush();
        
        $this->flashMessage("Body úspěšně přidány závodníkovi.");
        $this->redirect("Admin:balik", $id);
    }
    
    public function pbValidate($form, $values){
        $id = $this->getParameter("id");
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        $zavod = $balik;
        
        $zavodnik =  current($this->em->getRepository("App\Zavodnik")
                                ->findBy(array("cislo" => $values->cislo, "zavod" => $zavod->id)));
        
        if (!$zavodnik){
            $form->addError("Závodník s tímto číslem v závodě nefiguruje.");
            $this->flashMessage("Závodník s tímto číslem v závodě nefiguruje.", "danger");
        }
    }
    
    public function createComponentOpravitCas(){
        $form = new Form;  
        
        $form->addText('cislo', 'Číslo závodníka:');
        
        $form->addText('cas', 'Opravit čas');
        
        $form->addSubmit('submit', 'Uložit čas');

        $form->onSuccess[] = [$this, 'ocasSuccess'];

        return $form;
    }
    
    public function ocasSuccess($form, $values){
        $id = $this->getParameter("id");
        
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        
        $zavodid = $etapa->ref->balik;
        
        $zavodnik = current($this->em->getRepository("App\Zavodnik")
                                ->findBy(array("cislo" => $values->cislo, "zavod" => $zavodid)));
        
        bdump($zavodnik);
        
        if (isset($zavodnik->casy)){
        
            $cExist = $zavodnik->casy;
            
            if (count($cExist) > 0){

                foreach($cExist as $c){
                    
                    if ($c->etapa->id == $id){
                        $historie = new Historie();
                        $historie->cislo = $values->cislo;
                        $historie->puvodniCas = $c->cas;
                        $historie->zmenenyCas = new \DateTime($values->cas);
                        $historie->etapa = $etapa;

                        $c->cas = new \DateTime($values->cas);

                        $this->em->persist($historie);
                        $this->em->merge($c);
                        $this->em->flush();
                    }
                }
                
                $this->flashMessage("Čas byl úspěšně opraven");
                $this->redirect("Admin:etapa", $id);
            }
        }
    }
    
    private function projelEtapou($id){
         $projel = $this->em->createQuery('SELECT a FROM App\Projel a WHERE a.etapa = ?1 ORDER BY a.id DESC')
                      ->setParameter(1,$id)
                      ->getResult();
         
         foreach($projel as $p){
             $zavodnici[] = $p->zavodnik;
         }
         
         if (isset($zavodnici)){
             return $zavodnici;
         } else {
             return array();
         }
    }
    
    public function actionEtapa($id){
        
        $sess = $this->getSession()->getSection("etapa");
        $sess->id = $id;
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $this->template->etapa = $etapa;
        $u = $this->getUser();
        $user = $this->em->getRepository("App\User")->find($u->getId());
        
        if ($u->isInRole("kontrola") && $user->etapa->id !== intval($id)){
            $this->redirect("Admin:etapa", $user->etapa->id);
        }
        
        if(isset($etapa->historie)){

            foreach($etapa->historie as $polozka){


                $zavodnik = current($this->em->getRepository("App\Zavodnik")
                                    ->findBy(array("cislo" => $polozka->cislo)));

                $historie[] = array("historie" => $polozka, "zavodnik" => $zavodnik);

            }
        }
        
        if (isset($historie)){
           $this->template->historie = $historie; 
        }
        
        $this->template->projetiZavodnici = $this->projelEtapou($id);
        $this->template->jeUkoncena = $etapa->jeUkoncena;
        $this->template->externi = $etapa->externi;
        $this->template->bezcasu = $etapa->bezcasu;
        $this->template->obyc = $etapa->obyc;
        $this->template->jizdaPravidelnosti = $etapa->jizdaPravidelnosti;
        $this->template->dovednostniKontrola = $etapa->dovednostniKontrola;
        $this->template->etapa = $etapa;

        $zavod = $etapa->ref;

        $startZavodu = $zavod->start;
       
        $etapaid = intval($id);
        //$er = $this->rs->getEtapaResults($etapaid);
        $this->rs->setBalik($etapa->ref->balik);
        $this->rs->setPresenter($this);
        $this->rs->setZavod($zavod);
        $this->rs->historieHelper($etapa);
        $pbr = $this->rs->pezinskaBabaVysledky();
        $jps = $this->rs->jizdaPravidelnostiVysledky();
        $dk = $this->rs->demontazKolaVysledky();
        
        $this->template->dk = $dk;
        $this->template->jps = $jps;
        $this->template->pbr = $pbr;
     
        //$this->template->casy = $er;
        $this->template->start = $startZavodu;
        
    }
    
    public function handleRefresh($id){ 
       
       if ($this->isAjax()){
           $etapa = $this->em->getRepository("App\Etapa")->find($id);
           $zavod = $etapa->ref;
           $this->rs->setBalik($zavod->balik);
           $this->rs->setPresenter($this);
           $this->rs->setZavod($zavod);
           $this->rs->historieHelper($etapa);
           $this->redrawControl('vysledky');
       }
    }
    
    public function createComponentStanovenyCas(){
        $form = new Form;

        $form->addText('cislo', 'Číslo závodníka:');
        
        $form->addText('stanovenyCas', 'Stanovit čas dojezdu:');
        
        $form->addSubmit('submit', 'Uložit čas');

        $form->onSuccess[] = [$this, 'stanovenyCasSuccess'];

        return $form;
    }
    
    public function stanovenyCasSuccess($form, $values){
        $id = $this->getParameter("id");
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref->balik;
        $zavodnik = current($this->em->getRepository("App\Zavodnik")->findBy(array("cislo" => $values->cislo, "zavod" => $zavod->id)));
        
        $exist = $this->em->createQuery('SELECT a FROM App\StanovenyCasKontroly a WHERE a.zavodnik = ?1 AND a.etapa = ?2')
                      ->setParameter(1,$values->cislo)
                      ->setParameter(2,$etapa->id)
                      ->getResult();
        
        if (!$exist){
           
            $stanovenyCas = new StanovenyCasKontroly();
            $stanovenyCas->zavodnik = $zavodnik;
            $stanovenyCas->stanovenyCas = new \DateTime($values->stanovenyCas);
            $stanovenyCas->etapa = $etapa;
            $this->em->persist($stanovenyCas);
            $this->em->flush();
            $this->flashMessage("Čas dojezdu závodníka byl stanoven.");
            $this->redirect("Admin:etapa", $id);
        }
    }
    
    public function actionStanovitCas($id){
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $this->template->etapa = $etapa;
    }
    

    public function actionDefault(){
        $this->redirToEtapa();
        $this->template->user = $this->getUser();
    }
    
    
    /** @var \AddUserForm @inject */
    public $form;

    public function createComponentAddUziv(){
    	$form = $this->form->create();
    	$form->addSubmit("submit", "Přidej uživatele");
    	$form->onSuccess[] = [$this, "addSuccess"];

    	return $form;
    }

    public function addSuccess(Form $form, $values){

        $this->flashMessage("Uživatel úspěšně přidán do systému.");
    	$this->redirect('Admin:uzivatele');
    }

    public function actionOdebrat($id){

    	if ($id){
            $usr = $this->em->getRepository('App\User')->find($id);
            $this->em->remove($usr);
            $this->em->flush();

            $this->flashMessage("Uživatel byl odebrán.");
            $this->redirect('Admin:uzivatele');
    	}
    }
    
    public function redirToEtapa(){
        if ($this->getUser()->isInRole("kontrola")){
            $u = $this->getUser();
            $user = $this->em->getRepository("App\User")->find($u->getId());
            $this->redirect("Admin:etapa", $user->etapa->id);
        }
    }

    public function actionUzivatele(){
                
        $this->redirToEtapa();
        
        $baliky = $this->em->getRepository('App\BalikZavodu')->findAll();
        $zavody = $this->em->getRepository('App\Zavod')->findAll();
        $etapy = $this->em->getRepository('App\Etapa')->findAll();
    	$usr = $this->em->getRepository('App\User')->findAll();
        
        if (!$baliky || !$zavody || !$etapy){
            $this->template->render = false;
        } else {
            $this->template->render = true;
        }
        
    	$this->template->usr = $usr;
    }
    
    public function actionSmazZavodnika($balik, $id){
        
        $zavodnik = $this->em->getRepository('App\Zavodnik')->find($id);
        
        $this->em->remove($zavodnik);

        $this->em->flush();
        $this->redirect("Admin:balik", $balik);
    }
    
    public function actionSmazZavod($id, $idBalik){
        
        $zavodnik = $this->em->getRepository('App\Zavod')->find($id);
        
        $this->em->remove($zavodnik);

        $this->em->flush();
        $this->redirect("Admin:balik", $idBalik);
    }
    
    public function actionUE($id, $zavod){
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $this->em->getRepository("App\Zavod")->find($zavod);
        $this->getSession()->getSection("zavod")->zavod = $zavod;
        $this->getSession()->getSection("etapa")->etapa = $etapa;
        $this->redirect("Admin:upravEtapu");
    }
    
    public function renderUpravEtapu(){
        $this->template->zavod = $this->getSession()->getSection("zavod")->zavod;
        $this->template->etapa = $this->getSession()->getSection("etapa")->etapa;
    }
    
    public function actionUZ($id, $balik){
        $zavodnik = $this->em->getRepository("App\Zavodnik")->find($id);
        $balik = $this->em->getRepository("App\BalikZavodu")->find($balik);
        $this->getSession()->getSection("balik")->balik = $balik;
        $this->getSession()->getSection("zavodnik")->zavodnik = $zavodnik;
        $this->redirect("Admin:upravZavodnika");
    }
    
    public function renderUpravZavodnika(){
        $this->template->balik = $this->getSession()->getSection("balik")->balik;
        $this->template->zavodnik = $this->getSession()->getSection("zavodnik")->zavodnik;
    }
    
    public function createComponentUpravZavod(){
        
        $id = $this->getParameter("id");
        
        $zavod = $this->em->getRepository("App\Zavod")->find($id);
        
        $form = new Form;
        
        $form->setRenderer(new \Bs3FormRenderer());
        
        $form->addText('jmeno', 'Jméno etapy:')->setDefaultValue($zavod->name);
        
        $form->addSubmit('submit', 'Uložit změny');

        $form->onSuccess[] = [$this, 'uprzSuccess'];

        return $form;
    }
    
    public function uprzSuccess($form, $values){
        $zavod = $this->getSession()->getSection("zavod")->zavod;
        $zavod->name = $values->jmeno;
       
        $this->em->merge($zavod);
        $this->em->flush();
        $this->flashMessage("Etapa byla upravena");
        $this->redirect("Admin:zavod",$zavod->id);
    }
    
    
    public function createComponentUpravEtapu(){
        
        $etapa = $this->getSession()->getSection("etapa")->etapa;
        
        $form = new Form;
        
        $form->setRenderer(new \Bs3FormRenderer());
        
        $form->addText('jmeno', 'Jméno etapy:')->setDefaultValue($etapa->name);
        
        $form->addSubmit('submit', 'Uložit změny');

        $form->onSuccess[] = [$this, 'ueSuccess'];

        return $form;
    }
    
    public function ueSuccess($form, $values){
        $etapa = $this->getSession()->getSection("etapa")->etapa;
        $zavod = $this->getSession()->getSection("zavod")->zavod;
        $etapa->name = $values->jmeno;
       
        $this->em->merge($etapa);
        $this->em->flush();
        $this->flashMessage("Kontrola byla upravena");
        $this->redirect("Admin:zavod",$zavod->id);
    }
    
    public function createComponentUpravZavodnika(){
        
        $zavodnik = $this->getSession()->getSection("zavodnik")->zavodnik;
        
        $form = new Form;
        
        $form->setRenderer(new \Bs3FormRenderer());

        $form->addText('cislo', 'Číslo závodníka:')->setDefaultValue($zavodnik->cislo);
        
        $form->addText('jmeno', 'Jméno závodníka:')->setDefaultValue($zavodnik->jmeno);
        
        $form->addText('znacka' ,'Značka vozu:')->setDefaultValue($zavodnik->znacka);
        
        $form->addText('objem', 'Objem:')->setDefaultValue($zavodnik->objem);
        
        $form->addSubmit('submit', 'Uložit změny');

        $form->onSuccess[] = [$this, 'uzSuccess'];

        return $form;
    }
    
    public function uzSuccess($form, $values){
        $id = $this->getSession()->getSection("balik")->balik->id;
        $zavodnik = $this->getSession()->getSection("zavodnik")->zavodnik;
        $zavod = $this->getSession()->getSection("zavod")->zavod;
        $zavodnik->cislo = $values->cislo;
        $zavodnik->jmeno = $values->jmeno;
        $zavodnik->znacka = $values->znacka;
        $zavodnik->objem = $values->objem;

       
        $this->em->merge($zavodnik);
        $this->em->flush();
        $this->flashMessage("Závodník byl upraven.");
        $this->redirect("Admin:balik",$id);
    }
    
    public function createComponentBalik(){
        $form = new Form;
        
        $form->setRenderer(new \Bs3FormRenderer());

        $form->addText('jmeno', 'Jméno závodu:');
        
        $form->addSubmit('submit', 'Uložit');

        $form->onSuccess[] = [$this, 'balikSuccess'];

        return $form;
    }
    
    public function balikSuccess($form, $values){
        $balik = new BalikZavodu();
        $balik->balikname = $values->jmeno;
        
        $this->em->persist($balik);
        $this->em->flush();
    }
    
    public function renderBaliky(){
       $baliky = $this->em->getRepository("App\BalikZavodu")->findAll();
        
        $this->template->baliky = $baliky;
    }
    
    public function actionBalik($id){
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        $zavody = $this->em->getRepository("App\Zavod")->findBy(array("balik" => $id));
        $teamy = $this->em->getRepository("App\ZnackovyTym")->findBy(array("balik" => $id));
        $this->getSession()->getSection("balik")->balik = $id;
        
        $this->rs->setPresenter($this);
        $this->rs->setBalik($balik);
        $this->rs->setZavody($zavody);
        
        $this->rs->newZavodHelper();
        
        $this->template->teamy = $teamy;
        $this->template->zavody = $zavody;
        $this->template->balik = $balik;
    }
    
    public function handleRefreshMe($id){ 
       $zavody = $this->em->getRepository("App\Zavod")->findBy(["balik" => $id]);
       $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
       
       if ($this->isAjax()){
           $this->rs->setPresenter($this);
           $this->rs->setBalik($balik);
           $this->rs->setZavody($zavody);
           
           $this->rs->newZavodHelper();
           $this->redrawControl("vysledkyinzavod");
       }
    }
    
    public function createComponentImportZavodniku(){
        $form = new Form;
        $form->addUpload("file", "Excel file:");
        $form->addSubmit("submit", "Import");
        $form->onSuccess[] = [$this, 'izSuccess'];
        
        return $form;
    }
    
    public function izSuccess($form, $values){
        
        $id = $this->getParameter("id");
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        
        $file = $values->file->getTemporaryFile();
        $inputFileType = "Xlsx";
      //  $filterSubset = new MyReadFilter();
                
        $reader = IOFactory::createReader($inputFileType);
        
        $reader->setLoadSheetsOnly("List1");
        
        //$reader->setReadFilter($filterSubset);
        
        $spreadsheet = $reader->load($file);
        
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $worksheets[$worksheet->getTitle()] = $worksheet->toArray();
        }
        bdump($worksheets);
        
        $cnt = 0;
        
        foreach($worksheets["List1"] as $polozka){
            if ($cnt >= 2){
                
                if (!is_null($polozka[0]) && !is_null($polozka[1]) && !is_null($polozka[2])
                        && !is_null($polozka[14]) && !is_null($polozka[16])){
                    
                    if (!is_string($polozka[0])){
                        $zavodnik = new Zavodnik();
                        $zavodnik->cislo = $polozka[0];
                        $zavodnik->jmeno = $polozka[1] . " " . $polozka[2];
                        $zavodnik->znacka = $polozka[14];
                        $zavodnik->objem = $polozka[16];
                        $zavodnik->zavod = $balik;

                        $this->em->persist($zavodnik);
                    }
                }
            }
            $cnt++;
        }
        
        $this->em->flush();
        $this->redirect("Admin:balik", $id);
        
       // bdump($spreadsheet);
    }
    
    public function createComponentImportKontrol(){
        $form = new Form;
        $form->addUpload("file", "Excel file:");
        $form->addSubmit("submit", "Import");
        $form->onSuccess[] = [$this, 'ikSuccess'];
        
        return $form;
    }
    
    public function ikSuccess($form, $values){
        $id = $this->getParameter("id");
        $zavod = $this->em->getRepository("App\Zavod")->find($id);
        
        $file = $values->file->getTemporaryFile();
        $inputFileType = "Xlsx";
                
        $reader = IOFactory::createReader($inputFileType);
        
        //$reader->setLoadSheetsOnly("List1");
        
        
        $spreadsheet = $reader->load($file);
        
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $worksheets[$worksheet->getTitle()] = $worksheet->toArray();
        }
        
        $cnt = 0;
        $etapaId = 0;
        
        foreach($worksheets["List1"] as $polozka){
            
            $withStart = false;
            
            if (count($polozka) == 4){
                $withStart = true;
            }
            
            if ($cnt == 1){
                bdump($polozka[2]);
                $etapa = new Etapa();
                
                if ($withStart){
                    $etapa->name = $polozka[2];
                } else {
                    $etapa->name = $polozka[1];
                }
                
                $etapa->externi = false;
                $etapa->bezcasu = false;
                $etapa->obyc = true;
                $etapa->jizdaPravidelnosti = false;
                $etapa->dovednostniKontrola = false;
                $etapa->jeUkoncena = false;
                        
                $etapa->ref  = $zavod;
                $this->em->persist($etapa);
                $this->em->flush();
                $etapaId = $etapa->getId();
            }
            
            if ($cnt > 1){
                
               if ($withStart){
                    $stanovenyCas = new StanovenyCasKontroly();
                    $cisloZavodnika = $polozka[0];
                    $start = $polozka[1];
                    $dtstart = \DateTime::createfromformat('H:i',$start);
                    $prijezd = $polozka[2];
                    $dtprijezd = \DateTime::createfromformat('H:i',$prijezd);
                    $odjezd = $polozka[3];
                    $dtodjezd = \DateTime::createfromformat('H:i',$odjezd);
               } else {
                    $stanovenyCas = new StanovenyCasKontroly();
                    $cisloZavodnika = $polozka[0];
                    $start = null ;//$polozka[1];
                    $dtstart = null;//\DateTime::createfromformat('H:i',$start);
                    $prijezd = $polozka[1];
                    $dtprijezd = \DateTime::createfromformat('H:i',$prijezd);
                    $odjezd = $polozka[2];
                    $dtodjezd = \DateTime::createfromformat('H:i',$odjezd);
               }
               
               $zavodnik = $this->em->getRepository("App\Zavodnik")->findBy(array("cislo" => $cisloZavodnika));
               
               $etapa = $this->em->getRepository("App\Etapa")->find($etapaId);
               
               if (!empty($zavodnik)){
                    $stanovenyCas->zavodnik = $zavodnik[0];
                    $stanovenyCas->etapa = $etapa;
                    $stanovenyCas->start = $dtstart;
                    $stanovenyCas->prijezd = $dtprijezd;
                    $stanovenyCas->odjezd = $dtodjezd;
                    $this->em->persist($stanovenyCas);
               }
            }
        
           
           $cnt++;
           
        }
        
        $this->em->flush();
        $this->redirect("Admin:zavod", $id);
    }
    
    public function handleUkoncitKontrolu($id){
        
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref;
        
         $casy =  $this->em->createQuery('SELECT a FROM App\Cas a WHERE a.etapa = ?1 ORDER BY a.cas DESC')
                      ->setParameter(1,$id)
                      ->getResult();
         
         foreach($casy as $cas){
             $nots[] = $cas->zavodnik->id;
         }
         
         if (isset($nots)){
         
            $qb = $this->em->createQueryBuilder();
            $penalizovani = $qb->select('a')
                ->from('App\Zavodnik', 'a')
                ->where($qb->expr()->notIn('a.id', $nots))
                ->getQuery()
                ->getResult();
        } else {
            
            $qb = $this->em->createQueryBuilder();
            $penalizovani = $qb->select('a')
                ->from('App\Zavodnik', 'a')
                ->getQuery()
                ->getResult();
        }
         
         foreach($penalizovani as $p){
             $body = new Body();
             $body->body = 15000;
             $body->etapa = $etapa;
             $body->zavod = $zavod;
             $body->zavodnik = $p;
             
             $cas = new Cas();
             $cas->zavod = $zavod;
             $cas->etapa = $etapa;
             $cas->zavodnik = $p;
             $dateTime = new \DateTime('0000-00-00 00:00:00');
             $cas->cas = $dateTime;
             
             $this->em->persist($cas);
             $this->em->persist($body);
         }
         
         $etapa->jeUkoncena = true;
         $this->em->persist($etapa);
         
         $this->em->flush();
         $this->redirect("Admin:etapa", $id);
    }
    
    public function createComponentDSQ(){
        
        $form = new Form();
        $form->addText("cislo", "Číslo závodníka:");
        $form->addSubmit("submit", "Diskvalifikovat");
        $form->onSuccess[] = [$this, "dsqSuccess"];
        return $form;
    }
    
    public function dsqSuccess($form, $values){
        $id = $this->getParameter("id");
        $zavod = $this->em->getRepository("App\Zavod")->find($id)->balik;
        
        $zavodnik = current($this->em->getRepository("App\Zavodnik")
                         ->findBy(array("cislo" => $values->cislo, "zavod" => $zavod->id)));
        
        $zavodnik->status = "DSQ";
        
        $this->em->persist($zavodnik);
        $this->em->flush();
    }
    
    public function createComponentProjel(){
        $form = new Form();
        $form->addText("cislo", "Číslo závodníka:");
        $form->addSubmit("submit", "Potvrdit průjezd");
        $form->onSuccess[] = [$this, "projelSuccess"];
        return $form;
    }
    
    public function projelSuccess($form, $values){
        $id = $this->getParameter("id");
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref->balik;
        
        $zavodnik = current($this->em->getRepository("App\Zavodnik")
                         ->findBy(array("cislo" => $values->cislo, "zavod" => $zavod->id)));
        
        $projel = new Projel();
        $projel->zavodnik = $zavodnik;
        $projel->etapa = $etapa;
        $projel->projel = true;
        
        $this->em->persist($projel);
        $this->em->flush();
        $this->redirect("Admin:etapa", $id);
    }
    
    public function handleUkoncitProjeti($id){
        $projel = $this->em->getRepository("App\Projel")->findBy(array("etapa" => $id));
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref;
        
        foreach($projel as $p){
            $nots[] = $p->zavodnik->id;
        }
        
        if (isset($nots)){
         
            $qb = $this->em->createQueryBuilder();
            $penalizovani = $qb->select('a')
                ->from('App\Zavodnik', 'a')
                ->where($qb->expr()->notIn('a.id', $nots))
                ->getQuery()
                ->getResult();
        } else {
            
            $qb = $this->em->createQueryBuilder();
            $penalizovani = $qb->select('a')
                ->from('App\Zavodnik', 'a')
                ->getQuery()
                ->getResult();
        }
         
         foreach($penalizovani as $p){
             $body = new Body();
             $body->body = 15000;
             $body->etapa = $etapa;
             $body->zavod = $zavod;
             $body->zavodnik = $p;
    
             $this->em->persist($body);
         }
         
         $etapa->jeUkoncena = true;
         $this->em->persist($etapa);
         
         $this->em->flush();
         $this->redirect("Admin:etapa", $id);
    }
    
    public function createComponentDemontaz(){
        $form = new Form();
        
        $form->addText("cislo", "Číslo závodníka");
        $form->addText("sekundy", "Počet sekund");
        $form->addSubmit("submit", "Potvrď čas");
        $form->onSuccess[] = [$this, "demontazSuccess"];
        
        return $form;
    }
    
    public function demontazSuccess($form, $values){
        $id = $this->getParameter("id");
        $etapa = $this->em->getRepository("App\Etapa")->find($id);
        $zavod = $etapa->ref->balik;
        
        $zavodnik = current($this->em->getRepository("App\Zavodnik")
                         ->findBy(array("cislo" => $values->cislo, "zavod" => $zavod->id)));
        
        $sekundy = intval($values->sekundy);
        $trBody = $sekundy * 10;
        
        $demontaz = new DemontazKola();
        $demontaz->zavodnik = $zavodnik;
        $demontaz->casDemontaze = $sekundy;
        $demontaz->trBody = $trBody;
        
        $body = new Body();
        $body->body = $trBody;
        $body->etapa = $etapa;
        $body->zavod = $etapa->ref;
        $body->zavodnik = $zavodnik;
        
        $this->em->persist($body);
        $this->em->persist($demontaz);
        $this->em->flush();
        
        $this->redirect("Admin:etapa", $id);
    }
    
    public function createComponentVytvoritTeam(){
        $form = new Form();
        $form->addText("jmenoTymu", "Jméno Týmu:");
        $form->addSubmit("submit", "Vytvořit Tým");
        $form->onSuccess[] = [$this, "vytvoritTeamSuccess"];
        return $form;
    }
    
    public function vytvoritTeamSuccess($form, $values){
        $id = $this->getParameter("id");
        $session =  $this->getSession()->getSection("balik_zavod");
        $session->id = $id;
        $balik = $this->em->getRepository("App\BalikZavodu")->find($id);
        $zt = new ZnackovyTym();
        $zt->jmenoTymu = $values->jmenoTymu;
        $zt->balik = $balik;
        $this->em->persist($zt);
        $this->em->flush();
        $this->redirect("Admin:team", $zt->getId());
    }
    
    public function createComponentPridatDoTeamu(){
        $form = new Form();
        $form->addText("cislo", "Číslo závodníka");
        $form->addSubmit("submit", "Přidej do teamu");
        $form->onSuccess[] = [$this, "pridatDoTeamuSuccess"];
        return $form;
    }
    
    public function pridatDoTeamuSuccess($form, $values){
        $idbalik = $this->getSession()->getSection("balik_zavod")->id;
        $idTeam = $this->getParameter("id");
        $team = $this->em->getRepository("App\ZnackovyTym")->find($idTeam);
        $zavodnik = $this->em->getRepository("App\Zavodnik")->findBy(array("cislo" => $values->cislo, "zavod" => $idbalik));
        $zavodnik = current($zavodnik);
        $zavodnik->tym = $team;
        $this->em->persist($zavodnik);
        $this->em->flush();
        $this->redirect("Admin:team", $idTeam);
    }
    
    public function actionTeam($id){
        $zavodnici = $this->em->getRepository("App\Zavodnik")->findBy(array("tym" => $id));
        $team = $this->em->getRepository("App\ZnackovyTym")->find($id);
        
        $this->template->balik = $team->balik;
        $this->template->team = $team;
        $this->template->zavodnici = $zavodnici;
    }
    
}


