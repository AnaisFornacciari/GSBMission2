<h3>Fiche de frais du mois <?php echo $numMois."-".$numAnnee ?> pour <?php echo $leVisiteur['nom']." ".$leVisiteur['prenom'] ?> </h3>
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
		$libelle = $unFraisForfait['libelle'];
		?>	
		<th> <?php echo $libelle?> </th>
		<?php
            }
            ?>
	</tr>
        <tr>
            <?php
            foreach ($lesFraisForfait as $unFraisForfait) 
            {
		$quantite = $unFraisForfait['quantite'];
		?>
                <td class="qteForfait"> <?php echo $quantite?> </td>
		<?php
            }
            ?>
            </tr>
    </table>
</div>
 
