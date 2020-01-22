/*
 * função que limpa todod os campos editavéis
 * preenchidos pelo jogador
 */
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

/*--------------- eventos dos botões -------------------*/


// botão criar novo jogo
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
}
);