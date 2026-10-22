
<?php
    include "conexao.php";

    $mensagem = "";
    $tipo_mensagem = "";

    $nome = "";
    $email = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $nome = trim($_POST["nome"]);
        $email = trim($_POST["email"]);
        $senha = $_POST["senha"];

        if ($nome == "" || $email == "" || $senha == "") {

            $mensagem = "Preencha todos os campos.";
            $tipo_mensagem = "erro";

        } elseif (strlen($nome) > 100) {

            $mensagem = "O nome deve ter no máximo 100 caracteres.";
            $tipo_mensagem = "erro";

        } elseif (strlen($email) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $mensagem = "Informe um e-mail válido.";
            $tipo_mensagem = "erro";

        } elseif (strlen($senha) < 8) {

            $mensagem = "A senha deve ter pelo menos 8 caracteres.";
            $tipo_mensagem = "erro";

        } else {

            $nome_sql = mysql_real_escape_string($nome, $conecta_db);
            $email_sql = mysql_real_escape_string($email, $conecta_db);

            $consulta = mysql_query(
                "SELECT id_usuario
                 FROM usuarios
                 WHERE email = '" . $email_sql . "'
                 LIMIT 1",
                $conecta_db
            );

            if (!$consulta) {

                $mensagem = "Erro ao verificar o e-mail.";
                $tipo_mensagem = "erro";

            } elseif (mysql_num_rows($consulta) > 0) {

                $mensagem = "Este e-mail já está cadastrado.";
                $tipo_mensagem = "erro";

            } else {

                if (!function_exists("openssl_random_pseudo_bytes")) {

                    $mensagem = "Não foi possível gerar uma senha segura. Verifique a extensão OpenSSL do PHP.";
                    $tipo_mensagem = "erro";

                } else {

                    $forte = false;
                    $aleatorio = openssl_random_pseudo_bytes(16, $forte);

                    if ($aleatorio === false || !$forte) {

                        $mensagem = "Não foi possível gerar uma senha segura.";
                        $tipo_mensagem = "erro";

                    } else {

                        $salt = '$2y$10$' .
                            substr(
                                strtr(base64_encode($aleatorio), '+', '.'),
                                0,
                                22
                            );

                        $senha_hash = crypt($senha, $salt);

                        if (substr($senha_hash, 0, 4) != '$2y$') {

                            $mensagem = "O servidor não oferece suporte ao bcrypt. Verifique a versão do PHP.";
                            $tipo_mensagem = "erro";

                        } else {

                            $senha_sql = mysql_real_escape_string(
                                $senha_hash,
                                $conecta_db
                            );

                            $inserir = mysql_query(
                                "INSERT INTO usuarios (nome, email, senha)
                                 VALUES (
                                     '" . $nome_sql . "',
                                     '" . $email_sql . "',
                                     '" . $senha_sql . "'
                                 )",
                                $conecta_db
                            );

                            if ($inserir) {

                                header("Location: login.php?cadastro=sucesso");
                                exit;

                            } else {

                                $mensagem = "Erro ao realizar o cadastro.";
                                $tipo_mensagem = "erro";
                            }
                        }
                    }
                }
            }
        }
    }
?>

<html lang="pt-br">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | Freelancer</title>
    <link rel="stylesheet" href="style.css">
  </head>

  <body class="auth-page">

    <header class="topbar">
        <nav>
            <a href="index.php">HOME</a>
            <a href="login.php">LOGIN</a>
        </nav>
    </header>

    <div class="page-title">CADASTRO</div>

    <main class="auth-background">

        <form class="auth-card register-card" action="cadastro.php" method="POST">

            <h1>Crie sua conta</h1>

            <?php if ($mensagem != "") { ?>
                <p class="aviso">
                    <?php echo htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8"); ?>
                </p>
            <?php } ?>

            <label for="nome">Nome completo</label>
            <input id="nome" name="nome" type="text" maxlength="100" placeholder="Seu nome completo" value="<?php echo htmlspecialchars($nome, ENT_QUOTES, "UTF-8"); ?>" required>

            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" maxlength="200" placeholder="seuemail@exemplo.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, "UTF-8"); ?>" required>

            <label for="senha">Senha</label>
            <input id="senha" name="senha" type="password" minlength="8" placeholder="Crie uma senha com pelo menos 8 caracteres" required>

            <button class="btn" type="submit">CADASTRAR</button>

            <p>Já possui uma conta?
                <a href="login.php">Entrar</a>
            </p>

        </form>

    </main>

    <footer>FREELANCER • Projeto acadêmico</footer>

  </body>
</html>
