<?php

require_once 'controleurAuthentification.php';
require_once 'controleur2048.php';

class Routeur {
    private $ctrlAuthentification;

    public function __construct() {
        $this->ctrlAuthentification = new ControleurAuthentification();
        $this->ctrl2048 = new Controleur2048();
    }

    public function routerRequete() {
        session_start();

        
        //Choix du controleur en fonction de si oui ou non on s'est connecté avec un login
        if (!isset($_SESSION["pseudo"])) {
            $this->ctrlAuthentification->accueil();
     
        } else {
            $this->ctrl2048->accueil();
        }

    }

}


?>
