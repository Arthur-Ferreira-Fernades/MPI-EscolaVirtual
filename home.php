<?php
session_start();
require('scripts/conectaBanco.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_validado']) || $_SESSION['usuario_validado'] !== true) {
    header("location: index.php?login=erro");
    exit();
}

// Obtém o ID do professor logado
$profId = $_SESSION['UsuarioId'];

// Busca apenas as turmas do professor logado
$sql = "SELECT TurmaId, TurmaNome, DiasSemana, Horarios, TurmaReuniao FROM Turmas WHERE ProfId = ?";
$stmt = $conexao->prepare($sql);
$stmt->execute([$profId]);
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Home - MPI</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles/home.css"> <!-- Arquivo CSS separado -->
</head>
<body>

  <!-- Cabeçalho -->
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
          <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Configurações</a></li>
          <li class="nav-item"><a class="nav-link btn btn-light text-primary" href="scripts/logOff.php">Sair</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Conteúdo principal -->
  <div class="container turmas-container">
    <h2 class="text-center mb-4">Minhas Turmas</h2>

    <div class="row justify-content-center"> <!-- Centraliza os cards -->
      <?php if (count($turmas) > 0): ?>
        <?php foreach ($turmas as $turma): ?>
          <div class="col-md-6 mb-4">
            <div class="card text-center"> <!-- Centraliza o conteúdo do card -->
              <div class="card-body">
                <h5 class="card-title"><?= $turma['TurmaNome']; ?></h5>

                <div class="d-flex justify-content-around">
                  <!-- Alunos Matriculados -->
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

                  <!-- Dias e Horários -->
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

                <!-- Link da Reunião -->
                <?php if (!empty($turma['TurmaReuniao'])): ?>
                  <?php
                    // Garante que o link começa com "http://" ou "https://"
                    $linkReuniao = $turma['TurmaReuniao'];
                    if (!preg_match("~^(?:f|ht)tps?://~i", $linkReuniao)) {
                        $linkReuniao = "https://" . $linkReuniao;
                    }
                  ?>
                  <div class="mt-3">
                    <a href="<?= htmlspecialchars($linkReuniao); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn btn-primary w-100">
                      Entrar na Reunião
                    </a>
                  </div>
                <?php endif; ?>

                <!-- Botões de Ação -->
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

  <!-- Scripts do Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
