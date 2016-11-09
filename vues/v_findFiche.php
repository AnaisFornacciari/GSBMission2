<h3>Fiche de frais du mois <?php echo $numMois."-".$numAnnee ?> pour <?php echo $leVisiteur['nom']." ".$leVisiteur['prenom'] ?> </h3>
    <form action="validerFiche" method="post">
        <div class="encadre">
            <p>
                Etat : <?php echo $libEtat?> depuis le <?php echo $dateModif?> <br> Montant validé : <?php echo $montantValide?>
            </p>

            <table class="listeLegere">
                <caption>Eléments forfaitisés </caption>
                <tr>
                    <?php
                    foreach ($lesFraisForfait as $unFraisForfait) 
                    {
                        $idFrais = $unFraisForfait['idfrais'];
                        $libelle = $unFraisForfait['libelle'];
                        ?>
                        <th> <label for="idFrais"><?php echo $libelle ?></label> </th>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <?php
                    foreach ($lesFraisForfait as $unFraisForfait) 
                    {
                        $idFrais = $unFraisForfait['idfrais'];
                        $quantite = $unFraisForfait['quantite'];
                        ?>
                        <td class="qteForfait"> <input type="text" id="idFrais" name="lesFrais[<?php echo $idFrais?>]" size="10" maxlength="5" value="<?php echo $quantite?>" > </td>
                        <?php
                    }
                    ?>
                    </tr>
            </table>
    
        </div>
        <div class="piedForm">
            <p>
                <input id="ok" type="submit" value="Valider" size="20" />
                <input id="annuler" type="reset" value="Effacer" size="20" />
            </p>
        </div>
    </form>
 
