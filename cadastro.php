<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro - MPI</title>
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
  <link rel="stylesheet" href="styles/cadastro.css">
</head>
<body>

  <div class="cadastro-container">
    <!-- Logo -->
    <div class="logo-container">
      <img src="img/MPI.REDONDO.png" alt="Logo MPI">
    </div>

    <h2 class="text-center mb-4">Cadastro de Novo Usuário</h2>
    <p class="text-center text-muted">
      Preencha as informações para criar uma nova conta.
    </p>
    <form method="POST" action="scripts/ValidaRegistro.php" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="nome" class="form-label">Nome</label>
        <input 
          type="text" 
          class="form-control" 
          id="nome" 
          name = "nome"
          placeholder="Digite seu nome completo"
          required
        >
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input 
          type="email" 
          class="form-control" 
          id="email" 
          name = "email"
          placeholder="Digite seu e-mail"
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
      <div class="form-group">
            <label for="foto">Foto de Perfil (opcional):</label>
            <input type="file" class="form-control" id="foto" name="foto">
        </div>
      <button type="submit" class="btn btn-primary w-100">
        Cadastrar
      </button>
    </form>
    <div class="text-center mt-3">
      <a href="index.php" class="btn btn-link">Voltar para Login</a>
    </div>
  </div>
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
  </script>
</body>
</html>
