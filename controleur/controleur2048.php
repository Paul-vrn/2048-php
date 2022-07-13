<?php

require_once PATH_VUE."/vue.php";
require_once PATH_MODELE."/BDConnexion.php";


class Controleur2048 {

    private $vue;
    private $connexion;
    private $partie;

    function __construct() {
        $this->vue = new Vue();
        $this->connexion = new BDConnexion();
        $this->partie = new Partie();
    }

    function accueil() {
        
        //Permet de se déconnecter du site
        if(isset($_GET["logout"])){
            session_destroy();
            $this->vue->login();
            return;
        }

        //Initialisation de la partie :
        if(!isset($_GET["terminer"])){
            if(!isset($_SESSION["partie"])){
                $game = $this->connexion->getLastPartie($_SESSION["pseudo"]);
                if ($game["gagne"] == -1){ //Récupère la partie non fini dans la database
                    $_SESSION["partie"] = unserialize($game["gameState"]);
                    $_SESSION["id"] = $game["id"];
                    $_SESSION["score"] = $game["score"];
                } else { // Création d'une nouvelle partie
                    $_SESSION["partie"] = $this->partie->getPlateau();
                    $_SESSION["id"] = $this->connexion->createPartie($_SESSION["pseudo"], $_SESSION["partie"]);
                    $_SESSION["score"] = $this->partie->getscore();
                }
            } else {
                $this->partie->setPlateau($_SESSION["partie"]);
                $this->partie->setScore($_SESSION["score"]);
            }
        }

        //Pour arriver sur la view de résultat et terminer la partie en cours :
        if($this->partie->bestNombre() == 2048 || isset($_GET["terminer"])){
            $gagne = 0;
            if($this->partie->bestNombre() == 2048){
                $gagne = 1;
            }
            if(isset($_SESSION["partie"])){
                $this->connexion->updatePartie($_SESSION["id"], $_SESSION["score"], $gagne, $_SESSION["partie"]);
                unset($_SESSION["partie"]);
            }
            $data["top3"] = $this->connexion->getClassement();
            $data["userInfo"] = $this->connexion->getInfo($_SESSION["pseudo"]);
            $data["game"] = $this->connexion->getLastPartie($_SESSION["pseudo"]);
            $this->vue->PageResultat($data);
            return;
        }

        //Pour revenir un coup en arrière 
        if(isset($_GET["reload"]) && isset($_SESSION["backup"])){
            $_SESSION["partie"] = $_SESSION["backup"];
            $_SESSION["score"] = $_SESSION["backupScore"];
            $this->connexion->updatePartie($_SESSION["id"], $_SESSION["score"], -1, $_SESSION["partie"]);
            $this->vue->PageJeu();
            return;
        }
        
        //Pour effectuer un déplacement
        if(isset($_GET["mvt"])){
            $_SESSION["backupScore"] = $this->partie->getScore();
            $est_terminee = $this->partie->mouvement($_GET["mvt"]);
            $_SESSION["partie"] = $this->partie->getPlateau();
            $_SESSION["backup"] = $this->partie->getbackupPlateau();
            $_SESSION["score"] = $this->partie->getScore();
        
            if (!$est_terminee) { 
                $this->connexion->updatePartie($_SESSION["id"], $_SESSION["score"], -1, $_SESSION["partie"]);
                $this->vue->PageJeu();
            } else {
                //partie terminé
                $this->connexion->updatePartie($_SESSION["id"], $_SESSION["score"], 0, $_SESSION["partie"]);
                $this->vue->PageResultat();
            }
            return;
        } else {
            $this->vue->PageJeu();
            return;
        }
    $this->vue->PageJeu();
    }

}

?>