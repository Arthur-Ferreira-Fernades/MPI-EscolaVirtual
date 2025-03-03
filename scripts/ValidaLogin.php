<?php
session_start();
require('conectaBanco.php');


$_SESSION['usuario_validado'] = false;

$Login = $_POST['email'];
$senha = $_POST['senha'];

if ($senha != "" && $Login != "") {
    $stmt = $conexao->prepare("SELECT ProEmail, ProSenha, ProfId FROM Professores where ProEmail=? and ProSenha=?");
    $stmt->bindParam(1, $Login);
    $stmt->bindParam(2, $senha);
    $stmt->execute();
    $Usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($Usuario) { 
        $UsuarioId = $Usuario['ProfId'];
        header("location: ../home.php");
        $_SESSION['usuario_validado'] = true;
        $_SESSION['UsuarioId'] = $UsuarioId;
    } else {
        header('location: ../login.php?login=erro1');
    }
} else {
    header('location: ../index.php?login=erro2');
}
?>