
<?php
    session_start();

    include "conexao.php";

    $mensagem = "";
    $email = "";

    if (isset($_GET["cadastro"]) && $_GET["cadastro"] == "sucesso") {
        $mensagem = "Cadastro realizado com sucesso! Faça seu login.";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $email = trim($_POST["usuario"]);
        $senha = $_POST["senha"];

        if ($email == "" || $senha == "") {

            $mensagem = "Preencha o e-mail e a senha.";

        } else {

            $email_sql = mysql_real_escape_string($email, $conecta_db);

            $consulta = mysql_query(
                "SELECT id_usuario, nome, email, senha
                 FROM usuarios
                 WHERE email = '" . $email_sql . "'
                 LIMIT 1",
                $conecta_db
            );

            if (!$consulta) {

                $mensagem = "Erro ao consultar o banco de dados.";

            } elseif (mysql_num_rows($consulta) == 0) {

                $mensagem = "E-mail ou senha incorretos.";

            } else {

                $usuario = mysql_fetch_assoc($consulta);

                
                $senha_hash = crypt($senha, $usuario["senha"]);

                if ($senha_hash === $usuario["senha"]) {

                    
                    session_regenerate_id(true);

                    $_SESSION["id_usuario"] = $usuario["id_usuario"];
                    $_SESSION["nome_usuario"] = $usuario["nome"];
                    $_SESSION["email_usuario"] = $usuario["email"];

                    header("Location: index.php");
                    exit;

                } else {

                    $mensagem = "E-mail ou senha incorretos.";
                }
            }
        }
    }
?>

<html lang="pt-br">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Freelancer</title>
    <link rel="stylesheet" href="style.css">
  </head>

  <body class="auth-page">

    <header class="topbar">
        <nav>
            <a href="index.php">HOME</a>
        </nav>
    </header>

    <div class="page-title">LOGIN</div>

    <main class="auth-background">

        <form class="auth-card" action="login.php" method="POST">

            <h1>Bem-vindo de volta!</h1>

            <?php if ($mensagem != "") { ?>
                <p class="aviso">
                    <?php
                    echo htmlspecialchars(
                        $mensagem,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>
                </p>
            <?php } ?>

            <label for="usuario">E-mail</label>
            <input id="usuario" name="usuario" type="email" placeholder="Digite seu e-mail" value="<?php echo htmlspecialchars($email, ENT_QUOTES, "UTF-8"); ?>" required>

            <label for="senha">Senha</label>
            <input id="senha" name="senha" type="password" placeholder="Digite sua senha" required>

            <div class="login-actions">

                <button class="btn" type="submit">ENTRAR</button>

                <a href="cadastro.php">
                    Não tem conta?<br>Cadastre-se
                </a>

            </div>

        </form>

    </main>

    <footer>FREELANCER • Projeto acadêmico</footer>

  </body>
</html>

