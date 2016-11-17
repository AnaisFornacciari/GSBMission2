<?php
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

Class CouteauSuisse
{
    /**
     * Enregistre dans une variable session les infos d'un visiteur

     * @param $id 
     * @param $nom
     * @param $prenom
     */
        public function connecter($id,$nom,$prenom)
        {
            $_SESSION['idVisiteur']= $id; 
            $_SESSION['nom']= $nom;
            $_SESSION['prenom']= $prenom;
        }

    /**
     * Enregistre dans une variable session les infos d'un comptable

     * @param $id 
     * @param $nom
     * @param $prenom
     */
        public function connecterC($id,$nom,$prenom)
        {
            $_SESSION['idComptable']= $id; 
            $_SESSION['nom']= $nom;
            $_SESSION['prenom']= $prenom;
        }

      /**
     * Teste si un quelconque visiteur est connecté
     * @return vrai ou faux 
     */
        public  function estConnecte()
        {
            return isset($_SESSION['idVisiteur']);
        }

        /**
     * Teste si un quelconque comptable est connecté
     * @return vrai ou faux 
     */
        public  function estConnecteC()
        {
            return isset($_SESSION['idComptable']);
        }

     /**
     * Détruit la session active
     */
        public function deconnecter()
        {
            session_destroy();
        }
    /**
     * Transforme une date au format français jj/mm/aaaa vers le format anglais aaaa-mm-jj

     * @param $madate au format  jj/mm/aaaa
     * @return la date au format anglais aaaa-mm-jj
    */
        public function dateFrancaisVersAnglais($maDate)
        {
            @list($jour,$mois,$annee) = explode('/',$maDate);
            return date('Y-m-d',mktime(0,0,0,$mois,$jour,$annee));
        }
    /**
     * Transforme une date au format format anglais aaaa-mm-jj vers le format français jj/mm/aaaa 

     * @param $madate au format  aaaa-mm-jj
     * @return la date au format format français jj/mm/aaaa
    */
        public function dateAnglaisVersFrancais($maDate)
        {
           @list($annee,$mois,$jour)=explode('-',$maDate);
           $date="$jour"."/".$mois."/".$annee;
           return $date;
        }
    /**
     * retourne le mois au format aaaamm selon le jour dans le mois

     * @param $date au format  jj/mm/aaaa
     * @return le mois au format aaaamm
    */
        public function getMois($date)
        {
            @list($jour,$mois,$annee) = explode('/',$date);
            if(strlen($mois) == 1)
            {
                $mois = "0".$mois;
            }
            return $annee.$mois;
        }

    /* gestion des erreurs*/
    /**
     * Indique si une valeur est un entier positif ou nul

     * @param $valeur
     * @return vrai ou faux
    */
        public function estEntierPositif($valeur)
        {
            return preg_match("/[^0-9]/", $valeur) == 0;
        }

    /**
     * Indique si un tableau de valeurs est constitué d'entiers positifs ou nuls

     * @param $tabEntiers : le tableau
    * @return vrai ou faux
    */
        public function estTableauEntiers($tabEntiers)
        {
            $ok = true;
            foreach($tabEntiers as $unEntier)
            {
                if(!$this->estEntierPositif($unEntier))
                {
                    $ok = false; 
                }
            }
            return $ok;
        }

    /**
     * Vérifie si une date est inférieure d'un an à la date actuelle

     * @param $dateTestee 
     * @return vrai ou faux
    */
        public function estDateDepassee($dateTestee)
        {
            $dateActuelle=date("d/m/Y");
            @list($jour,$mois,$annee) = explode('/',$dateActuelle);
            $annee--;
            $AnPasse = $annee.$mois.$jour;
            @list($jourTeste,$moisTeste,$anneeTeste) = explode('/',$dateTestee);
            return ($anneeTeste.$moisTeste.$jourTeste < $AnPasse); 
        }

    /**
     * Vérifie la validité du format d'une date française jj/mm/aaaa 

     * @param $date 
     * @return vrai ou faux
    */
        public function estDateValide($date)
        {
            $tabDate = explode('/',$date);
            $dateOK = true;
            if (count($tabDate) != 3)
            {
                $dateOK = false;
            }
            else
            {
                if (!$this->estTableauEntiers($tabDate))
                {
                    $dateOK = false;
                }
                else 
                {
                    if (!checkdate($tabDate[1], $tabDate[0], $tabDate[2]))
                    {
                        $dateOK = false;
                    }
                }
            }
            return $dateOK;
        }

    /**
     * Vérifie que le tableau de frais ne contient que des valeurs numériques 

     * @param $lesFrais 
     * @return vrai ou faux
    */
        function lesQteFraisValides($lesFrais)
        {
            return $this->estTableauEntiers($lesFrais);
        }

    /**
     * Vérifie la validité des trois arguments : la date, le libellé du frais et le montant 

     * des message d'erreurs sont ajoutés au tableau des erreurs

     * @param $dateFrais 
     * @param $libelle 
     * @param $montant
     */
        function valideInfosFrais($dateFrais,$libelle,$montant)
        {
            if($dateFrais=="")
            {
                $this->ajouterErreur("Le champ date ne doit pas être vide");
            }
            else
            {
                if(!$this->estDatevalide($dateFrais))
                {
                    $this->ajouterErreur("Date invalide");
                }	
                else
                {
                    if(estDateDepassee($dateFrais))
                    {
                        $this->ajouterErreur("date d'enregistrement du frais dépassé, plus de 1 an");
                    }			
                }
            }
            if($libelle == "")
            {
                $this->ajouterErreur("Le champ description ne peut pas être vide");
            }
            if($montant == "")
            {
                $this->ajouterErreur("Le champ montant ne peut pas être vide");
            }
            else if( !is_numeric($montant) )
            {
                $this->ajouterErreur("Le champ montant doit être numérique");
            }
        }

    /**
     * Ajoute le libellé d'une erreur au tableau des erreurs 

     * @param $msg : le libellé de l'erreur 
     */
        function ajouterErreur($msg){
           if (! isset($_REQUEST['erreurs']))
            {
              $_REQUEST['erreurs']=array();
            } 
           $_REQUEST['erreurs'][]=$msg;
        }

    /**
     * Retoune le nombre de lignes du tableau des erreurs 

     * @return le nombre d'erreurs
     */
        function nbErreurs()
        {
           if (!isset($_REQUEST['erreurs']))
            {
                return 0;
            }
            else
            {
                return count($_REQUEST['erreurs']);
            }
        }
        
        
    /**
     * Ajoute le libellé d'un "sucess" au tableau des "sucess" 

     * @param $msg : le libellé du "sucess"
     */
        function ajouterSucess($msg){
           if (! isset($_REQUEST['sucess']))
            {
              $_REQUEST['sucess']=array();
            } 
           $_REQUEST['sucess'][]=$msg;
        }
        
    /**
     * Retoune le nombre de lignes du tableau des "sucess"

     * @return le nombre de "sucess"
     */
        function nbSucess()
        {
           if (!isset($_REQUEST['sucess']))
            {
                return 0;
            }
            else
            {
                return count($_REQUEST['sucess']);
            }
        } 

        function Logout()
        {
            $_SESSION = array ();
            if (ini_get ( "session.use_cookies" ))
            {
                $params = session_get_cookie_params ();
                setcookie ( session_name (), '', time () - 42000, $params ["path"], $params ["domain"], $params ["secure"], $params ["httponly"] );
            }
            session_destroy ();
            session_start ();
            $_SESSION ['result'] = " ";
        }

        public function htmlToTPdf($content,$titre)
        {

            require_once('../tcpdf/tcpdf.php');
            // create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information

            $pdf->SetTitle($titre);
            $pdf->setFooterData(array(0,64,0), array(0,64,128));
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set some language-dependent strings (optional)
            if (@file_exists(dirname(__FILE__).'/lang/eng.php')) 
            {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
            }
            // ---------------------------------------------------------
            // set default font subsetting mode
            $pdf->setFontSubsetting(true);
            // Set font
            // dejavusans is a UTF-8 Unicode font, if you only need to
            // print standard ASCII chars, you can use core fonts like
            // helvetica or times to reduce file size.
            $pdf->SetFont('dejavusans', '', 14, '', true);
            // Add a page
            // This method has several options, check the source code documentation for more information.
            $pdf->AddPage();
            // set text shadow effect
            $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
            // Set some content to print

            // Print text using writeHTMLCell()
            $pdf->writeHTMLCell(0, 0, '', '', $content, 0, 1, 0, true, '', true);
            // ---------------------------------------------------------
            // Close and output PDF document
            // This method has several options, check the source code documentation for more information.
            $pdf->Output($titre.'.pdf', 'I');
        }
}

/*------------------------Fin classe---------------------------*/

/*------------------------Création du service--------------------*/

$app['couteauSuisse'] = function ()
{
    return new CouteauSuisse();
};