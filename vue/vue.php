<?php
require_once PATH_METIER."/2048.php";

class Vue{

  /**
   * Cette vue permet de se connecter au jeu à partir du moment où l'on possède un tuple (login,password) 
   * enregistré dans la base de donnée
   */
    function login() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
        </head>
        <body>
        <link href="css/login.css" rel="stylesheet">
        <div class="login-box">
          <h2>Login</h2>
          <form method="post" action="index.php">
            <div class="user-box">
              <input type="text" name="login" required="">
              <label>Username</label>
           </div>
            <div class="user-box">
              <input type="password" name="password" required="">
              <label>Password</label>
            </div>
            <a>
              <span></span>
              <span></span>
              <span></span>
              <span></span>
              <input type="submit" value="login">
            </a>
            <a href="index.php?register">register</a>
          </form>
        </div>
        </body>
        </html>
        <?php
    }
    /**
     * Cette vue permet de créer un compte à partir d'un tuple (login,password) qui sera enregistré dans
     * la base de donnée à condition qu'il n'existe pas déjà un tuple ayant le même login
     */
    function registerForm() {
      ?>
      <!DOCTYPE html>
        <html>
        <head>
        </head>
        <body>
        <link href="css/login.css" rel="stylesheet">

        <div class="login-box">
          <h2>Register</h2>
          <form method="post" action="index.php">
            <div class="user-box">
              <input type="text" name="loginReg" required="">
              <label>Username</label>
           </div>
            <div class="user-box">
              <input type="password" name="passwordReg" required="">
              <label>Password</label>
            </div>
            <a>
              <span></span>
              <span></span>
              <span></span>
              <span></span>
              <input type="submit" value="register">
            </a>
            <a href="index.php">login</a>
          </form>
        </div>
        </body>
        </html>
      
      
      <?php
    }    

    /**
     * Cette vue permet d'afficher la page de jeu.
     */
    function PageJeu() {
      ?>
      <!DOCTYPE html>
      <html>
      <head>
      </head>
      <body>
      <link href="css/jeu.css" rel="stylesheet">

      
          <h1 style="text-align:center;">2048</h1>

          <h2> Votre nom : <?php echo $_SESSION["pseudo"];?></h2>
          <div class="game">
            <div class="gauche">
            <div class="header">
              <span><p class="text"> Score : <?php echo $_SESSION["score"];?> </p></span>
              <span><a href="index.php?reload"><img class="image" src="images/reload.png"></a></span>
              <span> <a href="index.php?logout"><img class="image" src="images/exit.png"></a></span>
              <span><a href="index.php?terminer"><p class="text">Terminer</p></a></span>
            </div>
            
            <div class="game-container">
            <?php
              $p = $_SESSION["partie"];
            
              for($i = 0;$i < 4; $i++) {
                echo '<div class="row">';
                for($j = 0; $j < 4; $j++){
                  echo '<div><p>'.$p[$i][$j].'</p></div>';
                }
                echo '</div>';
                }
              ?>
              </div>
            </div>
            
            <div class="droit">
            <div class="grille">
              <div class="up"> <a href="index.php?mvt=UP">  <img class="image" src="images/up.png"></a></div>
              <div class="left"> <a href="index.php?mvt=LEFT">  <img class="image" src="images/left.png"></a></div>
              <div class="right"> <a href="index.php?mvt=RIGHT">  <img class="image" src="images/right.png"></a></div>
              <div class="down"> <a href="index.php?mvt=DOWN">  <img class="image" src="images/down.png"></a></div>
            </div>
            </div>
      </body>
      </html>
      <?php
    }

    /**
     * Cette vue permet d'afficher le résultat de la partie terminé ainsi que le classement
     * On peut aussi rejouer ou se déconnecter.
     */
    function PageResultat($data) {
      $userInfo = $data["userInfo"];
      $top3 = $data["top3"];
      $game = $data["game"];
      ?>
      <!DOCTYPE html>
      <html>
      <head>
      <link href="css/login.css" rel="stylesheet">

      <link href="css/resultat.css" rel="stylesheet">
      </head>
      <body>
      <div class="conteneur">

      <div class="infoJoueur">
        <h1> Résultat de la partie : 
        <?php 
          if ($game["gagne"] == 1){
            echo("gagné");
          } else {
            echo("perdu");
          }
          
        ?></h1>
        
        <h2>Score obtenu : <?php echo $game["score"];?></h2>
          <p>Info joueur : <br>
          <?php echo "Pseudo : ".$userInfo["pseudo"]."<br> Meilleur score : ". $userInfo["score"]. "<br>Nombre de parties : ".$userInfo["total_partie"]. 
          "<br>Winrate : ".intval($userInfo["somme_gagne"]/$userInfo["total_partie"]*100)."%<br>";

          ?></p>
        <br>
      </div>
      <div class="button">
        <a href="index.php?logout"><img class="image" src="images/exit.png"></a>
        <a href="index.php?replay"><button>rejouer</button></a>
      </div>

      <div class="top3">
      <div class="row titre">
        <div>Classement</div>
        <div>Pseudo</div>
        <div>Meilleur score</div>
        <div>Nombre de parties</div>
        <div>Winrate</div>
      </div>
      <?php 
      $i = 1;
      foreach ($top3 as $player){
        echo '<div class="row">';
        echo "<div>".$i."</div>
              <div>".$player["pseudo"]."</div>
              <div class='txtcenter'>".$player["score"]."</div>
              <div class='txtcenter'>".$player["total_partie"]."</div>
              <div class='txtcenter'>".intval($player["somme_gagne"]/$player["total_partie"]*100)."%</div>";
        echo "</div>";
        $i = $i +1;
      }
      ?>
      </div>
    </div>
      </body>
      </html>
      <?php
    }

    /**
     * Cette vue permet d'afficher un message d'erreur au milieu de l'écran
     */
    function error($data){
      ?>
      <link href="css/error.css" rel="stylesheet">
      <div class="popup">
        <h1> <?php echo $data["titre"]; ?>   </h1>
        <br>
        <p> <?php echo $data["message"]; ?></p>
      </div>

      <?php
    }

}


?>