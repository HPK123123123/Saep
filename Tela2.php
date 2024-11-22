<?php
include ("bd.php");

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}


$mensagemErro = '';
$mensagemSucesso = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_cliente = $_POST['nome_cliente'];
    $email_cliente = $_POST['email_cliente'];
    $telefone_cliente = $_POST['telefone_cliente'];


    if (empty($nome_cliente) || empty($email_cliente) || empty($telefone_cliente)) {
        $mensagemErro = 'Todos os campos são obrigatórios!';
    }

    elseif (!filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
        $mensagemErro = 'E-mail inválido!';
    }
    else {

        $stmt = $conn->prepare("INSERT INTO cliente (nome_cliente, email_cliente, telefone_cliente) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome_cliente, $email_cliente, $telefone_cliente);

        if ($stmt->execute()) {
            $mensagemSucesso = 'Cliente cadastrado com sucesso!';
        } else {
            $mensagemErro = 'Erro ao cadastrar cliente: ' . $stmt->error;
        }

        $stmt->close();
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>
</head>
<body>

<h2>Cadastro de Cliente</h2>


<?php if ($mensagemErro): ?>
    <p style="color: red;"><?= $mensagemErro; ?></p>
<?php elseif ($mensagemSucesso): ?>
    <p style="color: green;"><?= $mensagemSucesso; ?></p>
<?php endif; ?>


<form method="POST" action="cadastro_cliente.php">
    <div>
        <label for="nome_cliente">Nome:</label>
        <input type="text" id="nome_cliente" name="nome_cliente" required>
    </div>
    <div>
        <label for="email_cliente">E-mail:</label>
        <input type="email" id="email_cliente" name="email_cliente" required>
    </div>
    <div>
        <label for="telefone_cliente">Telefone:</label>
        <input type="text" id="telefone_cliente" name="telefone_cliente" required>
    </div>
    <div>
        <button type="submit">Cadastrar</button>
    </div>
</form>

</body>
</html>