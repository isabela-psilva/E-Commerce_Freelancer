
<?php
    include "conexao.php";

    $mensagem = "";

    if (isset($_GET["excluir"])) {

        $id_servico = intval($_GET["excluir"]);

        if ($id_servico > 0) {

            $excluir = mysql_query(
                "DELETE FROM servicos
                 WHERE id_servico = " . $id_servico,
                $conecta_db
            );

            if ($excluir) {
                header("Location: estoque.php?excluido=1");
                exit;
            } else {
                $mensagem = "Erro ao excluir: " . mysql_error();
            }

        } else {
            $mensagem = "Servico invalido.";
        }
    }

    if (isset($_GET["excluido"])) {
        $mensagem = "Servico excluido com sucesso!";
    }

    if (isset($_POST["cadastrar"])) {

        $id_tipo_servico = isset($_POST["id_tipo_servico"])
            ? intval($_POST["id_tipo_servico"])
            : 0;

        $descricao = isset($_POST["descricao"])
            ? trim($_POST["descricao"])
            : "";

        $preco = isset($_POST["preco"])
            ? str_replace(",", ".", trim($_POST["preco"]))
            : "";

        $quantidade_texto = isset($_POST["quantidade"])
            ? trim($_POST["quantidade"])
            : "";

        $consulta_tipo = mysql_query(
            "SELECT id_tipo_servico
             FROM tipos_servicos
             WHERE id_tipo_servico = " . $id_tipo_servico,
            $conecta_db
        );

        if (!$consulta_tipo) {
            die("Erro ao consultar o tipo de servico: " . mysql_error());
        }

        if (
            $id_tipo_servico <= 0 ||
            mysql_num_rows($consulta_tipo) == 0 ||
            !is_numeric($preco) ||
            $preco < 0 ||
            !ctype_digit($quantidade_texto)
        ) {
            $mensagem = "Confira o tipo de servico, o preco e a quantidade.";
        } else {

            $descricao_sql = mysql_real_escape_string(
                $descricao,
                $conecta_db
            );

            $preco_sql = floatval($preco);
            $quantidade = intval($quantidade_texto);

            $inserir = mysql_query(
                "INSERT INTO servicos
                (id_tipo_servico, descricao, preco, quantidade, ativo)
                VALUES (
                    " . $id_tipo_servico . ",
                    '" . $descricao_sql . "',
                    " . $preco_sql . ",
                    " . $quantidade . ",
                    1
                )",
                $conecta_db
            );

            if ($inserir) {
                header("Location: estoque.php?sucesso=1");
                exit;
            } else {
                $mensagem = "Erro ao cadastrar: " . mysql_error();
            }
        }
    }

    if (isset($_GET["sucesso"])) {
        $mensagem = "Servico cadastrado com sucesso!";
    }

    $consulta_tipos = mysql_query(
        "SELECT ts.id_tipo_servico AS id,
                ts.nome,
                c.nome AS categoria
         FROM tipos_servicos ts
         INNER JOIN categorias c
         ON ts.id_categoria = c.id_categoria
         ORDER BY c.nome, ts.nome",
        $conecta_db
    );

    if (!$consulta_tipos) {
        die("Erro ao consultar tipos de servico: " . mysql_error());
    }

    $consulta_servicos = mysql_query(
        "SELECT s.id_servico AS id,
                ts.nome AS nome_servico,
                c.nome AS categoria,
                s.preco,
                s.quantidade,
                s.descricao
         FROM servicos s
         INNER JOIN tipos_servicos ts
         ON s.id_tipo_servico = ts.id_tipo_servico
         INNER JOIN categorias c
         ON ts.id_categoria = c.id_categoria
         ORDER BY s.id_servico DESC",
        $conecta_db
    );

    if (!$consulta_servicos) {
        die("Erro ao consultar servicos: " . mysql_error());
    }

    $totalServicos = mysql_num_rows($consulta_servicos);
    $disponiveis = 0;
    $esgotados = 0;

    while ($servico = mysql_fetch_assoc($consulta_servicos)) {
        if ($servico["quantidade"] > 0) {
            $disponiveis++;
        } else {
            $esgotados++;
        }
    }

    $consulta_servicos = mysql_query(
        "SELECT s.id_servico AS id,
                ts.nome AS nome_servico,
                c.nome AS categoria,
                s.preco,
                s.quantidade,
                s.descricao
         FROM servicos s
         INNER JOIN tipos_servicos ts
         ON s.id_tipo_servico = ts.id_tipo_servico
         INNER JOIN categorias c
         ON ts.id_categoria = c.id_categoria
         ORDER BY s.id_servico DESC",
        $conecta_db
    );

    if (!$consulta_servicos) {
        die("Erro ao carregar a tabela: " . mysql_error());
    }
?>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gerenciar Estoque | Freelancer</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>

        <header class="topbar">
            <nav>
                <a href="index.php">INÍCIO</a>
                <a href="servicos.php">SERVIÇOS</a>
            </nav>

            <a class="cart-link" href="carrinho.php">🛒 CARRINHO</a>
        </header>

        <main class="estoque-container">

            <div class="estoque-topo">
                <div>
                    <h1>Gerenciar estoque</h1>
                    <p>Consulte a quantidade disponível de cada serviço.</p>
                </div>

                <button class="btn-adicionar" type="button" onclick="window.location.href='estoque.php?adicionar=1'">
                    + Adicionar serviço
                </button>
            </div>

            <?php if ($mensagem != "") { ?>
                <p>
                    <?php
                        echo htmlspecialchars(
                        $mensagem,
                        ENT_QUOTES,
                        "UTF-8"
                        );
                    ?>
                </p>
            <?php } ?>

            <?php if (isset($_GET["adicionar"])) { ?>

                <section style="margin: 25px 0; padding: 20px; background: #fff; border-radius: 10px;">

                    <h2>Adicionar serviço</h2>

                    <form method="post" action="estoque.php">

                        <p>
                            <label for="id_tipo_servico">Tipo de serviço:</label><br>

                            <select name="id_tipo_servico" id="id_tipo_servico" required style="width: 100%; max-width: 450px; padding: 10px; margin-top: 5px;">

                                <option value="">Selecione um serviço</option>

                                <?php
                                    while ($tipo = mysql_fetch_assoc($consulta_tipos)) {
                                ?>
                                <option value="<?php echo $tipo["id"]; ?>">
                                    <?php
                                        echo htmlspecialchars(
                                        $tipo["nome"] . " - " . $tipo["categoria"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                        );
                                    ?>
                                </option>
                                <?php } ?>

                            </select>
                        </p>

                        <p>
                            <label for="descricao">Descrição:</label><br>
                            <textarea name="descricao" id="descricao" rows="3" style="width: 100%; max-width: 450px; padding: 10px;"></textarea>
                        </p>

                        <p>
                            <label for="preco">Preço (R$):</label><br>
                            <input type="text" name="preco" id="preco" placeholder="Ex.: 150,00" required style="padding: 10px; width: 200px;">
                        </p>

                        <p>
                            <label for="quantidade">Quantidade:</label><br>
                            <input type="number" name="quantidade" id="quantidade" min="0" step="1" required style="padding: 10px; width: 200px;">
                        </p>

                        <button class="btn-adicionar" type="submit" name="cadastrar">Cadastrar serviço</button>

                        <a href="estoque.php" style="margin-left: 10px;">
                            Cancelar
                        </a>

                    </form>
                </section>

            <?php } ?>

            <section class="estoque-resumo">

                <div class="resumo-card">
                    <h3>Total de serviços</h3>
                    <strong><?php echo $totalServicos; ?></strong>
                </div>

                <div class="resumo-card">
                    <h3>Disponíveis</h3>
                    <strong><?php echo $disponiveis; ?></strong>
                </div>

                <div class="resumo-card">
                    <h3>Sem estoque</h3>
                    <strong><?php echo $esgotados; ?></strong>
                </div>

            </section>

            <div class="tabela-responsiva">
                <table class="estoque-tabela">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Serviço</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Quantidade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($totalServicos == 0) { ?>
                            <tr>
                                <td colspan="7">Nenhum serviço cadastrado.</td>
                            </tr>
                        <?php } ?>

                        <?php while ($servico = mysql_fetch_assoc($consulta_servicos)) { ?>

                            <tr>
                                <td><?php echo $servico["id"]; ?></td>

                                <td>
                                    <?php
                                        echo htmlspecialchars(
                                        $servico["nome_servico"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                        );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                        echo htmlspecialchars(
                                        $servico["categoria"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                        );
                                    ?>
                                </td>

                                <td>
                                    R$ <?php echo number_format(
                                        $servico["preco"],
                                        2,
                                        ",",
                                        "."
                                    ); ?>
                                </td>

                                <td class="quantidade">
                                    <?php echo $servico["quantidade"]; ?>
                                </td>

                                <td>
                                    <?php if ($servico["quantidade"] > 0) { ?>
                                        <span class="status status-disponivel">
                                            Disponível
                                        </span>
                                    <?php } 
                                    else { ?>
                                        <span class="status status-esgotado">
                                            Esgotado
                                        </span>
                                    <?php } ?>
                                </td>

                                <td class="acoes">

                                    <a href="estoque.php?excluir=<?php echo $servico["id"]; ?>"onclick="return confirm('Tem certeza que deseja excluir este serviço?');">
                                        Excluir
                                    </a>
                                </td>
                            </tr>

                        <?php } ?>

                    </tbody>
                </table>
            </div>

            <div class="aviso">
                <strong>Observação:</strong> os serviços exibidos agora são
                consultados no banco de dados. O cadastro também salva os dados
                na tabela servicos.
            </div>

        </main>

    </body>
</html>
