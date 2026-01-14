<?php


session_start(); // Recuperamos la sesión actual
session_unset(); // Borramos las variables de sesión
session_destroy(); // Destruimos la sesión por completo

// Te devolvemos al inicio (ahora como invitado)
header("Location: ../index.php");
exit();
?>