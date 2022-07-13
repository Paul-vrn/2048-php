<?php
require_once "BDException.php";

class BDConnexion{


	private $connexion;
	private static $instancePDO;


// constructeur qui permet de créer la connexion au sgbd
	public function __construct(){
		$chaine="mysql:host=".HOST.";dbname=".BD;
		try{
			$this->connexion = new PDO($chaine,BD,PASSWORD);
			$this->connexion->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e){
         throw new ConnexionException("problème de connexion".$e);

		}
	}

/** méthode qui implémente le patron singleton qui ne permet d'utiliser qu'une instance d'objet de type PDO (unze seule connexion au sgbd)
@return la seule instance d'objet PDO
*/
public static function getInstance(): BDConnexion{  
	if(is_null(self::$instancePDO)){
		self::$instancePDO = new BDConnexion();
	}
	return self::$instancePDO;
}

/** méthode qui permet de retourner une connexion
@return un objet de type PDO
*/
public function getConnexion(): PDO{
	return $this->connexion;
}


/** méthode qui permet de gérer la déconnexion au sgbd
*/
public function deconnexion(): PDO{
	$this->connexion=null;
}

/**
 * Fonction qui va vérifier si le couple (login,password) et bien référencé dans la table JOUEURS
 * return true si c'est le cas, sinon false
 */
public function login($login, $password): bool {
	try {
		$statement = $this->connexion->prepare("select password from JOUEURS where pseudo = ?;");
		$statement->bindParam(1,$login);
		$statement->execute();
		$resultat = $statement->fetch(PDO::FETCH_ASSOC);
		//On crypt le mdp pour pouvoir le comparer au mdp de la base de donnée (qui est crypté)
		if (crypt($password, $resultat["password"]) == $resultat["password"])
			return true;
		return false;
		
	} catch (PDOException $e) {
		$this->deconnexion();
		throw new ConnexionException("problème de connexion à la table".$e);
	}
}

/**
 * Fonction qui va enregistrer dans la table JOUEURS un couple (login,password)
 * tout en cryptant le password
 * return true si l'enregistrement a bien eu lieu, sinon false
 */
public function register($login, $password): bool {	
	try {
		$statement = $this->connexion->prepare("INSERT INTO JOUEURS (pseudo, password) VALUES (?,?);");
		$statement->bindParam(1,$login);
		$hash = crypt($password);
		$statement->bindParam(2,$hash);
		return $statement->execute();
	} catch (PDOException $e) {
		return false;
	}
	return false;
}

/**
 * Fonction qui va enregistrer dans la base de donnée une partie créée
 * Elle va par la suite renvoyé l'identifiant de celle-ci
 */
public function createPartie($login, $partie): int {
	$gagne = -1;
	$score = 0;
	try {
		$statement = $this->connexion->prepare("INSERT INTO PARTIES (pseudo, gagne, score, gameState) VALUES (?,?,?,?);");
		$statement->bindParam(1,$login);
		$statement->bindParam(2,$gagne, PDO::PARAM_INT);
		$statement->bindParam(3,$score, PDO::PARAM_INT);
		$gameasString = serialize($partie);
		$statement->bindParam(4,$gameasString);
		$statement->execute();
	} catch (PDOException $e){
		throw new ConnexionException("probleme d'enregistrement de la partie\n".$e);
	}
	try{
		$sql = "SELECT id FROM PARTIES WHERE pseudo = '".$login."' ORDER BY id DESC;";
		$statement2 = $this->connexion->query($sql);
		$res = $statement2->fetchAll(); 

		return $res[0]["id"];

	} catch (PDOException $e){
		throw new ConnexionException("probleme de récupération de l'id".$e);
	}
	return 0;
}

/**
 * fonction qui va mettre à jour les informations d'une partie dont on refère l'identifiant en paramètre
 */
public function updatePartie($id, $score, $gagne, $stateGame) {
	try {
		$statement = $this->connexion->prepare("UPDATE PARTIES SET gagne=?, score=?, gameState=?  WHERE id=?;");
		$statement->bindParam(1,$gagne, PDO::PARAM_INT);
		$statement->bindParam(2,$score, PDO::PARAM_INT);
		$gameasString = serialize($stateGame);
		$statement->bindParam(3,$gameasString);
		$statement->bindParam(4,$id, PDO::PARAM_INT);
		if ($statement->execute()){
			return true;
		} else {
			return false;
		}
	} catch (PDOException $e){
		throw new ConnexionException("probleme d'update de la partie".$e);
	}
	
}

/**
 * Fonction qui retourne la dernière partie enregistrée avec le login entrée en paramètre
 */
public function getLastPartie($login): array {
	try {
		//vu que les parties sont liés à un id qui incrémente à chaque ajout de nouvelle partie 
		//on peut facilement récupéré la dernière partie enregistrée en faisant un select et en ajoutant la condition “ORDER BY id DESC”
		$sql = "SELECT * FROM PARTIES WHERE pseudo = '".$login."' ORDER BY id DESC"; 
		$statement = $this->connexion->query($sql);
		$res = $statement->fetchAll();
		if(!empty($res[0])){
			return $res[0];
		}
		//Si il n'y a pas de partie enregistré avec le joueur on renvoie gagne = -2 pour que le contrôleur crée une nouvelle partie
		return array("gagne" => -2);
	}catch (PDOException $e){
		throw new ConnexionException("probleme de récup de la dernière partie du joueur".$e);
	}
}

/**
 * Fonction qui retourne le classement des users inscrit en fonction de leur meilleur score.
 * return pseudo, nombre de partie joué, nombre de partie gagnée et le meilleur score
 */
public function getClassement(): array {
	try {
		$sql = "SELECT pseudo, COUNT(*) AS total_partie, SUM(gagne) AS somme_gagne, MAX(score) AS score
		 FROM PARTIES WHERE gagne = 0 OR gagne = 1 GROUP BY pseudo ORDER BY score DESC";
		$statement = $this->connexion->query($sql);
		$res = $statement->fetchAll(); 

		return $res;
	}catch (PDOException $e){
		throw new ConnexionException("probleme de récup du top 3".$e);
	}
}

/**
 * Fonction qui retourne les informations statistique du joueur connecté
 * return pseudo, nombre de partie joué, nombre de partie gagnée et le meilleur score
 */
public function getInfo($login): array {
	try {
		$sql = "SELECT pseudo, COUNT(*) AS total_partie, SUM(gagne) AS somme_gagne, MAX(score) AS score FROM PARTIES p WHERE p.pseudo = '".$login."' AND (p.gagne = 0 OR p.gagne = 1);";
		$statement = $this->connexion->query($sql);
		$res = $statement->fetch(PDO::FETCH_ASSOC);
		return $res;
	} catch (PDOException $e) {
		throw new ConnexionException("problème de récupération des informations du joueur actuel".$e);
	}
}

}
