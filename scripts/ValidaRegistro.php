<?php
session_start();
require('conectaBanco.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = "../ProfessoresFotos/";

    // Caminho do arquivo
    $targetFile = $targetDir . basename($_FILES["foto"]["name"]);

    // Verifica se o arquivo é uma imagem, somente se a imagem foi enviada
    $ProFoto = null; // Inicializa a variável de foto

    if (!empty($_FILES["foto"]["name"])) {
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["foto"]["tmp_name"]);

        if ($check !== false) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFile)) {
                $ProFoto = basename($_FILES["foto"]["name"]); // Atribui o nome da foto se o upload for bem-sucedido
            } else {
                header("Location: registroFalha.php?Erro=2"); // Falha no upload
                exit();
            }
        } else {
            header("Location: registroFalha.php?Erro=3"); // Não é uma imagem
            exit();
        }
    } else {
        $ProFoto = 'padrao.png'; // Defina uma imagem padrão ou nula, se desejar
    }

    // Dados do formulário
    $ProNome = $_POST['nome'];
    $ProEmail = $_POST['email'];
    $ProSenha = $_POST['senha'];

    // Insere no banco de dados
    $sql = "INSERT INTO professores (ProNome, ProEmail, ProSenha, ProFoto) VALUES (?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$ProNome, $ProEmail, $ProSenha, $ProFoto]);

    // Verifique se houve erro ao inserir
    if ($erro = error_get_last()) {
        // Obtém a mensagem de erro
        $MensagemErro = $erro['message'];

        // Verifica se a mensagem de erro contém o texto desejado
        if (strpos($MensagemErro, 'Undefined array key') !== false || strpos($MensagemErro, 'Integrity constraint violation') !== false) {
            // Redireciona o usuário para a página de login
            header("Location: registroFalha.php?Erro=1");
            exit(); // Encerra o script para garantir que o redirecionamento funcione corretamente
        }
    } else {
        // Redireciona para uma página de sucesso ou de confirmação
        header("Location: ../index.php");
        exit();
    }
}
?>