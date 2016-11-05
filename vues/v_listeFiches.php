<div id="contenu">
    <h2>Les fiches de frais</h2>
    <form action="voirFiche" method="post">
        <h3>Visiteur à sélectionner : </h3>
        <div class="corpsForm">
            <p>
                <label for="lstVisiteurs">Visiteurs : </label>
                <select id="lstVisiteurs" name="lstVisiteurs">
                    <?php
                        foreach ($lesVisiteurs as $unVisiteur)
                        {
                            ?>
                            <option selected value="<?php echo $unVisiteur['id'] ?>"><?php echo  $unVisiteur['nom']." ".$unVisiteur['prenom'] ?> </option>
                            <?php
                        }
                        ?>    
                </select>
            </p>
        </div>
        <h3>Date à sélectionner : </h3>
        <div class="corpsForm">
            <p>
                <label for="lstMois">Dates : </label>
                <select id="lstMois" name="lstMois">
                    <?php
                        foreach ($lesMois as $unMois)
                        {
                            ?>
                            <option selected value="<?php echo $unMois['date'] ?>"><?php echo  $unMois['numMois']."/".$unMois['numMois'] ?> </option>
                            <?php
                        }
                        ?>    
                </select>
            </p>
        </div>
        <div class="piedForm">
            <p>
                <input id="ok" type="submit" value="Valider" size="20" />
                <input id="annuler" type="reset" value="Effacer" size="20" />
            </p> 
          </div>
    </form>