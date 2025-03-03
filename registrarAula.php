<?php
session_start();
require('scripts/conectaBanco.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_validado']) || $_SESSION['usuario_validado'] !== true) {
    header("location: index.php?login=erro");
    exit();
}

$profId = $_SESSION['UsuarioId'];
$turmaId = $_GET['turmaId'];

// Verifica se a turma pertence ao professor logado
$sqlTurma = "SELECT TurmaNome FROM Turmas WHERE TurmaId = ? AND ProfId = ?";
$stmtTurma = $conexao->prepare($sqlTurma);
$stmtTurma->execute([$turmaId, $profId]);
$turma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

// Se a turma não for do professor, redireciona
if (!$turma) {
    header("location: home.php");
    exit();
}

// Se o formulário for enviado para registrar aula e presenças
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrarAula'])) {
    $dataAula = $_POST['DataAula'];
    $conteudoAula = $_POST['ConteudoAula'];

    // Insere a aula no banco associando ao professor
    $sqlInsertAula = "INSERT INTO Aulas (TurmaId, ProfId, DataAula, Conteudo) VALUES (?, ?, ?, ?)";
    $stmtInsertAula = $conexao->prepare($sqlInsertAula);
    $stmtInsertAula->execute([$turmaId, $profId, $dataAula, $conteudoAula]);

    // Obtém o ID da aula recém-criada
    $aulaId = $conexao->lastInsertId();

    // Registra a presença dos alunos
    if (isset($_POST['presenca'])) {
        foreach ($_POST['presenca'] as $alunoId => $presente) {
            $presenteBool = ($presente == "1") ? 1 : 0;
            $sqlInsertPresenca = "INSERT INTO Presencas (AulaId, AluId, Presente) VALUES (?, ?, ?)";
            $stmtInsertPresenca = $conexao->prepare($sqlInsertPresenca);
            $stmtInsertPresenca->execute([$aulaId, $alunoId, $presenteBool]);
        }
    }

    header("location: registrarAula.php?turmaId=$turmaId");
    exit();
}

// Busca aulas já registradas com o professor que registrou
$sqlAulas = "SELECT Aulas.AulaId, Aulas.DataAula, Aulas.Conteudo, professores.ProNome 
             FROM Aulas 
             JOIN professores ON Aulas.ProfId = professores.ProfId
             WHERE Aulas.TurmaId = ? 
             ORDER BY Aulas.DataAula DESC";
$stmtAulas = $conexao->prepare($sqlAulas);
$stmtAulas->execute([$turmaId]);
$aulas = $stmtAulas->fetchAll(PDO::FETCH_ASSOC);

// Busca alunos matriculados na turma
$sqlAlunos = "SELECT AluId, AluNome FROM Alunos WHERE TurmaId = ?";
$stmtAlunos = $conexao->prepare($sqlAlunos);
$stmtAlunos->execute([$turmaId]);
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar Aula</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Registrar Aula - <?= htmlspecialchars($turma['TurmaNome']); ?></h2>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Data da Aula:</label>
            <input type="date" class="form-control" name="DataAula" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Conteúdo da Aula:</label>
            <textarea class="form-control" name="ConteudoAula" rows="4" required></textarea>
        </div>

        <!-- Marcar Presenças -->
        <div class="mb-3">
            <label class="form-label">Presença dos Alunos:</label>
            <ul class="list-group">
                <?php foreach ($alunos as $aluno): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= $aluno['AluNome']; ?>
                        <div>
                            <label class="me-2">
                                <input type="radio" name="presenca[<?= $aluno['AluId']; ?>]" value="1" required> Presente
                            </label>
                            <label>
                                <input type="radio" name="presenca[<?= $aluno['AluId']; ?>]" value="0" required> Ausente
                            </label>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <button type="submit" name="registrarAula" class="btn btn-primary w-100">Registrar Aula</button>
    </form>

    <!-- Lista de Aulas Registradas -->
    <h4 class="mt-4">Aulas Registradas</h4>
    <?php if (count($aulas) > 0): ?>
        <ul class="list-group">
            <?php foreach ($aulas as $aula): ?>
                <li class="list-group-item">
                    <strong><?= date('d/m/Y', strtotime($aula['DataAula'])); ?>:</strong> <?= htmlspecialchars($aula['Conteudo']); ?>
                    <br><small><em>Registrado por: <?= htmlspecialchars($aula['ProNome']); ?></em></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">Nenhuma aula registrada ainda.</p>
    <?php endif; ?>

    <a href="home.php" class="btn btn-secondary w-100 mt-3">Voltar</a>
</div>

</body>
</html>
