<?php
session_start();
require('scripts/conectaBanco.php');

if (!isset($_SESSION['usuario_validado']) || $_SESSION['usuario_validado'] !== true) {
    header("location: index.php?login=erro");
    exit();
}

$profId = $_SESSION['UsuarioId'];

// Obtém a semana atual ou a semana selecionada
// Obtém a semana atual ou a semana selecionada corretamente
$semanaOffset = isset($_GET['semana']) ? intval($_GET['semana']) : 0;

// Corrige o cálculo para garantir que a semana sempre inicie na segunda-feira
$dataInicioSemana = date("Y-m-d", strtotime("monday $semanaOffset week", strtotime("today")));
$dataFimSemana = date("Y-m-d", strtotime("$dataInicioSemana +6 days"));


// Tradução dos dias da semana para português
$diasSemana = ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"];

// Busca aulas dadas dentro da semana
$sqlAulasDadas = "
    SELECT A.DataAula, A.HoraInicio, A.Conteudo, T.TurmaNome, P.Presente
    FROM Aulas A
    JOIN Turmas T ON A.TurmaId = T.TurmaId
    LEFT JOIN Presencas P ON A.AulaId = P.AulaId
    WHERE T.ProfId = ? AND A.DataAula BETWEEN ? AND ?
    ORDER BY A.DataAula, A.HoraInicio";
$stmtAulasDadas = $conexao->prepare($sqlAulasDadas);
$stmtAulasDadas->execute([$profId, $dataInicioSemana, $dataFimSemana]);
$aulasDadas = $stmtAulasDadas->fetchAll(PDO::FETCH_ASSOC);

// Busca todas as turmas do professor para exibir as aulas programadas
$sqlTurmas = "SELECT TurmaId, TurmaNome, DiasSemana, Horarios FROM Turmas WHERE ProfId = ?";
$stmtTurmas = $conexao->prepare($sqlTurmas);
$stmtTurmas->execute([$profId]);
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

// Organiza as aulas futuras programadas
$aulasProgramadas = [];
foreach ($turmas as $turma) {
    $diasTurma = explode(', ', $turma['DiasSemana']);
    $horarios = json_decode($turma['Horarios'], true) ?: [];

    foreach ($diasTurma as $dia) {
        $indice = array_search($dia, $diasSemana);
        if ($indice !== false) {
            $dataAula = date("Y-m-d", strtotime("$dataInicioSemana +$indice days"));
            $horarioAula = isset($horarios[$dia]) ? $horarios[$dia] : 'Horário indefinido';
            $aulasProgramadas[] = [
                'DataAula' => $dataAula,
                'HoraInicio' => $horarioAula,
                'Conteudo' => 'Aula programada',
                'TurmaNome' => $turma['TurmaNome']
            ];
        }
    }
}

// Junta as aulas dadas com as programadas
$aulas = array_merge($aulasDadas, $aulasProgramadas);

// Ordena as aulas por data e horário
usort($aulas, function ($a, $b) {
    return strtotime($a['DataAula'] . ' ' . $a['HoraInicio']) - strtotime($b['DataAula'] . ' ' . $b['HoraInicio']);
});

// Organiza as aulas em um formato de agenda semanal
$agendaSemana = [];
foreach ($aulas as $aula) {
    $agendaSemana[$aula['DataAula']][] = $aula;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda - MPI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/agenda.css">
</head>
<body>

<!-- Navbar -->
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

<div class="container mt-5">
    <h2 class="text-center">Agenda Semanal</h2>

    <!-- Botões de navegação -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="?semana=<?= $semanaOffset - 1; ?>" class="btn btn-outline-primary">← Semana Anterior</a>
        <h5 class="text-center">
            <?= date("d/m/Y", strtotime($dataInicioSemana)) . " - " . date("d/m/Y", strtotime($dataFimSemana)); ?>
        </h5>
        <a href="?semana=<?= $semanaOffset + 1; ?>" class="btn btn-outline-primary">Próxima Semana →</a>
    </div>

    <!-- Grade da agenda estilo Google Calendar -->
    <div class="agenda-container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <?php foreach ($diasSemana as $dia): ?>
                        <th class="text-center"><?= $dia; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach ($diasSemana as $indice => $dia): ?>
                        <td class="agenda-dia">
                            <?php 
                            $data = date("Y-m-d", strtotime("$dataInicioSemana +$indice days"));
                            if (isset($agendaSemana[$data])): 
                                foreach ($agendaSemana[$data] as $aula): 
                                    // Define a cor de fundo com base na presença
                                    $classeCor = ($aula['Conteudo'] === 'Aula programada') ? 'bg-light text-muted' : 
                                                 (($aula['Presente'] == '0') ? 'bg-danger text-white' : 'bg-success text-white');
                                    ?>
                                    <div class="agenda-aula <?= $classeCor; ?>">
                                        <strong><?= $aula['HoraInicio']; ?> - <?= $aula['TurmaNome']; ?></strong><br>
                                        <?= $aula['Conteudo']; ?>
                                    </div>
                                <?php endforeach;
                            else:
                                echo "<p class='text-muted'>Sem aulas</p>";
                            endif;
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
