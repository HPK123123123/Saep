<?php
include ("bd.php");
$mensagemErro = '';
$mensagemSucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_chamado'])) {
    $id_chamado = $_POST['id_chamado'];
    $stats_chamado = $_POST['stats_chamado'];
    $fk_id_tecnico = $_POST['fk_id_tecnico'];

    if (empty($stats_chamado) || empty($fk_id_tecnico)) {
        $mensagemErro = 'Todos os campos são obrigatórios!';
    } else {
        $stmt = $conn->prepare("UPDATE chamados SET stats_chamado = ?, fk_id_tecnico = ? WHERE id_chamado = ?");
        $stmt->bind_param("sii", $stats_chamado, $fk_id_tecnico, $id_chamado);

        if ($stmt->execute()) {
            $mensagemSucesso = 'Chamado atualizado com sucesso!';
        } else {
            $mensagemErro = 'Erro ao atualizar chamado: ' . $stmt->error;
        }

        $stmt->close();
    }
}

$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterCriticidade = isset($_GET['criticidade']) ? $_GET['criticidade'] : '';

$query = "SELECT c.id_chamado, c.descricao_chamado, c.stats_chamado, c.criticidade_chamado, c.data_abertura_chamado, 
                 cl.nome_cliente, t.nome_tecnico 
          FROM chamados c 
          JOIN cliente cl ON c.fk_id_cliente = cl.id_cliente
          LEFT JOIN tecnico t ON c.fk_id_tecnico = t.id_tecnico 
          WHERE 1";

if ($filterStatus) {
    $query .= " AND c.stats_chamado = '$filterStatus'";
}

if ($filterCriticidade) {
    $query .= " AND c.criticidade_chamado = '$filterCriticidade'";
}

$query .= " ORDER BY c.data_abertura_chamado DESC";

$result = $conn->query($query);

$tecnicos = $conn->query("SELECT id_tecnico, nome_tecnico FROM tecnico");

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Chamados</title>
 
</head>
<body>
<div class="container">
    <h2>Gerenciamento de Chamados</h2>
    <?php if ($mensagemErro): ?>
        <p class="message error"><?= $mensagemErro; ?></p>
    <?php elseif ($mensagemSucesso): ?>
        <p class="message success"><?= $mensagemSucesso; ?></p>
    <?php endif; ?>
    <div class="filters">
        <form method="GET" action="gerenciar_chamados.php">
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="">Todos</option>
                    <option value="Aberto" <?= ($filterStatus == 'Aberto') ? 'selected' : ''; ?>>Aberto</option>
                    <option value="Em andamento" <?= ($filterStatus == 'Em andamento') ? 'selected' : ''; ?>>Em andamento</option>
                    <option value="Resolvido" <?= ($filterStatus == 'Resolvido') ? 'selected' : ''; ?>>Resolvido</option>
                </select>
            </div>

            <div class="form-group">
                <label for="criticidade">Criticidade</label>
                <select name="criticidade" id="criticidade">
                    <option value="">Todas</option>
                    <option value="Baixo" <?= ($filterCriticidade == 'Baixo') ? 'selected' : ''; ?>>Baixo</option>
                    <option value="Média" <?= ($filterCriticidade == 'Média') ? 'selected' : ''; ?>>Média</option>
                    <option value="Alta" <?= ($filterCriticidade == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                </select>
            </div>

            <input type="submit" value="Filtrar">
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Status</th>
                <th>Criticidade</th>
                <th>Cliente</th>
                <th>Técnico Responsável</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($chamado = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $chamado['id_chamado']; ?></td>
                        <td><?= $chamado['descricao_chamado']; ?></td>
                        <td><?= $chamado['stats_chamado']; ?></td>
                        <td><?= $chamado['criticidade_chamado']; ?></td>
                        <td><?= $chamado['nome_cliente']; ?></td>
                        <td><?= $chamado['nome_tecnico'] ?: 'Não atribuído'; ?></td>
                        <td>
                            <form method="POST" action="gerenciar_chamados.php">
                                <input type="hidden" name="id_chamado" value="<?= $chamado['id_chamado']; ?>">
                                <select name="stats_chamado">
                                    <option value="Aberto" <?= ($chamado['stats_chamado'] == 'Aberto') ? 'selected' : ''; ?>>Aberto</option>
                                    <option value="Em andamento" <?= ($chamado['stats_chamado'] == 'Em andamento') ? 'selected' : ''; ?>>Em andamento</option>
                                    <option value="Resolvido" <?= ($chamado['stats_chamado'] == 'Resolvido') ? 'selected' : ''; ?>>Resolvido</option>
                                </select>
                                <select name="fk_id_tecnico">
                                    <option value="">Selecionar Técnico</option>
                                    <?php while ($tecnico = $tecnicos->fetch_assoc()): ?>
                                        <option value="<?= $tecnico['id_tecnico']; ?>" <?= ($tecnico['id_tecnico'] == $chamado['fk_id_tecnico']) ? 'selected' : ''; ?>>
                                            <?= $tecnico['nome_tecnico']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="submit" name="editar_chamado">Editar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">Nenhum chamado encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
