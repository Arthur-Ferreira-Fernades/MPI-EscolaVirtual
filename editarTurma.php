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

// Busca informações da turma
$sqlTurma = "SELECT TurmaNome, DiasSemana, Horarios, TurmaReuniao FROM Turmas WHERE TurmaId = ? AND ProfId = ?";
$stmtTurma = $conexao->prepare($sqlTurma);
$stmtTurma->execute([$turmaId, $profId]);
$turma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

// Se o professor tentar acessar uma turma que não é dele
if (!$turma) {
    header("location: home.php");
    exit();
}

// Converte os dados de DiasSemana e Horarios
$turmaDiasSelecionados = explode(', ', $turma['DiasSemana']);
$horarios = json_decode($turma['Horarios'], true) ?: [];

// Lista de dias da semana
$diasSemana = ["Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado", "Domingo"];

// Busca alunos da turma
$sqlAlunosTurma = "SELECT AluId, AluNome FROM Alunos WHERE TurmaId = ?";
$stmtAlunosTurma = $conexao->prepare($sqlAlunosTurma);
$stmtAlunosTurma->execute([$turmaId]);
$alunosTurma = $stmtAlunosTurma->fetchAll(PDO::FETCH_ASSOC);

// Busca alunos disponíveis para adicionar
$sqlAlunosDisponiveis = "SELECT AluId, AluNome FROM Alunos WHERE TurmaId IS NULL";
$stmtAlunosDisponiveis = $conexao->prepare($sqlAlunosDisponiveis);
$stmtAlunosDisponiveis->execute();
$alunosDisponiveis = $stmtAlunosDisponiveis->fetchAll(PDO::FETCH_ASSOC);

// Se o formulário for enviado para editar a turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editarTurma'])) {
    $novoNome = $_POST['TurmaNome'];
    $turmaReuniao = $_POST['TurmaReuniao'];
    $diasSelecionados = isset($_POST['DiasSemana']) ? implode(', ', $_POST['DiasSemana']) : '';

    // Monta um array de horários
    $horariosAtualizados = [];
    foreach ($_POST['DiasSemana'] as $dia) {
        $horariosAtualizados[$dia] = $_POST["Horario_$dia"];
    }
    $horariosJson = json_encode($horariosAtualizados);

    // Atualiza os dados da turma
    $sqlUpdate = "UPDATE Turmas SET TurmaNome = ?, DiasSemana = ?, Horarios = ?, TurmaReuniao = ? WHERE TurmaId = ?";
    $stmtUpdate = $conexao->prepare($sqlUpdate);
    $stmtUpdate->execute([$novoNome, $diasSelecionados, $horariosJson, $turmaReuniao, $turmaId]);

    header("location: editarTurma.php?turmaId=$turmaId");
    exit();
}

// Se o professor quiser remover um aluno da turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removerAluno'])) {
    $alunoIdRemover = $_POST['AlunoIdRemover'];

    // Remove o aluno da turma (define TurmaId como NULL)
    $sqlRemoverAluno = "UPDATE Alunos SET TurmaId = NULL WHERE AluId = ?";
    $stmtRemoverAluno = $conexao->prepare($sqlRemoverAluno);
    $stmtRemoverAluno->execute([$alunoIdRemover]);

    header("location: editarTurma.php?turmaId=$turmaId");
    exit();
}

// Se o professor quiser adicionar um aluno à turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionarAluno'])) {
    $alunoIdAdicionar = $_POST['AlunoIdAdicionar'];

    if (!empty($alunoIdAdicionar)) {
        $sqlAdicionarAluno = "UPDATE Alunos SET TurmaId = ? WHERE AluId = ?";
        $stmtAdicionarAluno = $conexao->prepare($sqlAdicionarAluno);
        $stmtAdicionarAluno->execute([$turmaId, $alunoIdAdicionar]);

        header("location: editarTurma.php?turmaId=$turmaId");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Turma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function toggleHorarios() {
            const checkboxes = document.querySelectorAll("input[name='DiasSemana[]']");
            checkboxes.forEach(checkbox => {
                const horarioField = document.getElementById(`horario_${checkbox.value}`);
                if (checkbox.checked) {
                    horarioField.style.display = "block";
                } else {
                    horarioField.style.display = "none";
                }
            });
        }
    </script>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Editar Turma</h2>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nome da Turma:</label>
            <input type="text" class="form-control" name="TurmaNome" value="<?= $turma['TurmaNome']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Link da Reunião:</label>
            <input type="text" class="form-control" name="TurmaReuniao" value="<?= $turma['TurmaReuniao']; ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Dias da Semana:</label>
            <?php foreach ($diasSemana as $dia): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="DiasSemana[]" value="<?= $dia; ?>" 
                           <?= in_array($dia, $turmaDiasSelecionados) ? 'checked' : ''; ?> 
                           onclick="toggleHorarios()">
                    <label class="form-check-label"><?= $dia; ?></label>
                </div>
                <div id="horario_<?= $dia; ?>" style="display: <?= in_array($dia, $turmaDiasSelecionados) ? 'block' : 'none'; ?>;">
                    <label>Horário:</label>
                    <input type="time" class="form-control" name="Horario_<?= $dia; ?>" 
                           value="<?= isset($horarios[$dia]) ? $horarios[$dia] : ''; ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" name="editarTurma" class="btn btn-primary w-100">Salvar Alterações</button>
    </form>

    <!-- Lista de alunos matriculados -->
    <h4 class="mt-4">Alunos Matriculados</h4>
    <?php if (count($alunosTurma) > 0): ?>
        <ul class="list-group">
            <?php foreach ($alunosTurma as $aluno): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $aluno['AluNome']; ?>
                    <form method="POST">
                        <input type="hidden" name="AlunoIdRemover" value="<?= $aluno['AluId']; ?>">
                        <button type="submit" name="removerAluno" class="btn btn-danger btn-sm">Remover</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
     <!-- Formulário para adicionar alunos -->
     <h4 class="mt-4">Adicionar Aluno</h4>
    <form method="POST">
        <select class="form-control" name="AlunoIdAdicionar">
            <option value="">Selecione um aluno...</option>
            <?php foreach ($alunosDisponiveis as $aluno): ?>
                <option value="<?= $aluno['AluId']; ?>"><?= $aluno['AluNome']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="adicionarAluno" class="btn btn-success w-100 mt-2">Adicionar Aluno</button>
    </form>

    <a href="home.php" class="btn btn-secondary w-100 mt-3">Voltar</a>
</div>

</body>
</html>
