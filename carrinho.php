<?php
    session_start();

    include "conexao.php";

    $mensagem = "";

    if (!isset($_SESSION["carrinho"])) {
        $_SESSION["carrinho"] = array();
    }

    if (isset($_POST["finalizar"])) {

        if (!isset($_SESSION["id_usuario"])) {
            header("Location: login.php");
            exit;
        }

        if (count($_SESSION["carrinho"]) == 0) {
            header("Location: carrinho.php");
            exit;
        }

        $id_usuario = intval($_SESSION["id_usuario"]);
        $itens_pedido = array();
        $total_pedido = 0;
        $erro = "";

        $transacao = mysql_query(
            "START TRANSACTION",
            $conecta_db
        );

        if (!$transacao) {
            $erro = "Não foi possível iniciar o pedido.";
        }

        if ($erro == "") {

            foreach ($_SESSION["carrinho"] as $id => $quantidade) {

                $id = intval($id);
                $quantidade = intval($quantidade);

                if ($id <= 0 || $quantidade <= 0) {
                    $erro = "Item ou quantidade inválida.";
                    break;
                }

                $consulta = mysql_query(
                    "SELECT id_servico, preco, quantidade
                     FROM servicos
                     WHERE id_servico = " . $id . "
                     AND ativo = 1
                     FOR UPDATE",
                    $conecta_db
                );

                if (!$consulta) {
                    $erro = "Erro ao consultar o serviço: "
                          . mysql_error();
                    break;
                }

                if (mysql_num_rows($consulta) == 0) {
                    $erro = "Um dos serviços não está mais disponível.";
                    break;
                }

                $servico = mysql_fetch_assoc($consulta);

                if ($quantidade > intval($servico["quantidade"])) {
                    $erro = "Estoque insuficiente para um dos serviços.";
                    break;
                }

                $preco = floatval($servico["preco"]);

                $total_pedido += $preco * $quantidade;

                $itens_pedido[] = array(
                    "id_servico" => $id,
                    "quantidade" => $quantidade,
                    "preco" => $preco
                );
            }
        }

        if ($erro == "") {

            $total_sql = number_format(
                $total_pedido,
                2,
                ".",
                ""
            );

            $inserir = mysql_query(
                "INSERT INTO pedidos
                 (id_usuario, total, status)
                 VALUES (
                    " . $id_usuario . ",
                    " . $total_sql . ",
                    'Pendente'
                 )",
                $conecta_db
            );

            if (!$inserir) {
                $erro = "Erro ao registrar o pedido: "
                      . mysql_error();
            }
        }


        if ($erro == "") {

            $id_pedido = mysql_insert_id($conecta_db);

            if ($id_pedido <= 0) {
                $erro = "Não foi possível obter o número do pedido.";
            }
        }

        if ($erro == "") {

            foreach ($itens_pedido as $item) {

                $id_servico = $item["id_servico"];
                $quantidade = $item["quantidade"];

                $preco = number_format(
                    $item["preco"],
                    2,
                    ".",
                    ""
                );

                $inserir_item = mysql_query(
                    "INSERT INTO itens_pedidos
                     (id_pedido, id_servico, quantidade, preco_unitario)
                     VALUES (
                        " . $id_pedido . ",
                        " . $id_servico . ",
                        " . $quantidade . ",
                        " . $preco . "
                     )",
                    $conecta_db
                );

                if (!$inserir_item) {
                    $erro = "Erro ao registrar os itens do pedido: "
                          . mysql_error();
                    break;
                }

                $atualizar_estoque = mysql_query(
                    "UPDATE servicos
                     SET quantidade = quantidade - " . $quantidade . "
                     WHERE id_servico = " . $id_servico . "
                     AND ativo = 1
                     AND quantidade >= " . $quantidade,
                    $conecta_db
                );

                if (
                    !$atualizar_estoque ||
                    mysql_affected_rows($conecta_db) != 1
                ) {
                    $erro = "Erro ao atualizar o estoque. "
                          . "Verifique a quantidade disponível.";

                    break;
                }
            }
        }

        if ($erro == "") {

            if (mysql_query("COMMIT", $conecta_db)) {

                $_SESSION["carrinho"] = array();

                header(
                    "Location: carrinho.php?pedido="
                    . $id_pedido
                );

                exit;

            } else {
                $erro = "Erro ao confirmar o pedido: "
                      . mysql_error();
            }
        }

        mysql_query("ROLLBACK", $conecta_db);

        $mensagem = $erro;
    }


    if (isset($_POST["id_servico"])) {

        $id_servico = intval($_POST["id_servico"]);

        $quantidade = isset($_POST["quantidade"])
            ? intval($_POST["quantidade"])
            : 1;

        $consulta = mysql_query(
            "SELECT id_servico, quantidade
             FROM servicos
             WHERE id_servico = " . $id_servico . "
             AND ativo = 1",
            $conecta_db
        );

        if ($consulta && mysql_num_rows($consulta) > 0) {

            $servico = mysql_fetch_assoc($consulta);

            $quantidade_atual = isset(
                $_SESSION["carrinho"][$id_servico]
            )
                ? $_SESSION["carrinho"][$id_servico]
                : 0;

            $nova_quantidade = $quantidade_atual + $quantidade;

            if ($quantidade < 1) {

                $mensagem = "Quantidade inválida.";

            } elseif (
                $nova_quantidade > intval($servico["quantidade"])
            ) {

                $mensagem = "Quantidade maior que o estoque disponível.";

            } else {

                $_SESSION["carrinho"][$id_servico] = $nova_quantidade;

                header("Location: carrinho.php?adicionado=1");
                exit;
            }

        } else {

            $mensagem = "Serviço não encontrado ou indisponível.";
        }
    }


    if (isset($_POST["atualizar"])) {

        if (
            isset($_POST["quantidades"]) &&
            is_array($_POST["quantidades"])
        ) {

            foreach ($_POST["quantidades"] as $id => $qtd) {

                $id = intval($id);
                $qtd = intval($qtd);

                if (!isset($_SESSION["carrinho"][$id])) {
                    continue;
                }

                $consulta = mysql_query(
                    "SELECT quantidade
                     FROM servicos
                     WHERE id_servico = " . $id . "
                     AND ativo = 1",
                    $conecta_db
                );

                if ($consulta && mysql_num_rows($consulta) > 0) {

                    $dados = mysql_fetch_assoc($consulta);
                    $estoque = intval($dados["quantidade"]);

                    if ($estoque < 1) {
                        unset($_SESSION["carrinho"][$id]);
                        continue;
                    }

                    if ($qtd < 1) {
                        $qtd = 1;
                    }

                    if ($qtd > $estoque) {
                        $qtd = $estoque;
                    }

                    $_SESSION["carrinho"][$id] = $qtd;

                } else {

                    unset($_SESSION["carrinho"][$id]);
                }
            }
        }

        header("Location: carrinho.php?atualizado=1");
        exit;
    }


    if (isset($_POST["remover"])) {

        $id_remover = intval($_POST["remover"]);

        if (isset($_SESSION["carrinho"][$id_remover])) {
            unset($_SESSION["carrinho"][$id_remover]);
        }

        header("Location: carrinho.php?removido=1");
        exit;
    }


    if (isset($_GET["adicionado"])) {

        $mensagem = "Serviço adicionado ao carrinho!";

    } elseif (isset($_GET["atualizado"])) {

        $mensagem = "Carrinho atualizado!";

    } elseif (isset($_GET["removido"])) {

        $mensagem = "Serviço removido do carrinho!";

    } elseif (isset($_GET["pedido"])) {

        $mensagem = "Pedido realizado com sucesso!";
    }

    $itens = array();
    $total = 0;

    foreach ($_SESSION["carrinho"] as $id => $quantidade) {

        $id = intval($id);

        $consulta = mysql_query(
            "SELECT
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
             WHERE s.id_servico = " . $id . "
             AND s.ativo = 1",
            $conecta_db
        );

        if ($consulta && mysql_num_rows($consulta) > 0) {

            $servico = mysql_fetch_assoc($consulta);

            $qtd = intval($quantidade);
            $estoque = intval($servico["quantidade"]);

            if ($estoque < 1) {
                unset($_SESSION["carrinho"][$id]);
                continue;
            }

            if ($qtd < 1) {
                $qtd = 1;
            }

            if ($qtd > $estoque) {
                $qtd = $estoque;
                $_SESSION["carrinho"][$id] = $qtd;
            }

            $servico["quantidade_carrinho"] = $qtd;

            $servico["subtotal"] =
                floatval($servico["preco"]) * $qtd;

            $total += $servico["subtotal"];

            $itens[] = $servico;

        } else {

            unset($_SESSION["carrinho"][$id]);
        }
    }
?>

<html lang="pt-br">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Carrinho | Freelancer</title>

    <link rel="stylesheet" href="style.css">
  </head>

  <body>

    <header class="topbar">
        <nav>
            <a href="index.php">HOME</a>
            <a href="servicos.php">SERVIÇOS</a>
        </nav>

        <a class="cart-link" href="carrinho.php">🛒 CARRINHO</a>
    </header>

    <div class="page-title">CARRINHO DE COMPRAS</div>

    <main class="content">

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

        <?php if (count($itens) == 0) { ?>

            <div class="cart-empty">
                <div class="cart-icon">🛒</div>

                <h1>Seu carrinho está vazio</h1>

                <p>
                    Explore nossos serviços e adicione o que você precisa.
                </p>

                <a class="btn" href="servicos.php">VER SERVIÇOS</a>
            </div>

        <?php } else { ?>

            <section class="cart-preview">

                <h2>Seus serviços</h2>

                <form action="carrinho.php" method="POST">

                    <?php foreach ($itens as $item) { ?>

                        <article class="cart-item">

                            <?php
                                if ($item["categoria"] == "Ilustração") {

                                    $imagem = "https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=400&q=80";

                                } elseif ($item["categoria"] == "Escrita") {

                                    $imagem = "https://images.stockcake.com/public/c/1/7/c17f0fca-25cd-4bdc-955e-d37e64e754ea/digital-writing-setup-stockcake.jpg";

                                } else {

                                    $imagem = "https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=400&q=80";
                                }
                            ?>

                            <img
                                src="<?php echo htmlspecialchars($imagem, ENT_QUOTES, "UTF-8"); ?>"
                                alt="<?php echo htmlspecialchars($item["categoria"], ENT_QUOTES, "UTF-8"); ?>"
                            >

                            <div>

                                <h3>
                                    <?php
                                        echo htmlspecialchars(
                                            $item["nome_servico"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    ?>
                                </h3>

                                <p>
                                    <?php
                                        echo htmlspecialchars(
                                            $item["descricao"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    ?>
                                </p>

                                <p>
                                    Preço unitário:
                                    R$ <?php
                                        echo number_format(
                                            $item["preco"],
                                            2,
                                            ",",
                                            "."
                                        );
                                    ?>
                                </p>

                                <label for="quantidade_<?php echo $item["id_servico"]; ?>">
                                    Quantidade:
                                </label>

                                <input
                                    type="number"
                                    id="quantidade_<?php echo $item["id_servico"]; ?>"
                                    name="quantidades[<?php echo $item["id_servico"]; ?>]"
                                    value="<?php echo $item["quantidade_carrinho"]; ?>"
                                    min="1"
                                    max="<?php echo $item["quantidade"]; ?>"
                                    required
                                >

                                <p>
                                    Subtotal:
                                    <strong>
                                        R$ <?php
                                            echo number_format(
                                                $item["subtotal"],
                                                2,
                                                ",",
                                                "."
                                            );
                                        ?>
                                    </strong>
                                </p>

                            </div>

                            <button
                                class="btn small muted-button"
                                type="submit"
                                name="remover"
                                value="<?php echo $item["id_servico"]; ?>"
                                formnovalidate
                            >
                                REMOVER
                            </button>

                        </article>

                    <?php } ?>

                    <button
                        class="btn small"
                        type="submit"
                        name="atualizar"
                        value="1"
                    >
                        ATUALIZAR QUANTIDADES
                    </button>

                </form>

                <div class="cart-total">

                    <strong>
                        Total:
                        R$ <?php
                            echo number_format(
                                $total,
                                2,
                                ",",
                                "."
                            );
                        ?>
                    </strong>

                    <form action="carrinho.php" method="POST">
                        <button
                            class="btn"
                            type="submit"
                            name="finalizar"
                            value="1"
                        >
                            FINALIZAR PEDIDO
                        </button>
                    </form>

                </div>

            </section>

        <?php } ?>

    </main>

    <footer>FREELANCER • Projeto acadêmico</footer>

  </body>
</html>
