<?php
session_start();
require('scripts/conectaBanco.php');

if (!isset($_SESSION['usuario_validado']) || $_SESSION['usuario_validado'] !== true) {
  header("location: index.php?login=erro");
  exit();
}

$profId = $_SESSION['UsuarioId'];
$sql = "SELECT TurmaId, TurmaNome, DiasSemana, Horarios, TurmaReuniao FROM Turmas WHERE ProfId = ?";
$stmt = $conexao->prepare($sql);
$stmt->execute([$profId]);
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ordenacao = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'alfabetico';

function converterDiaParaNumero($dia) {
  $dias = [
      'Segunda-feira' => 1, 'Terça-feira' => 2, 'Quarta-feira' => 3,
      'Quinta-feira' => 4, 'Sexta-feira' => 5, 'Sábado' => 6, 'Domingo' => 7
  ];
  return $dias[$dia] ?? 8; // Se não encontrado, assume 8 (vai para o final)
}

if ($ordenacao == 'alfabetico') {
  usort($turmas, function ($a, $b) {
      return strcmp($a['TurmaNome'], $b['TurmaNome']);
  });
} elseif ($ordenacao == 'dia') {
  $diaAtual = date('N'); // Obtém o dia da semana atual (1=segunda, ..., 7=domingo)

  usort($turmas, function ($a, $b) use ($diaAtual) {
      // Pega o primeiro dia da semana listado para cada turma
      $diasA = explode(', ', $a['DiasSemana']);
      $diasB = explode(', ', $b['DiasSemana']);

      $primeiroDiaA = converterDiaParaNumero($diasA[0] ?? '');
      $primeiroDiaB = converterDiaParaNumero($diasB[0] ?? '');

      // Se o dia da turma já passou na semana, joga para o final
      if ($primeiroDiaA < $diaAtual) $primeiroDiaA += 7;
      if ($primeiroDiaB < $diaAtual) $primeiroDiaB += 7;

      return $primeiroDiaA - $primeiroDiaB;
  });
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionarAluno'])) {
  $turmaId = $_POST['TurmaId'];
  $aluNome = $_POST['AluNome'];
  $aluTelefone = $_POST['AluTelefone'];
  $aluEmail = $_POST['AluEmail'];
  $aluNascimento = $_POST['AluNascimento'];
  $aluCPF = $_POST['AluCPF'];

  try {
    // Insere o aluno no banco de dados
    $sqlInsert = "INSERT INTO Alunos (AluNome, AluTelefone, AluEmail, AluNascimento, AluCPF, TurmaId) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conexao->prepare($sqlInsert);
    $stmtInsert->execute([$aluNome, $aluTelefone, $aluEmail, $aluNascimento, $aluCPF, $turmaId]);

    $mensagem = "<div class='alert alert-success'>Aluno cadastrado com sucesso!</div>";
  } catch (PDOException $e) {
    $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar aluno: " . $e->getMessage() . "</div>";
  }
}

// Buscar alunos disponíveis para adicionar à turma
$sqlAlunosDisponiveis = "SELECT AluId, AluNome FROM Alunos WHERE TurmaId IS NULL OR TurmaId = 0";
$stmtAlunosDisponiveis = $conexao->prepare($sqlAlunosDisponiveis);
$stmtAlunosDisponiveis->execute();
$alunosDisponiveis = $stmtAlunosDisponiveis->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criarTurma'])) {
  $turmaNome = $_POST['TurmaNome'];
  $diasSemana = isset($_POST['DiasSemana']) ? implode(', ', $_POST['DiasSemana']) : '';

  // Monta um array de horários para os dias selecionados
  $horarios = [];
  foreach ($_POST['DiasSemana'] as $dia) {
    $horarios[$dia] = $_POST["Horario_$dia"];
  }
  $horariosJson = json_encode($horarios);
  $turmaReuniao = $_POST['TurmaReuniao'] ?? '';

  try {
    $sqlInsertTurma = "INSERT INTO Turmas (TurmaNome, ProfId, DiasSemana, Horarios, TurmaReuniao) VALUES (?, ?, ?, ?, ?)";
    $stmtInsertTurma = $conexao->prepare($sqlInsertTurma);
    $stmtInsertTurma->execute([$turmaNome, $profId, $diasSemana, $horariosJson, $turmaReuniao]);

    // Obtém o ID da turma recém-criada
    $turmaId = $conexao->lastInsertId();

    // Adiciona alunos selecionados à turma
    if (!empty($_POST['AlunosSelecionados'])) {
      foreach ($_POST['AlunosSelecionados'] as $alunoId) {
        $sqlAdicionarAluno = "UPDATE Alunos SET TurmaId = ? WHERE AluId = ?";
        $stmtAdicionarAluno = $conexao->prepare($sqlAdicionarAluno);
        $stmtAdicionarAluno->execute([$turmaId, $alunoId]);
      }
    }

    $mensagem = "<div class='alert alert-success'>Turma criada com sucesso!</div>";
  } catch (PDOException $e) {
    $mensagem = "<div class='alert alert-danger'>Erro ao criar turma: " . $e->getMessage() . "</div>";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Home - MPI</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles/home.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="home.php">
        <img src="img/MPI.REDONDO.png" alt="Logo MPI">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="agenda.php">Agenda</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Configurações</a></li>
          <li class="nav-item"><a class="nav-link btn btn-light text-primary" href="scripts/logOff.php">Sair</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container turmas-container">
    <h2 class="text-center mb-4">Minhas Turmas</h2>

    <?= $mensagem; ?>

    <div class="mb-4 d-flex justify-content-between">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionarAluno">Adicionar Aluno</button>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCriarTurma">Criar Turma</button>
      <div>
        <label class="fw-bold me-2">Ordenar por:</label>
        <select class="form-select d-inline w-auto" onchange="ordenarTurmas(this.value)">
          <option value="alfabetico" <?= $ordenacao == 'alfabetico' ? 'selected' : ''; ?>>Nome (A → Z)</option>
          <option value="dia" <?= $ordenacao == 'dia' ? 'selected' : ''; ?>>Dia da Semana</option>
        </select>
      </div>
    </div>

    <div class="row justify-content-center">
      <?php if (count($turmas) > 0): ?>
        <?php foreach ($turmas as $turma): ?>
          <div class="col-md-6 mb-4">
            <div class="card text-center">
              <div class="card-body">
                <h5 class="card-title"><?= $turma['TurmaNome']; ?></h5>

                <div class="d-flex justify-content-around">
                  <div>
                    <h6 class="card-subtitle mb-2 text-muted">Alunos matriculados:</h6>
                    <?php
                    $sqlAlunos = "SELECT AluNome FROM Alunos WHERE TurmaId = ?";
                    $stmtAlunos = $conexao->prepare($sqlAlunos);
                    $stmtAlunos->execute([$turma['TurmaId']]);
                    $alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

                    if (count($alunos) > 0) {
                      foreach ($alunos as $aluno): ?>
                        <div class="d-flex align-items-center justify-content-center mb-2">
                          <span><?= $aluno['AluNome']; ?></span>
                        </div>
                    <?php endforeach;
                    } else {
                      echo "<p class='text-muted'>Nenhum aluno matriculado.</p>";
                    }
                    ?>
                  </div>
                  <div>
                    <h6 class="card-subtitle mb-2 text-muted">Horários:</h6>
                    <ul class="list-unstyled">
                      <?php
                      $diasSemana = explode(', ', $turma['DiasSemana']);
                      $horarios = json_decode($turma['Horarios'], true) ?: [];

                      if (!empty($diasSemana[0])) {
                        foreach ($diasSemana as $dia):
                          $horario = isset($horarios[$dia]) ? $horarios[$dia] : 'Horário não definido';
                      ?>
                          <li><strong><?= $dia; ?>:</strong> <?= $horario; ?></li>
                      <?php endforeach;
                      } else {
                        echo "<li class='text-muted'>Horários não definidos.</li>";
                      }
                      ?>
                    </ul>
                  </div>
                </div>

                <?php if (!empty($turma['TurmaReuniao'])): ?>
                  <div class="mt-3">
                    <a href="<?= htmlspecialchars($turma['TurmaReuniao']); ?>"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="btn btn-primary w-100">
                      Entrar na Reunião
                    </a>
                  </div>
                <?php endif; ?>

                <div class="mt-3">
                  <a href="editarTurma.php?turmaId=<?= $turma['TurmaId']; ?>" class="btn btn-warning btn-sm">Editar Turma</a>
                  <a href="registrarAula.php?turmaId=<?= $turma['TurmaId']; ?>" class="btn btn-success btn-sm">Registrar Aula</a>
                </div>

              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center text-muted">Você ainda não possui turmas cadastradas.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal para Criar Turma -->
  <div class="modal fade" id="modalCriarTurma" tabindex="-1" aria-labelledby="modalLabelTurma" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabelTurma">Criar Nova Turma</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nome da Turma:</label>
              <input type="text" class="form-control" name="TurmaNome" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Dias da Semana:</label>
              <?php
              $diasSemana = ["Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado", "Domingo"];
              foreach ($diasSemana as $dia): ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="DiasSemana[]" value="<?= $dia; ?>" onclick="toggleHorarios()">
                  <label class="form-check-label"><?= $dia; ?></label>
                  <input type="time" class="form-control mt-1" name="Horario_<?= $dia; ?>" style="display: none;">
                </div>
              <?php endforeach; ?>
            </div>

            <div class="mb-3">
              <label class="form-label">Link da Reunião (Opcional):</label>
              <input type="text" class="form-control" name="TurmaReuniao">
            </div>

            <div class="mb-3">
              <label class="form-label">Adicionar Alunos:</label>
              <?php if (!empty($alunosDisponiveis)): ?>
                <?php foreach ($alunosDisponiveis as $aluno): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="AlunosSelecionados[]" value="<?= $aluno['AluId']; ?>">
                    <label class="form-check-label"><?= $aluno['AluNome']; ?></label>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted">Nenhum aluno disponível.</p>
              <?php endif; ?>
            </div>

            <button type="submit" name="criarTurma" class="btn btn-success w-100">Criar Turma</button>
          </form>
        </div>
      </div>
    </div>
  </div>

     <!-- Modal para Adicionar Aluno -->
  <div class="modal fade" id="modalAdicionarAluno" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabel">Adicionar Novo Aluno</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nome:</label>
              <input type="text" class="form-control" name="AluNome" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Telefone:</label>
              <input type="text" class="form-control" name="AluTelefone" required>
            </div>
            <div class="mb-3">
              <label class="form-label">E-mail:</label>
              <input type="email" class="form-control" name="AluEmail" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Data de Nascimento:</label>
              <input type="date" class="form-control" name="AluNascimento" required>
            </div>
            <div class="mb-3">
              <label class="form-label">CPF:</label>
              <input type="text" class="form-control" name="AluCPF" required>
            </div>
            <button type="submit" name="adicionarAluno" class="btn btn-primary w-100">Cadastrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      function toggleHorarios() {
        const checkboxes = document.querySelectorAll("input[name='DiasSemana[]']");
        checkboxes.forEach(checkbox => {
          const horarioField = checkbox.parentElement.querySelector("input[type='time']");
          horarioField.style.display = checkbox.checked ? "block" : "none";
        });
      }

      function ordenarTurmas(criterio) {
        window.location.href = "home.php?ordenar=" + criterio;
      }
    </script>

</body>

</html>