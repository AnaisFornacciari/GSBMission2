    <!-- Division pour le sommaire -->
<div id="menuGauche">
    <div id="infosUtil">
    </div>  
    <?php 
    if(is_array($visiteur))
    {
        ?>
        <ul id="menuList">
           <li>
                <?php echo "Visiteur :<br>";
                echo $_SESSION['prenom']."  ".$_SESSION['nom'] ?>
            </li>
            <li class="smenu">
                <a href="saisirFrais" title="Saisie fiche de frais ">Saisie fiche de frais</a>
            </li>
            <li class="smenu">
                <a href="selectionnerMois" title="Consultation de mes fiches de frais">Mes fiches de frais</a>
            </li>
            <li class="smenu">
                <a href="deconnecter" title="Se déconnecter">Déconnexion</a>
            </li>
        </ul>
        <?php
    }
    else 
    {
        ?>
        <ul id="menuList">
            <li>
                <?php echo "Comptable :<br>";
                echo $_SESSION['prenom']."  ".$_SESSION['nom'] ?>
            </li>
            <li class="smenu">
                <a href="validerFiche" title="Valider fiche de frais">Valider fiche de frais</a>
            </li>
            <li class="smenu">
                <a href="genererEtatQuotidien" title="Consultation des fiches de frais Valider">Générer mon état quotidien</a>
            </li>
            <li class="smenu">
                <a href="deconnecter" title="Se déconnecter">Déconnexion</a>
            </li>
        </ul>
        <?php
    }
    ?>
 </div>