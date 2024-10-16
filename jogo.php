<?php
session_start();

// Configurações do Campo Minado
$linhas = 15;
$colunas = 15;
$minas = 5;

// Inicializa o jogo na primeira vez ou quando o jogador clica em "Reiniciar"
if (!isset($_SESSION['tabuleiro']) || isset($_POST['reiniciar'])) {
    $_SESSION['tabuleiro'] = inicializarTabuleiro($linhas, $colunas, $minas);
    $_SESSION['visivel'] = array_fill(0, $linhas, array_fill(0, $colunas, false)); // Mantém controle das células reveladas
    $_SESSION['fim'] = false; // Marca se o jogo terminou (ganhou ou perdeu)
}

// Função para inicializar o tabuleiro
function inicializarTabuleiro($linhas, $colunas, $minas) {
    $tabuleiro = array();

    for ($i = 0; $i < $linhas; $i++) {
        for ($j = 0; $j < $colunas; $j++) {
            $tabuleiro[$i][$j] = 0;
        }
    }

    $minasRestantes = $minas;
    while ($minasRestantes > 0) {
        $linhaAleatoria = rand(0, $linhas - 1);
        $colunaAleatoria = rand(0, $colunas - 1);
        if ($tabuleiro[$linhaAleatoria][$colunaAleatoria] !== 'M') {
            $tabuleiro[$linhaAleatoria][$colunaAleatoria] = 'M';
            $minasRestantes--;
        }
    }

    for ($i = 0; $i < $linhas; $i++) {
        for ($j = 0; $j < $colunas; $j++) {
            if ($tabuleiro[$i][$j] !== 'M') {
                $tabuleiro[$i][$j] = contarMinasAdjacentes($tabuleiro, $i, $j, $linhas, $colunas);
            }
        }
    }

    return $tabuleiro;
}

// Função para contar minas adjacentes
function contarMinasAdjacentes($tabuleiro, $linha, $coluna, $linhas, $colunas) {
    $contagem = 0;
    for ($i = $linha - 1; $i <= $linha + 1; $i++) {
        for ($j = $coluna - 1; $j <= $coluna + 1; $j++) {
            if ($i >= 0 && $i < $linhas && $j >= 0 && $j < $colunas && $tabuleiro[$i][$j] === 'M') {
                $contagem++;
            }
        }
    }
    return $contagem;
}

// Função para revelar a célula
function revelarCelula($linha, $coluna) {
    global $linhas, $colunas;
    if ($_SESSION['visivel'][$linha][$coluna] || $_SESSION['fim']) {
        return;
    }
    $_SESSION['visivel'][$linha][$coluna] = true;

    if ($_SESSION['tabuleiro'][$linha][$coluna] === 0) {
        for ($i = $linha - 1; $i <= $linha + 1; $i++) {
            for ($j = $coluna - 1; $j <= $coluna + 1; $j++) {
                if ($i >= 0 && $i < $linhas && $j >= 0 && $j < $colunas) {
                    revelarCelula($i, $j);
                }
            }
        }
    }
}

// Função para verificar se o jogador venceu
function verificarVitoria() {
    global $linhas, $colunas, $minas;
    $célulasSeguras = $linhas * $colunas - $minas;

    $célulasReveladas = 0;
    for ($i = 0; $i < $linhas; $i++) {
        for ($j = 0; $j < $colunas; $j++) {
            if ($_SESSION['visivel'][$i][$j] && $_SESSION['tabuleiro'][$i][$j] !== 'M') {
                $célulasReveladas++;
            }
        }
    }

    if ($célulasReveladas === $célulasSeguras) {
        $_SESSION['fim'] = true;
        return true;
    }
    return false;
}

// Processa o clique do jogador
if (isset($_POST['linha']) && isset($_POST['coluna'])) {
    $linha = intval($_POST['linha']);
    $coluna = intval($_POST['coluna']);
    
    if ($_SESSION['tabuleiro'][$linha][$coluna] === 'M') {
        $mensagem = "Você perdeu! Clique em reiniciar para jogar novamente.";
        $_SESSION['fim'] = true;
    } else {
        revelarCelula($linha, $coluna);
        if (verificarVitoria()) {
            $mensagem = "Parabéns, você venceu!";
        }
    }
}

// Função para imprimir o tabuleiro com interatividade
function imprimirTabuleiro($tabuleiro, $linhas, $colunas) {
    echo "<form method='POST'>";
    echo "<table border='1' style='text-align:center'>";
    for ($i = 0; $i < $linhas; $i++) {
        echo "<tr>";
        for ($j = 0; $j < $colunas; $j++) {
            if ($_SESSION['visivel'][$i][$j]) {
                echo "<td style='width:30px;height:30px;'>" . ($_SESSION['tabuleiro'][$i][$j] === 'M' ? 'M' : $_SESSION['tabuleiro'][$i][$j]) . "</td>";
            } else {
                echo "<td style='width:30px;height:30px;'>
                    <button type='submit' name='linha' value='$i' style='width:100%;height:100%;'></button>
                    <input type='hidden' name='coluna' value='$j'>
                  </td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "<br><button type='submit' name='reiniciar'>Reiniciar</button>";
    echo "</form>";
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo Interativo de Campo Minado</title>
</head>
<body>
    <h1>Jogo de Campo Minado</h1>
    <?php
    if (isset($mensagem)) {
        echo "<p style='color:red; font-weight:bold;'>$mensagem</p>";
    }
    imprimirTabuleiro($_SESSION['tabuleiro'], $linhas, $colunas);
    ?>
</body>
</html>
