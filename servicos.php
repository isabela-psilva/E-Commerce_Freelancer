<?php
    include "conexao.php";

    $consulta = mysql_query("
        SELECT
            s.id_servico,
            s.descricao,
            s.preco,
            s.quantidade,
            ts.nome AS nome_servico,
            c.nome AS categoria
        FROM servicos s
        INNER JOIN tipos_servicos ts
            ON s.id_tipo_servico = ts.id_tipo_servico
        INNER JOIN categorias c
            ON ts.id_categoria = c.id_categoria
        WHERE s.ativo = 1
          AND s.quantidade > 0
        ORDER BY c.nome, ts.nome
    ", $conecta_db);

    if (!$consulta) {
        die("Erro ao buscar serviços: " . mysql_error());
    }
?>

<html lang="pt-br">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços | Freelancer</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>

    <header class="topbar">
        <nav>
            <a href="index.php">HOME</a>
            <a href="estoque.php">ESTOQUE</a>
        </nav>

        <a class="cart-link" href="carrinho.php">🛒 CARRINHO</a>
    </header>

    <div class="page-title">FREELANCER</div>

    <section class="catalog-banner">
        <img src="https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1500&q=80" alt="Espaço de trabalho">
        <div>
            <h1>ENCONTRE O SERVIÇO IDEAL</h1>
            <p>Profissionais preparados para tirar suas ideias do papel.</p>
        </div>
    </section>

    <main class="content">
        <h2 class="section-heading">NOSSOS SERVIÇOS</h2>

        <div class="service-grid">

            <?php
              if (mysql_num_rows($consulta) > 0) {

                while ($servico = mysql_fetch_assoc($consulta)) {

                  if ($servico["categoria"] == "Ilustração") {
                    $imagem = "https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=700&q=80";
                    $alt = "Serviço de ilustração";

                  } 
                  elseif ($servico["categoria"] == "Escrita") {
                    $imagem = "https://images.stockcake.com/public/c/1/7/c17f0fca-25cd-4bdc-955e-d37e64e754ea/digital-writing-setup-stockcake.jpg";
                    $alt = "Serviço de escrita";

                  } 
                  else {
                    $imagem = "https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=700&q=80";
                    $alt = "Serviço de programação";
                  }
            ?>

                  <article class="service-card">

                    <img src="<?php echo htmlspecialchars($imagem, ENT_QUOTES, "UTF-8"); ?>" alt="<?php echo htmlspecialchars($alt, ENT_QUOTES, "UTF-8"); ?>">

                    <div class="service-card-body">

                        <h3>
                            <?php
                              echo htmlspecialchars(
                                $servico["nome_servico"],
                                ENT_QUOTES,
                                "UTF-8"
                              );
                            ?>
                        </h3>

                        <p>
                            <?php
                              echo htmlspecialchars(
                                $servico["descricao"],
                                ENT_QUOTES,
                                "UTF-8"
                              );
                            ?>
                        </p>

                        <p>
                          Categoria:
                            <?php
                              echo htmlspecialchars(
                                $servico["categoria"],
                                ENT_QUOTES,
                                "UTF-8"
                              );
                            ?>
                        </p>

                        <p class="price">
                            R$
                            <?php
                              echo number_format(
                                $servico["preco"],
                                2,
                                ",",
                                "."
                              );
                            ?>
                        </p>

                        <form action="carrinho.php" method="POST">

                          <input type="hidden" name="id_servico" value="<?php echo intval($servico["id_servico"]); ?>">

                          <input type="hidden" name="servico" value="<?php echo htmlspecialchars($servico["nome_servico"], ENT_QUOTES, "UTF-8");?>">

                          <input type="hidden" name="price" value="<?php echo htmlspecialchars($servico["preco"], ENT_QUOTES, "UTF-8"); ?>">

                          <label for="quantidade_<?php echo intval($servico["id_servico"]); ?>">
                            Quantidade:
                          </label>

                            <input type="number" id="quantidade_<?php echo intval($servico["id_servico"]); ?>"name="quantidade" value="1" min="1" max="<?php echo intval($servico["quantidade"]); ?>"required>

                            <button class="btn small" type="submit">
                              ADICIONAR AO CARRINHO
                            </button>

                        </form>

                    </div>
                </article>

            <?php
                }

            } else {
                echo "<p>Nenhum serviço disponível no momento.</p>";
            }
            ?>

        </div>
    </main>

    <footer>FREELANCER • Projeto acadêmico</footer>

  </body>
</html>