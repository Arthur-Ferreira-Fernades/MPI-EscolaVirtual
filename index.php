<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - MPI</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles/index.css">
</head>
<body>

  <div class="login-container">
    <div class="logo-container">
      <img src="img/MPI.REDONDO.png" alt="Logo MPI">
    </div>

    <h2 class="text-center mb-4">Acesso ao Sistema</h2>
    <p class="text-center text-muted">
      Bem-vindo ao sistema de controle de alunos e aulas da escola.
    </p>
    <form method="POST" action = "scripts/ValidaLogin.php">
      <div class="mb-3">
        <label for="email" class="form-label">Usuário ou E-mail</label>
        <input 
          type="text" 
          class="form-control" 
          id="email" 
          name = "email"
          placeholder="Digite seu usuário ou e-mail"
          required
        >
      </div>
      <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input 
          type="password" 
          class="form-control" 
          id="senha" 
          name = "senha"
          placeholder="Digite sua senha"
          required
        >
      </div>
      <button type="submit" class="btn btn-primary w-100">
        Entrar
      </button>
    </form>
    <!-- Botão para Página de Cadastro -->
    <div class="text-center mt-3">
      <a href="cadastro.php" class="btn btn-secondary w-100">
        Cadastrar
      </a>
    </div>
  </div>
  </div>
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
  </script>
</body>
</html>
