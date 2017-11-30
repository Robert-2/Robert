<?php
$err_head = "
<style>
    h3 { color: red; }
</style>
<h1>
    Robert v".R_VERSION."
</h1>";

$err_noConfig = "
<h3>
    Vous devez renseigner l'accès à la base de données dans le fichier '$userConfig' !
</h3>";

$err_noConfigCopy = "
<p>
    > Impossible de copier le fichier automatiquement. Merci de le faire manuellement, ou bien
    vérifiez les droits d'accès du serveur au dossier 'config/'.
</p>
<p>
    > Pour cela, copiez le fichier <b>config/exemple.user_config.php</b> en <b>user_config.php</b>
    puis modifiez-le pour renseigner l'accès à la base de données du Robert.
</p>";

$err_pdoConnexion = "
<h3>
    Impossible d'accéder à la base de données MySQL de Robert avec les données suivantes :
</h3>
<ul>
    <li>Host   = <code>$host</code></li>
    <li>Server = <code>$serverName</code></li>
</ul>
<h3>Message d'erreur de PDO :</h3>";
