<?php 

class Partie {

    private $plateau;
    private $backupPlateau;
    private $score;


    public function __construct(){
        $this->plateau = array(
            array(0,0,0,0),            
            array(0,0,0,0),
            array(0,0,0,0),
            array(0,0,0,0));
        
        $this->backupPlateau = $this->plateau;
        $this->genereNewNumber();
        $this->score = 0;
    }
    
    public function getPlateau(): array {
        return $this->plateau;
    }
    public function getbackupPlateau(): array {
        return $this->backupPlateau;
    }
    public function getScore(): int {
        return $this->score;
    }
    public function setPlateau($newPlateau) {
        $this->plateau = $newPlateau;
    }
    public function setScore($score){
        $this->score = $score;
    }

    /**
     * Fonction qui permet de faire un des trois mouvements
     * return true si le plateau après le mouvement ne permet plus de jouer (partie perdu)
     * false dans le cas contraire
     */
    public function mouvement($mvt): bool {
        $this->backupPlateau = $this->plateau;
        switch ($mvt) {
            case 'LEFT':
                $this->gauche();
            break;
            case 'RIGHT':
                $this->droit();
            break;
            case 'UP':
                $this->haut();
            break;
            case 'DOWN':
                $this->bas();
            break;  
        }
        if(!$this->identique($this->plateau, $this->backupPlateau)){
            $this->genereNewNumber();
        }
        return $this->est_perdu();
    }
    
    /**
     * Fonction qui va générer un nouveau nombre sur le plateau
     * 20% de chanque que ça soit un "4" et 80% un "2" 
     */
    public function genereNewNumber(){
        if($this->full($this->plateau)){
            return;
        }
        $l = rand(0,3);
        $c = rand(0,3);
        while ($this->plateau[$l][$c] != 0) {
            $l = rand(0,3);
            $c = rand(0,3);
        }
        if (rand(1,10) < 3) { 
            $this->plateau[$l][$c] = 4;
        } else {
            $this->plateau[$l][$c] = 2;
        }
    }


    /**
     * Ces quatres fonctions permettent de faire les déplacements de plateau lorsqu'on joue
     */
    public function gauche() {
        $this->addition();
    }
    public function droit() {
        $this->reverse();
        $this->addition();
        $this->reverse();
    }
    public function haut() {
        $this->transposition();
        $this->addition();
        $this->transposition();
    }
    public function bas() {
        $this->transposition();
        $this->reverse();
        $this->addition();
        $this->reverse();
        $this->transposition();
    }

    /**
     * fonction qui va additionner chaque ligne de la matrice vers la gauche 
     * (c'est pas vraiment une "addition" normal mais une propre au jeu 2048)
     */
    public function addition(){
        
        for ($l=0; $l < 4; $l++) {           
            $i = 0;
            //boucle qui décale  tous à gauche [4,0,0,2] ==> [4,2,0,0]
            for ($j=0; $j < 4; $j++) { 
                if($this->plateau[$l][$j] != 0 && $i != $j){
                    $this->plateau[$l][$i] = $this->plateau[$l][$j];
                    $this->plateau[$l][$j] = 0;
                    $i = $i + 1;
                } else if ($this->plateau[$l][$j] != 0){
                    $i = $i + 1;
                }
            }
            
            // addition des cases similaires pour une matrice de 4 cases uniquement (cas du 2048)
            if ($this->plateau[$l][0] == $this->plateau[$l][1])  {
                $this->plateau[$l][0] = $this->plateau[$l][0]*2;
                $this->score = $this->score + $this->plateau[$l][0];
                $this->plateau[$l][1] = 0;

                if ($this->plateau[$l][2] != 0 && $this->plateau[$l][3] != 0) {
                    $this->plateau[$l][1] = $this->plateau[$l][2];
                    $this->plateau[$l][2] = $this->plateau[$l][3];
                    $this->plateau[$l][3] = 0;
                } else if ($this->plateau[$l][2] != 0) {
                    $this->plateau[$l][1] = $this->plateau[$l][2];
                    $this->plateau[$l][2] = 0;
                }
            }
            if ($this->plateau[$l][1] == $this->plateau[$l][2]) {
                $this->plateau[$l][1] = $this->plateau[$l][1]*2;
                $this->score = $this->score + $this->plateau[$l][1];
                $this->plateau[$l][2] = 0;
                if ($this->plateau[$l][3] != 0) {
                    $this->plateau[$l][2] = $this->plateau[$l][3];
                    $this->plateau[$l][3] = 0;
                }
            }
            if($this->plateau[$l][2] == $this->plateau[$l][3]){
                $this->plateau[$l][2] = $this->plateau[$l][2]*2;
                $this->score = $this->score + $this->plateau[$l][2];
                $this->plateau[$l][3] = 0;
            }
        }
    }

    /**
     * fonction qui va transposer la matrice
     */
    public function transposition(){
        $newPlateau = array();
        for ($i=0; $i < 4; $i++) { 
            for ($j=0; $j < 4; $j++) { 
                $newPlateau[$i][$j] = $this->plateau[$j][$i];
            }
        }
        $this->plateau = $newPlateau;
    }

    /**
     * fonction qui va retourner la matrice
     */    
    public function reverse() {
        $newPlateau = array();
        for ($i=0; $i < 4; $i++) { 
            for ($j=0; $j < 4; $j++) { 
                $newPlateau[$i][$j] = $this->plateau[$i][3 - $j];
            }
        }
        $this->plateau = $newPlateau;
    }

    /**
     * fonction retournant true si la partie est perdu et false dans le cas contraire
     */
    public function est_perdu(): bool{
        for ($i=0; $i < 4; $i++) { 
            for ($j=0; $j < 3; $j++) { 
                if($this->plateau[$i][$j] == $this->plateau[$i][$j+1]) {
                    return false;
                }
            }
        }
        for ($i=0; $i < 4; $i++) { 
            for ($j=0; $j < 3; $j++) { 
                if($this->plateau[$j][$i] == $this->plateau[$j][$i+1]) {
                    return false;
                }
            }
        }
        //Une fois qu'on a vérifié qu'il n'y avait pas 2 cases avec le même nombre côte à côte
        // On appelle full() pour vérifier qu'il n'y a plus de case "vide" permettant de générer un nouveau chiffre 
        return $this->full($this->partie);
    }

    /**
     * fonction qui permet de savoir si la matrice est pleinne.
     */
    public function full($array): bool {
        for ($i=0; $i < 4; $i++) { 
            for ($j=0; $j < 4; $j++) { 
                if($array[$i][$j] == 0) {
                    return false;
                }
            }
        }
        return true;
    
    }
    
    /**
     * fonction qui compare si deux matrices sont identiques ou non
     * return true si elles le sont, sinon false
     */
    public function identique($array1, $array2): bool {
        for ($i=0; $i < 4; $i++) { 
            for ($j=0; $j < 4; $j++) { 
                if($array1[$i][$j] != $array2[$i][$j]) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Fonction qui retourne le plus grand chiffre sur le plateau
     */
    public function bestNombre(): int {
        $score = 0;
        for ($i=0; $i < 4; $i++) { 
            for ($j=0; $j < 4; $j++) { 
                if($this->plateau[$i][$j] > $score) {
                    $score = $this->plateau[$i][$j];
                }
            }
        }
        return $score;
       
    }
}

?>