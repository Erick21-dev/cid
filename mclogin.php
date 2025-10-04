<?php
include('conexao.PHP');

if(isset($_POST['email']) || isset($_POST['senha'])) {
  if (strlen($_POST['emai']) == 0) {
     echo "preencha seu email";
  } else if (strlen($_POST['senha'])== 0){
    echo "preenha sua senha";
  } else{
    $email = $mysqli->real_escape_string($_POST['email']);
    $senha = $mysqli->real_escape_string($_POST['senha']);
    $sql_code = "SELECT * FROM usuario WHERE email = '$email' AND senha = $senha";
    $sql_query = $mysqli - > query($sql_code) or die("falha na execução do codigo sql:". $sqli->error);

    $quantidade=$sql_query-> num_rows;

    if ($quantidade ==1 ) {
      $usuario = $sql_query-> fetch_assoc();
      
      if (!isset($_SESSION)) {
         session_start();
      }
      $_SESSION['id'] = $usuario['id'];
      $_SESSION['nome'] = $usuario['nome'];

      header('location: painel.php')
    } else {
      echo "incorreto";
    }
  }
}

?>


<!DOCTYPE html>
<html lang="pt-br">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
  <link rel="stylesheet" href="login.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barriecito&family=Delius&display=swap" rel="stylesheet">
    <title>login Natalie</title>
</head>
<body>
    <main class="container">
        <form action="" method="POST">
           <h1>acesse sua conta</h1> 
           <div class="input-box">
            <input placeholder="usuario" type="email">
            <i class="bx bxs-user"></i>
           </div>
           <div class="input-box">
            <input placeholder="senha" type="password">
            <i class="bx bxs-lock-alt"></i>
           </div>
           <div class="lembre">
            <label >
            <input type="checkbox"> lembrar-me
            </label>
            <a href="#">esqueci minha senha</a>
           </div>
           <button  type="submit" class="login">entrar</button>
           <div class="registre">
            <p>não tem uma conta? <a href="#">cadastre-se</a></p>
           </div>
        </form>
    </main>
</body>
</html>
