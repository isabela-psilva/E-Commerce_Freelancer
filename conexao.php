<?php
    $servidor="127.0.0.1";
    $usuario="root";
    $senha="usbw";
    $banco="freelancer";
    $conecta_db=mysql_connect($servidor, $usuario, $senha) or die (mysql_error());
    mysql_select_db($banco) or die ("Erro ao Conectar");
    mysql_set_charset("utf8", $conecta_db);
?>