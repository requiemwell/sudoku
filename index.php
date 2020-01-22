<!DOCTYPE html>
<html>

    <head>
        <title>Ad2_web</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">
        <script type="text/javascript "src="script.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <link rel='stylesheet' type='text/css' href='estilo.css' />
    </head>

    <body onload="novoJogo()">
        <div id="container">
            <header>
                <div id='header'>
                    <p>Sudoku</p>
                </div>
            </header>
            <div align='center ' id='counter'>
                <h2>00:00:00</h2>
            </div>
            <hr>
            <div id="teste">
                <div id="area_sodoku">
                </div>
                <div id="instrucao">
                    <p id="texto">

                        O Sudoku é um passatempo, pra ser jogado por apenas
                        uma pessoa, que envolve raciocínio e lógica.
                        A ideia do jogo
                        é bem simples: completar todas as 81 células usando
                        números de 1 a 9, sem repetir os números numa mesma
                        linha, coluna ou grade (3x3).

                    </p>
                    <img src="exemplo.png" id="exemplo">
                </div>

            </div>
            <div id="op">
                <button id='btn_start' type="button">Iniciar</button>
                <button id='btn_novo' type="button">Novo</button>
                <button id='btn_verificar' type="button">Verificar</button>
                <button id='btn_limpar' type="button">Limpar</button>
                <button id="inst" type="button"><i class="fas fa-question"></i></button>
            </div>
        </div>
        <script>
            function limpar() {
                var x = document.getElementsByClassName('c1');
                for (i = 0; i < x.length; i++) {
                    x[i].value = x[i].value.innerHtml = '';
                }

            }
            /*
             * função responsável por fazer a requisição
             * para um novo jogo.
             */
            function novoJogo() {
                var page = "controle.php";
                $.ajax({
                    type: 'POST',
                    dataType: "html",
                    url: page,
                    beforeSend: function () {
                        $('#area_sodoku').html('<img src="spinner.gif" id="spiner"/>');
                    },
                    data: {opt: 1},
                    cache: false,
                    success: function (msg) {
                        $("#area_sodoku").html(msg);
                        $('.c1').hide();
                    }
                });
            }

            /*
             * função auxiliar que transforma um vetor em matriz,
             * uma vez que os elementos da classe 'campo'
             * estão em um array.
             */
            function arrayParaMatriz(a) {
                vet = [];
                v = [];
                lin = 0;
                col = 0;
                for (i = 0; i <= a.length; i++) {
                    if (i % 9 === 0 & i !== 0) {
                        vet[lin] = v;
                        v = [];
                        col = 0;
                        v[col] = parseInt(a[i]);
                        col++;
                        lin++;
                    } else {
                        v[col] = parseInt(a[i]);
                        col++;
                    }
                }
                return vet;
            }

            /*
             * Função responsável por capturar o tabuleiro
             * do jogador, bem como o tempo paro o seu preenchimento
             * e realizar a requisição para verificação.
             */
            function verificar() {
                var x = document.getElementsByClassName('campo');
                var vetor = [];
                for (i = 0; i < x.length; i++) {
                    vetor[i] = x[i].value;
                }
                console.log(vetor);
                vet = arrayParaMatriz(vetor);

                if (typeof interval !== 'undefined') {
                    clearInterval(interval);
                }
                var page = "controle.php";
                tempo = document.getElementById('counter').textContent;
                $.ajax({
                    type: 'POST',
                    dataType: "html",
                    url: page,
                    beforeSend: function () {
                        $('#area_sodoku').html('<img src="spinner.gif"/>');
                    },
                    data: {vet: vet, tempo: tempo, opt: 2},
                    cache: false,
                    success: function (msg) {
                        $("#area_sodoku").html(msg);
                    }
                });
            }

            /*
             * função auxiliar responsável
             * pela formatação do tempo
             */
            function formatatempo(hr, min, segs) {

                if (hr < 10) {
                    hr = '0' + hr;
                }
                if (min < 10) {
                    min = '0' + min;
                }
                if (segs < 10) {
                    segs = '0' + segs;
                }
                fin = '<h2>' + hr + ':' + min + ':' + segs + '</h2>';
                return fin;

            }
            // inicialização das variáveis de tempo
            var segundos = 0;
            var minutos = 0;
            var horas = 0;

            /*
             * função  responsável pelo incremento
             * dos segundos, minutos e horas
             */
            function conta() {
                segundos++;
                if (segundos >= 60) {
                    segundos = 0;
                    minutos++;
                }
                if (minutos >= 60) {
                    minutos = 0;
                    horas++;
                }
                document.getElementById('counter').innerHTML = formatatempo(horas, minutos, segundos);
            }

            /*
             * Função que inicia o contador de tempo
             * chamando a função conta() em intervalos de 1 segundo
             */
            function inicia() {
                //se o contador já foi inicializado será resetado
                if (typeof interval !== 'undefined') {
                    horas = 0;
                    minutos = 0;
                    segundos = 0;
                    clearInterval(interval);
                    interval = setInterval("conta();", 1000);
                } else {
                    interval = setInterval("conta();", 1000);
                }
            }
            /*
             * Função responsável por garantir
             * a validade da entrada passada pelo jogador
             * adimitindo somente números, e estes no intervalo
             * fechado de 1 até 9
             */
            function validaEntrada() {
                //função que garante a correção dos dados de entrada
                var x = document.getElementsByClassName("c1");
                var re = /^[1-9]$/;
                for (i = 0; i < x.length; i++) {
                    if (!re.test(x[i].value)) {
                        x[i].value = x[i].value.innerHTML = '';
                    }
                }
            }
            $("#btn_novo").click(function () {
                $('#btn_verificar').css('display', 'none');
                if (typeof interval !== 'undefined') {
                    clearInterval(interval);
                }
                novoJogo();
                $("#btn_start").show();

            });

            // botão inicia o jogo
            $("#btn_start").click(function () {
                $('.c1').css("background-color", '#DCDCDC');
                $('#btn_verificar').css('display', 'block');
                $('.c1').show();
                $("#btn_start").hide();
                inicia();

            });

            // Botão limpar tabuleiro
            $('#btn_limpar').click(function () {
                limpar();
            });

            //botão avaliar 
            $('#btn_verificar').click(function () {
                verificar();
            });
            // botao instuções
            $("#inst").click(function () {
                $("#area_sodoku").fadeToggle();
                $("#instrucao").fadeToggle();
            });

        </script>

    </body>

</html>
