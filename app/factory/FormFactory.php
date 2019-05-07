<?php

use Nette\Application\UI\Form;
use App\User;

class AddUserForm
{

    /**
     * @inject
     * @var \Kdyby\Doctrine\EntityManager
     */
    public $EntityManager;

    public function __construct(\Kdyby\Doctrine\EntityManager $em){
        $this->EntityManager = $em;
    }

    public function create($checkbox = NULL)
    {
        $form = new Form;
        $form->setRenderer(new \Bs3FormRenderer());

        $form->addText('email', 'E-mail:')->addRule(Form::FILLED, "Pole musí být vyplněno.")
                                            ->addRule(Form::EMAIL, "Zadejte prosím platný email.");

        $form->addPassword('pass', 'Heslo:')->addRule(Form::FILLED, "Pole musí být vyplněno.");

        $form->addPassword('pass2', 'Heslo znovu:')->addRule(Form::FILLED, "Pole musí být vyplněno.")
                                                    ->addRule(Form::EQUAL, "Hesla se neshodují.", $form['pass']);
        
        $zavody = $this->EntityManager->getRepository('App\BalikZavodu')->findAll();
       
        
        foreach($zavody as $z){
            $dp[$z->id] = $z->balikname; 
        }
        
        if (isset($dp)){
            
            $form->addCheckbox('user', 'Je omezený účet pro stanoviště kontroly')
                ->addCondition($form::EQUAL, true)
                ->toggle('zavod');
                
           
           $form->addSelect('zavod', 'Závod', $dp)
               // ->setOption('id', 'zavod')
                ->setPrompt('--- Vyberte závod ---');
           
            $form->addDependentSelectBox('etapa', 'Etapa', $form['zavod'])
                ->setDependentCallback(function ($values)  {
                    $data = new \NasExt\Forms\Controls\DependentSelectBoxData;

                    if (isset($values["zavod"])){
                         $etapy = $this->EntityManager->createQuery('SELECT e FROM App\Zavod e WHERE e.balik = ?1')
                                        ->setParameter(1, $values["zavod"])
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
            ->setPrompt('--- Vyberte etapu ---');
           // ->setOption('id', 'etapa');

           $form->addDependentSelectBox('kontrola', 'Kontrola', $form['zavod'], $form['etapa'])
                ->setDependentCallback(function ($values)  {
                    $data = new \NasExt\Forms\Controls\DependentSelectBoxData;
                    

                    if (isset($values["etapa"])){
                         $etapy = $this->EntityManager->createQuery('SELECT e FROM App\Etapa e WHERE e.ref = ?1')
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
           // ->setOption('id', 'kontrola');
        
        }


        $form->onValidate[] = [$this, 'registerFormValidate'];
        $form->onSuccess[] = [$this, 'registerFormSuccess'];

        return $form;
    }

    public function registerFormValidate(Form $form, $values){
        $r = $this->EntityManager->getRepository('App\User')->findBy(['email' => $values['email']]);
        
        if ($r){
            $form->addError("Tento email je již registrován.");
        }
        
        if (isset($values->zavod) && isset($values->etapa) && isset($values->kontrola)){
            $exist = $this->EntityManager->getRepository('App\User')->findBy(['zavod' => $values->etapa, 'etapa' => $values->kontrola]);

            if ($exist){
                $form->addError("Ucet pro toto stanoviste již existuje");
            }
        }
    }

    public function registerFormSuccess(Form $form, $values){

        $user = new User;
        $user->email = $values["email"];
        $user->password = password_hash($values["pass2"], PASSWORD_DEFAULT);
        
        if (isset($values->zavod) && isset($values->etapa) && isset($values->kontrola)){
            $zavod = $this->EntityManager->getRepository('App\Zavod')->find($values->etapa);
            $etapa = $this->EntityManager->getRepository('App\Etapa')->find($values->kontrola);

            $user->zavod = $zavod;
            $user->etapa =  $etapa;
            $user->role = "kontrola";
        } else {
            $user->role = "admin";
        }

        $this->EntityManager->persist($user);
        $this->EntityManager->flush();
    }
}