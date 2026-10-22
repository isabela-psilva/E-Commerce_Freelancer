<?php
    session_start();
?>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sobre | Freelancer</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header class="topbar">
            <nav>
                <?php if (isset($_SESSION["id_usuario"])) { ?>
                    <span>
                        Olá, <?php echo htmlspecialchars($_SESSION["nome_usuario"], ENT_QUOTES, "UTF-8"); ?>!
                    </span>
                    <a href="logout.php">SAIR</a>
                <?php } else { ?>
                    <a href="login.php">LOGIN</a>
                    <a href="cadastro.php">CADASTRO</a>
                <?php } ?>

                <a href="estoque.php">ESTOQUE</a>
                
            </nav>
            <a class="cart-link" href="carrinho.php">🛒 CARRINHO</a>
        </header>

        <div class="page-title">SOBRE A FREELANCER</div>

        <main class="content about-layout">
            <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=900&q=80" alt="Equipe trabalhando">

            <div>
                <h1>Conectamos talentos e oportunidades</h1>
                <p>A Freelancer é uma plataforma conceitual para aproximar pessoas que precisam de serviços criativos e digitais de profissionais independentes.</p>
                <p>Nosso objetivo é tornar a contratação simples, organizada e acessível.</p>
                <a class="btn" href="servicos.php">CONHECER SERVIÇOS</a>
            </div>
        </main>

        <footer>FREELANCER • Projeto acadêmico</footer>
    </body>
</html>

