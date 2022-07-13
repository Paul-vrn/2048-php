<?php

require_once PATH_VUE."/vue.php";
require_once PATH_MODELE."/BDConnexion.php";


class ControleurAuthentification {
    private $vue;
    private $connexion;

    function __construct() {
        $this->vue = new Vue();
        $this->connexion = new BDConnexion();
    }

    function accueil() {  

        //Permet de passer de la vue login à la vue register
        if(isset($_GET["register"])) {
            $this->vue->registerForm();
            return;
        }
        
        //vue lorsqu'on a pas encore entré le login (quand on arrive sur la page la première fois)
        if(!isset($_POST["login"]) && !isset($_POST["loginReg"])){
            $this->vue->login();
            return;
        }


        //Permet de se connecter et d'aller sur la vue PageJeu
        if(isset($_POST["login"]) && isset($_POST["password"])){
            if($this->connexion->login($_POST["login"], $_POST["password"])) {
                $_SESSION["pseudo"] = $_POST["login"];
                //Une fois la variable de session créé, on refresh la page pour que le routeur utilise le controleur2048
                //à la place du controleurAuthentification vu que la variable de session pseudo sera set
                header("Refresh:0");
            } else {
                $this->vue->login();
                $data["titre"] = "Error login";
                $data["message"] = "mot de passe incorrecte";
                $this->vue->error($data);
            }
            return;
        }

        //Lorsqu'on crée un compte
        if(isset($_POST["loginReg"])){
            //le @ permet d'enlever les messages de warning (en l'occurence cela permet d'enlever le warning généré
            //par l'utilisation de la fonction hash())
            if (@$this->connexion->register($_POST["loginReg"], $_POST["passwordReg"])){
                $_SESSION["pseudo"] = $_POST["loginReg"];
                header("Refresh:0");
            } else {
                $this->vue->registerForm();
                $data["titre"] = "Error register";
                $data["message"] = "username déjà utilisé";
                $this->vue->error($data);
            }
            return;
        } 
    
    }

}



?>