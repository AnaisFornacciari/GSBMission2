<?php
require_once __DIR__.'/../modele/class.pdogsb.php';
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

//********************************************Contrôleur connexion*****************//
Class ConnexionControleur{

    public function __construct()
    {
        ob_start();             // démarre le flux de sortie
        require_once __DIR__.'/../vues/v_entete.php';
    }
    
    public function accueil()
    {
        require_once __DIR__.'/../vues/v_connexion.php';
        require_once __DIR__.'/../vues/v_pied.php';
        $view = ob_get_clean(); // récupère le contenu du flux et le vide
        return $view;           // retourne le flux 
    }
    
    public function verifierUser(Request $request, Application $app)
    {
        session_start();
        $login = htmlentities($request->get('login'));
	$mdp = htmlentities($request->get('mdp'));
        $pdo = PdoGsb::getPdoGsb();
	$visiteur = $pdo->getInfosVisiteur($login,$mdp);
        $comptable = $pdo->getInfosComptable($login,$mdp);
	if(!is_array( $visiteur) && !is_array($comptable))
        {
            $app['couteauSuisse']->ajouterErreur("Login ou mot de passe incorrect");
            require_once __DIR__.'/../vues/v_erreurs.php';
            require_once __DIR__.'/../vues/v_connexion.php';
            require_once __DIR__.'/../vues/v_pied.php';
            $view = ob_get_clean();
        }
        else if (is_array($comptable))
        {
            $id = $comptable['id'];
            $nom =  $comptable['nom'];
            $prenom = $comptable['prenom'];
            $app['couteauSuisse']->connecterC($id,$nom,$prenom);
            require_once __DIR__.'/../vues/v_sommaire.php';
            require_once __DIR__.'/../vues/v_pied.php';
            $view = ob_get_clean();
        }
	else if (is_array($visiteur))
        {
            $id = $visiteur['id'];
            $nom =  $visiteur['nom'];
            $prenom = $visiteur['prenom'];
            $app['couteauSuisse']->connecter($id,$nom,$prenom);
            require_once __DIR__.'/../vues/v_sommaire.php';
            require_once __DIR__.'/../vues/v_pied.php';
            $view = ob_get_clean();
        }
        return $view;        
    }
    public function deconnecter(Application $app)
    {
        $app['couteauSuisse']->deconnecter();
        $app['couteauSuisse']->Logout();
        return $app->redirect('index.php');       
    }
}
//**************************************Contrôleur EtatFrais**********************

Class EtatFraisControleur 
{
    private $idVisiteur;
    private $pdo;
    public function init()
    {
        $this->idVisiteur = $_SESSION['idVisiteur'];
        $this->pdo = PdoGsb::getPdoGsb();
        ob_start();             // démarre le flux de sortie
        require_once __DIR__.'/../vues/v_entete.php';
        require_once __DIR__.'/../vues/v_sommaire.php';
    }
    public function selectionnerMois(Application $app)
    {
        session_start();
        if($app['couteauSuisse']->estConnecte())
        {
            $this->init();
            $lesMois = $this->pdo->getLesMoisDisponibles($this->idVisiteur);
            // Afin de sélectionner par défaut le dernier mois dans la zone de liste
            // on demande toutes les clés, et on prend la première,
            // les mois étant triés décroissants
            $lesCles = array_keys( $lesMois );
            $moisASelectionner = $lesCles[0];
            require_once __DIR__.'/../vues/v_listeMois.php';
             require_once __DIR__.'/../vues/v_pied.php';
            $view = ob_get_clean();
            return $view;
        }
        else
        {
            $response = new response ();
            $response->setContent ( 'Connexion nécessaire' );
            return $response;
        }
    }
    public function voirFrais(Request $request,Application $app)
    {
        session_start();
        if($app['couteauSuisse']->estConnecte())
        {
            $this->init();
            $leMois = htmlentities($request->get('lstMois'));
            $this->pdo = PdoGsb::getPdoGsb();
            $lesMois = $this->pdo->getLesMoisDisponibles($this->idVisiteur);
            $moisASelectionner = $leMois;
            $lesFraisForfait= $this->pdo->getLesFraisForfait($this->idVisiteur,$leMois);
            $lesInfosFicheFrais = $this->pdo->getLesInfosFicheFrais($this->idVisiteur,$leMois);
            $numeroAnnee = substr( $leMois,0,4);
            $numeroMois = substr( $leMois,4,2);
            $libEtat = $lesInfosFicheFrais['libEtat'];
            $montantValide = $lesInfosFicheFrais['montantValide'];
            $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
            $dateModif =  $lesInfosFicheFrais['dateModif'];
            $dateModif =  $app['couteauSuisse']->dateAnglaisVersFrancais($dateModif);
            require_once __DIR__.'/../vues/v_listeMois.php';
            require_once __DIR__.'/../vues/v_etatFrais.php';
            require_once __DIR__.'/../vues/v_pied.php';
            $view = ob_get_clean();
            return $view;
         }
        else
        {
            $response = new Response();
            $response->setContent('Connexion nécessaire');
            return $response;
        }
    } 
}
//************************************Controleur GererFicheFrais********************

Class GestionFicheFraisControleur
{
    private $pdo;
    private $mois;
    private $idVisiteur;
    private $numAnnee;
    private $numMois;
    
    public function init(Application $app)
    {
        $this->idVisiteur = $_SESSION['idVisiteur'];
        ob_start();
        require_once __DIR__.'/../vues/v_entete.php';
        require_once __DIR__.'/../vues/v_sommaire.php';
        $this->mois = $app['couteauSuisse']->getMois(date("d/m/Y"));
        $this->numAnnee =substr($this->mois,0,4);
        $this->numMois =substr( $this->mois,4,2);
        $this->pdo = PdoGsb::getPdoGsb();
    }
     
    public function saisirFrais(Application $app)
    {
        session_start();
        if($app['couteauSuisse']->estConnecte())
        {
            $this->init($app);
            if($this->pdo->estPremierFraisMois($this->idVisiteur,$this->mois))
            {
                $this->pdo->creeNouvellesLignesFrais($this->idVisiteur,$this->mois);
            }
            $lesFraisForfait = $this->pdo->getLesFraisForfait($this->idVisiteur,$this->mois);
            $numMois = $this->numMois;
            $numAnnee = $this->numAnnee; 
            require_once __DIR__.'/../vues/v_listeFraisForfait.php';
            require_once __DIR__.'/../vues/v_pied.php';
            $view = ob_get_clean();
            return $view; 
        }
         else
        {
            $response = new Response();
            $response->setContent('Connexion nécessaire');
            return $response;
        }
    }
    public function validerFrais(Request $request,Application $app)
    {
        session_start();
        if($app['couteauSuisse']->estConnecte())
        {
            $this->init($app);
            $lesFrais = htmlentities($request->get('lesFrais'));
            if($app['couteauSuisse']->lesQteFraisValides($lesFrais))
            {
                $this->pdo->majFraisForfait($this->idVisiteur,$this->mois,$lesFrais);
            }
            else
            {
                $app['couteauSuisse']->ajouterErreur("Les valeurs des frais doivent être numériques.");
                require_once __DIR__.'/../vues/v_erreurs.php';
                require_once __DIR__.'/../vues/v_pied.php';
            }
            $lesFraisForfait= $this->pdo->getLesFraisForfait($this->idVisiteur,$this->mois);
            $numMois = $this->numMois;
            $numAnnee = $this->numAnnee; 
            require_once __DIR__.'/../vues/v_listeFraisForfait.php';
            require_once __DIR__.'/../vues/v_pied.php';
            $view = ob_get_clean();
            return $view; 
        }
        else
        {
            $response = new Response();
            $response->setContent('Connexion nécessaire');
            return $response;
        }
        
    }
}
//************************************Controleur ValiderFicheFrais********************

Class ValiderFicheFraisControleur
{
    private $idComptable;
    private $pdo;
    
    public function init()
    {
        $this->idComptable = $_SESSION['idComptable'];
        $this->pdo = PdoGsb::getPdoGsb();
        ob_start();             // démarre le flux de sortie
        require_once __DIR__.'/../vues/v_entete.php';
        require_once __DIR__.'/../vues/v_sommaire.php';
    }
    public function selectionnerFiche(Application $app)
    {
        session_start();
        if($app['couteauSuisse']->estConnecteC())
        {
            $this->init();
            $lesMois = $this->pdo->getLesDates();
            $lesVisiteurs = $this->pdo->getVisiteurs();
            require_once __DIR__.'/../vues/v_listeFiches.php';
            require_once __DIR__.'/../vues/v_pied.php';
            $view = ob_get_clean();
            return $view;
        }
        else
        {
            $response = new response ();
            $response->setContent ( 'Connexion nécessaire' );
            return $response;
        }
    }
    public function voirFiche(Request $request,Application $app)
    {
        session_start();
        if($app['couteauSuisse']->estConnecteC())
        {
            $this->init();
            $lesMois = $this->pdo->getLesDates();
            $lesVisiteurs = $this->pdo->getVisiteurs();
            $leVisiteur = $request->get('lstVisiteurs');
            $leMois = $request->get('lstMois');
            $numAnnee = substr($leMois,0,4);
            $numMois = substr($leMois,4,2);
            $lesInfosFicheFrais = $this->pdo->getLesInfosFicheFraisCR($leVisiteur,$leMois);
            if($lesInfosFicheFrais)
            {
                $lesFraisForfait= $this->pdo->getLesFraisForfait($leVisiteur,$leMois);
                $libEtat = $lesInfosFicheFrais['libEtat'];
                $montantValide = $lesInfosFicheFrais['montantValide'];
                $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
                $dateModif =  $lesInfosFicheFrais['dateModif'];
                $dateModif =  $app['couteauSuisse']->dateAnglaisVersFrancais($dateModif);
                $leVisiteur = $this->pdo->getInfosVisiteurID($leVisiteur);
                require_once __DIR__.'/../vues/v_listeFiches.php';
                require_once __DIR__.'/../vues/v_findFiche.php';
                require_once __DIR__.'/../vues/v_pied.php';
            }
            else
            {
                
                $leVisiteur = $this->pdo->getInfosVisiteurID($leVisiteur);
                $app ['couteauSuisse']->ajouterErreur("Il n'existe pas de fiche de frais à valider pour " . $leVisiteur['nom'] . " " . $leVisiteur['prenom'] . " le " . $numMois . "-" . $numAnnee);
                require_once __DIR__.'/../vues/v_listeFiches.php';
                require_once __DIR__.'/../vues/v_erreurs.php';
                require_once __DIR__.'/../vues/v_pied.php';
            }
            $view = ob_get_clean();
            return $view;
         }
        else
        {
            $response = new Response();
            $response->setContent('Connexion nécessaire');
            return $response;
        }
    }
    public function validerFiche(Request $request,Application $app)
    {
         session_start();
        if($app['couteauSuisse']->estConnecteC())
        {
            $this->init();
            $lesFrais = $request->get('lesFrais');
            $idVisiteur = $request->get('leVisiteur');
            $mois = $request->get('leMois');
            $lesMois = $this->pdo->getLesDates();
            $lesVisiteurs = $this->pdo->getVisiteurs();
            $numAnnee = substr($mois,0,4);
            $numMois = substr($mois,4,2);
            $leVisiteur = $this->pdo->getInfosVisiteurID($idVisiteur);
            if($app['couteauSuisse']->lesQteFraisValides($lesFrais))
            {
                $this->pdo->majFraisForfait($idVisiteur,$mois,$lesFrais);
                $this->pdo->majFicheFrais($idVisiteur,$mois,"VA",$lesFrais);
                $app['couteauSuisse']->ajouterSucess("Fiche de frais du mois " . $numMois . "-" . $numAnnee . " pour " . $leVisiteur['nom'] . " " . $leVisiteur['prenom'] . " validée !");
                require_once __DIR__.'/../vues/v_listeFiches.php';
                require_once __DIR__.'/../vues/v_sucess.php';
                require_once __DIR__.'/../vues/v_pied.php';
            }
            else
            {
                $app['couteauSuisse']->ajouterErreur("Les valeurs des frais doivent être numériques.");
                require_once __DIR__.'/../vues/v_listeFiches.php';
                require_once __DIR__.'/../vues/v_erreurs.php';
                require_once __DIR__.'/../vues/v_pied.php';
            }
            $view = ob_get_clean();
            return $view;
        }
        else
        {
            $response = new Response();
            $response->setContent('Connexion nécessaire');
            return $response;
        }
    }
}
//************************************Controleur GenererEtatQuotidient********************

Class GenererEtatQuotidientControleur
{
    private $idComptable;
    private $pdo;
    
    public function init()
    {
        $this->idComptable = $_SESSION['idComptable'];
        $this->pdo = PdoGsb::getPdoGsb();
        ob_start();             // démarre le flux de sortie
        require_once __DIR__.'/../vues/v_entete.php';
        require_once __DIR__.'/../vues/v_sommaire.php';
    }
    
    public function genererEtat(Application $app)
    {
        session_start();
        if($app['couteauSuisse']->estConnecteC())
        {
            $this->init($app);
            $date = (new DateTime(date("Y-m-d")))->format("Y-m-d");
            $lesInfosFicheFrais = $this->pdo->getLesInfosFicheFraisVA($date);
            if($lesInfosFicheFrais)
            {
                $tableauFiches = " <h3> Fiches de frais validées le " .$date. " : </h3> <br/><br/> ";
                foreach ($lesInfosFicheFrais as $InfosFicheFrais) 
                {
                    $leVisiteur = $InfosFicheFrais['idVisiteur'];
                    $leMois = $InfosFicheFrais['mois'];
                    $numAnnee = substr($leMois,0,4);
                    $numMois = substr($leMois,4,2);
                    $lesFraisForfait= $this->pdo->getLesFraisForfait($leVisiteur,$leMois);
                    $nomVisiteur = $InfosFicheFrais['nom'];
                    $prenomVisiteur = $InfosFicheFrais['prenom'];
                    $libEtat = $InfosFicheFrais['libEtat'];
                    $montantValide = $InfosFicheFrais['montantValide'];
                    $nbJustificatifs = $InfosFicheFrais['nbJustificatifs'];
                    $tableauFiches = $tableauFiches. "
                        <p>
                            Fiche de ".$nomVisiteur." ".$prenomVisiteur. " créé le : " .$numMois."-".$numAnnee. " <br/> Montant validé : " .$montantValide."€
                        </p>
                        <table cellspacing='0' cellpadding='1' border='1'>
                            <caption> Eléments forfaitisés </caption><br/>
                            <tr>";
                                foreach ($lesFraisForfait as $unFraisForfait) 
                                {
                                    $libelle = $unFraisForfait['libelle'];
                                    $tableauFiches = $tableauFiches. " <th> " .$libelle. " </th> ";
                                }
                                $tableauFiches =
                            $tableauFiches .
                            "</tr>
                            <tr>";
                                foreach ($lesFraisForfait as $unFraisForfait) 
                                {
                                    $quantite = $unFraisForfait['quantite'];
                                    $tableauFiches = $tableauFiches. " <td> " .$quantite. " </td> ";
                                }
                                $tableauFiches = $tableauFiches. 
                            "</tr>
                        </table>
                        <br/><br/>
                    ";
                }
                ob_end_clean();
                $view = ob_get_clean();
                $app['couteauSuisse']->htmlToTPdf($tableauFiches,'Fiches_de_frais_du_jour');
            }
            else
            {
                $app ['couteauSuisse']->ajouterErreur("Aucune fiche de frais n'a été validée à ce jour.");
                require_once __DIR__.'/../vues/v_erreurs.php';
                require_once __DIR__.'/../vues/v_pied.php';
                $view = ob_get_clean();
            }
            return $view;
        }
        else
        {
            $response = new Response();
            $response->setContent('Connexion nécessaire');
            return $response;
        }
    }
}
?>

