<?php

/*
  Classe responsável por gerar um tabuleiro de Sudoku válido,
 * criar as operações de inserção e consulta deste no banco de dados
 * garantir uma solução única e exibir ao jogador um tabuleiro jogável,
 * bem como guardar os melhores resultados  
 */

class Controle {

    private $tabuleiro;
    private $cont = 0; //contador para o número de soluções possiveis 

    public function __construct() {
        $this->tabuleiro = $this->gerarSudoku();
    }

    /*
     * Este método varre a matriz e verifica se o tabuleiro
     * está completo, ou seja, sem zeros nas células
     */

    function estaCheio($grid) {
        for ($i = 0; $i < 9; $i++) {
            for ($j = 0; $j < 9; $j++) {
                if ($grid[$i][$j] == 0) {
                    return false;
                }
            }
        }
        return true;
    }

    /* Verifica os candidatos à ocupar uma determinada 
     * posição passada no tabuleiro. Inicialmente todos são
     * candidatos. Na medida que as verificações são feitas
     * vão sendo eliminados os que não atendem aos critérios
     */

    function vetorDePossibilidades($grid, $lin, $col) {
        // array com todos os possiveis candidatos
        $possibilidades = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
        // verificação horizontal
        for ($i = 1; $i <= 9; $i++) {
            for ($c = 0; $c < 9; $c++) {
                if ($grid[$lin][$c] == $i) {
                    $possibilidades[$i - 1] = 0;
                }
            }
        }
        // verificação vertical
        for ($i = 1; $i <= 9; $i++) {
            for ($l = 0; $l < 9; $l++) {
                if ($grid[$l][$col] == $i) {
                    $possibilidades[$i - 1] = 0;
                }
            }
        }

        $lr = (int) ($lin / 3);
        $cr = (int) ($col / 3);

        // verifica a região 3x3
        for ($n = 1; $n <= 9; $n++) {
            for ($i = ($lr * 3); $i < ($lr + 1) * 3; $i++) {
                for ($j = $cr * 3; $j < ($cr + 1) * 3; $j++) {
                    if ($grid[$i][$j] == $n) {
                        $possibilidades[$n - 1] = 0;
                    }
                }
            }
        }return $possibilidades;
    }

    /*
     * O método recebe um tabuleiro parcialmente preenchido
     * e o preenche com todas as soluções possíveis e
     * estas vão sendo contabilizadas para ganrantir 
     * que o tabuleiro à ser exibido ao jogador tenha apenas 
     * uma solução única */

    function resolve($grid) {
        // variaveis que guardam as coordenadas das células
        // à serem preenchidas
        $linha = 0;
        $coluna = 0;

        if ($this->estaCheio($grid)) {
            // Nesse ponto o tabuleiro passado foi resolvido
            // e o contador de soluções é incrementado
            $this->cont++;
            return;
        } else {

            //Busca a primeira célula vazia 
            for ($i = 0; $i < 9; $i++) {
                $achou = false;
                for ($j = 0; $j < 9; $j++) {
                    if ($grid[$i][$j] == 0) {
                        $linha = $i;
                        $coluna = $j;
                        $achou = true;
                        break;
                    }
                }
                if ($achou) {
                    break;
                }
            }
            // Após a célula ser encontrada, ela é passada para o método
            // responsável por retornar os candidatos para aquela posição.
            $possibilidades = $this->vetorDePossibilidades($grid, $linha, $coluna);

            // Todas as possibilidades são verificadas recursivamente
            foreach ($possibilidades as $p) {
                if (!$p == 0) {
                    $grid[$linha][$coluna] = $p;
                    $this->resolve($grid);
                }
            }
            // Backtracking
        }$grid[$linha][$coluna] = 0;
    }

    /*
     * Método responsável por exibir visualmente
     * um grid ao jagador
     */

    public function criarGrid() {
        for ($i = 1; $i <= 9; $i++) {
            echo "<table border=3 class='tabela_fixa'>";
            for ($k = 1; $k <= 3; $k++) {
                echo "<tr class='linha'>";
                for ($j = 1; $j <= 3; $j++) {
                    echo"<td class='cel'></td>";
                }
                echo "</tr>";
            }
            echo"</table>";
        }
    }

    /*
     * Metodo responsável por armazenar o tabuleiro gerado
     * no banco de dados
     */

    public function guardarTabela($t) {
        $link = $this->getConexao();
        $tab = json_encode($t);
        $sql = "INSERT INTO tabuleiros(matriz) VALUES('$tab')";
        $result = mysqli_query($link, $sql);
        if (!$result) {
            die('erro' . mysqli . error($link));
        }
        mysqli_close($link);
    }

    /*
     * Método responsável por verificar
     * se o tabuleiro preenchido pelo jogador
     * encontra correspondência no banco de dados 
     * e garantir que novos tabuleiros gerados não 
     * se repitam no BD
     */

    public function existeNoBD($t) {
        $link = $this->getConexao();
        $mat = json_encode($t);
        $sql = "SELECT *FROM tabuleiros WHERE matriz = '$mat' ";
        $result = mysqli_query($link, $sql);

        $n_tab = mysqli_num_rows($result);
        mysqli_close($link);
        if ($n_tab > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Este é o método que gera os Sudokos válidos,
     * podendo gerar 6 tipos diferentes de tabuleiros completos
     */

    private function gerarSudoku() {
        $n = 3;
        $op = rand(1, 8); // opção entre um dos seis tabuleiros possíveis
        $mat = array(); // ìnicio da construção da matriz
        // a opção não pode ser um multiplo de 3
        $c = $op % 3 != 0 ? $op : 1;
        for ($i = 0; $i < 9; $i++) {
            $new = array();
            for ($j = 0; $j < 9; $j++) {
                $v = ($i * $n + (int) ($i / $n) + $j) * $c % 9 + 1;
                $new[] = (string)$v;
            }
            $mat[] = $new;
        }

        // Se o tabuleiro gerado não existir no banco de dados,
        // ele será armazenado. Como o algoritmo
        // gera apenas seis tipos diferentes de tabuleiros,
        // só existirão seis no banco.
        if (!$this->existeNoBD($mat)) {
            $this->guardarTabela($mat);
        }
        return $mat;
    }

    /*
     * Método responsável por exibir ao jogador uma
     * mescla de números e campos vázios editáveis
     */

    public function exibirSudoku() {
        echo "<div id = 'tab'><table class='tabela' style='width: 600px;
	height: 600px;'>";

        // Enquanto a solução se mantém única
        // uma célula é marcada aleatoriamente com zero.
        while ($this->cont <= 1) {
            $i = rand(0, 8);
            $j = rand(0, 8);
            $n = $this->tabuleiro[$i][$j];
            $this->tabuleiro[$i][$j] = 0;
            $this->resolve($this->tabuleiro);
            if ($this->cont > 1) {
                $this->tabuleiro[$i][$j] = $n;
            } else {
                $this->cont = 0;
            }
        }

        // As células marcadas com zero serão os campos vázios editáveis
        for ($i = 0; $i < 9; $i++) {
            echo "<tr class='linha'>";
            for ($j = 0; $j < 9; $j++) {
                $p = $this->tabuleiro[$i][$j];
                if ($p == 0) {
                    echo '<td class="cel"><input type="text" class ="campo c1" onClick= "validaEntrada()"></td>';
                } else {
                    echo '<td class="cel"><input type="text" class="campo" value=' . $p . ' readonly></td>';
                }
            }
            echo "</tr>";
        }
        echo"</table></div>";
    }

    /*
     * Metodo responsável por fazer a conexão com 
     * o Banco de Dados.
     */

    private function getConexao() {
        $servidor = 'localhost';
        $usuario = 'root';
        $senha = '';
        $banco = 'db_sudoku';
        $con = mysqli_connect($servidor, $usuario, $senha, $banco) 
                or die('Não foi possivel conectar' . mysqli_error($con));
        return $con;
    }

    /*
     * Este método tem por finalidade fazer a conexao com o BD
     * para avaliar se o tabuleiro
     * preenchido pelo jogador existe no banco e,
     * desta forma, verificar se sua resposta
     * está correta ou não e se os seu tempo é o melhor para 
     * aquele tabuleiro em caso de acerto.
     */

    public function verificaTabJogador($t, $tempo) {
        $link = $this->getConexao();
        if ($this->existeNoBD($t)) {
            $mat = json_encode($t);
            $result = mysqli_query($link, "SELECT tempo FROM tabuleiros WHERE matriz = '$mat' ");
            $res = mysqli_fetch_array($result);
            if (!$res['tempo']) {
                $recorde = date('H:i:s', $tempo);
                mysqli_query($link, "UPDATE tabuleiros SET tempo ='$recorde' WHERE matriz = '$mat'");
                echo"<h1><center style='margin:20px auto;color:red'>Melhor tempo: $recorde</center></h1>";
            } else if ($tempo < strtotime($res['tempo'])) {
                $recorde = date('H:i:s', $tempo);
                mysqli_query($link, "UPDATE tabuleiros SET tempo ='$recorde' WHERE matriz = '$mat'");
                echo"<h2><center style='margin:20px auto;>Novo recorde : $recorde</center></h2>";
            }
            echo"<h1><center style='margin:30px auto;color:red'>A Solução está correta</center></h1>";
            echo"<img style='margin-left:100px;' src='acertou.png'/>";
        } else {
            echo"<h1><center style='margin:50px auto;color:red'>Solução errada</center></h1>";
            echo"<img style='margin-left:100px;' src='errou.jpg'/>";
        }
        mysqli_close($link);
    }

}

//----------------- main() -----------------------------


$getparam = filter_input_array(INPUT_POST, FILTER_DEFAULT);

// O jogador optou por avaliar seu jogo ou por começar um novo
$option = $getparam['opt'];
$t = new Controle();
switch ($option) {
    case 1: // começar um novo jogo
        $t->criarGrid();
        $t->exibirSudoku();
        break;
    case 2: // submeter sua resposta a correção
        if (isset($getparam['vet'])) {
            $tabela = $getparam['vet'];
            $tempo = $getparam['tempo'];
            $time = strtotime($tempo);

            $t->verificaTabJogador($tabela, $time);
        } else {
            echo "<p><center style='margin:200px'>Inicie um novo jogo</center></p>";
        }
        break;
}