<?php

$this->get('/', function () {
  dispatch_now(new \App\Jobs\LoadEventHoje());
    return response()->json(['message' => 'Acesso negado!'], 404);
});

//Rotas liberadas

$this->group(['namespace' => 'Api'], function () {

    $this->get('teste', 'MatchController@teste');

    $this->post('login', 'ApiAuthController@login')->name('login');

    $this->get('site-list-leagues', 'MatchController@getLeagues')->name('list.leagues');

    $this->get('site-list-leagues-main', 'MatchController@getLeaguesMain');

    $this->post('site-search-times', 'MatchController@searchTime');

    $this->get('site-partidas-home', 'MatchController@showHomeHoje')->name('partidas-home');

    $this->get('site-partidas-amanha', 'MatchController@showHomeAmanha')->name('partidas-amanha');

    $this->get('site-partidas-depois-amanha', 'MatchController@depoisAmanha')->name('partidas-depois-amanha');

    $this->get('site-all-matchs', 'MatchController@allMatchs');

    //Live futebol
    $this->get('site-live-futebol', 'MatchController@liveFutebol');

    //Dias Futebol
    $this->get('dias-futebol', 'MatchController@diasFutebol');

    //Load
    $this->get('load-home', 'MatchController@loadLiveHome');

    //Teste Request
    $this->get('teste-request', 'MatchController@testeRequest');


    $this->post('site-search-league', 'MatchController@searchLeague')->name('search.league');

    $this->get('site-list-odds/{id}', 'MatchController@showOdds')->name('list.odd.match');
    $this->get('site-list-odds-live/{id}', 'MatchController@showOddsLive')->name('list.odd.match');



    //Lista regulamentos
    $this->get('regulamento', 'ConfiguracaoController@regulamento')->name('regulamento.list');

    $this->get('list-limites', 'ConfiguracaoController@listaLimites')->name('list.limites');

    //BIlhetes
    //View geral do bilhete
    $this->get('bilhete/{id}', function () {

        return view('bilhete');
    });

    $this->get('print-bilhete-id/{id}', 'BilheteController@printBilheteId');

    $this->post('print-bilhete-cod', 'BilheteController@printBilheteCod');

    $this->post('send-pre-aposta', 'BilheteController@sendPreAposta');

    //Rota para testes
    $this->get('testes', function () {

        //Montagem do objeto odds
        $this->arr['objeto']['id'] = 1245;
        $this->arr['objeto']['cotacao'] = 12.65;
        $this->arr['objeto']['group_odd'] = "Vencedor do Encontro";
        $this->arr['objeto']['odd'] = "Fora";
        $this->arr['objeto']['selected'] = true;

        return $this->arr;
    });


    //Função para Quininha Seninha
    $this->get('num-quina', 'QuininhaSeninhaController@geraQuina');

    //View cotações quina
    $this->get('taxas-quina', 'QuininhaSeninhaController@viewCotacaoQuina');

    //view Dias sorteios quina
    $this->get('concursos-quina', 'QuininhaSeninhaController@viewDiasSorteioQuina');


    //

    //Função para Seninha Seninha
    $this->get('num-sena', 'QuininhaSeninhaController@geraSena');

    //View cotações quina
    $this->get('taxas-sena', 'QuininhaSeninhaController@viewCotacaoSena');

    //view Dias sorteios quina
    $this->get('concursos-sena', 'QuininhaSeninhaController@viewDiasSorteioSena');
});

$this->group(['namespace' => 'Api', 'middleware' => 'jwt.auth', 'jwt.refresh'], function () {

    //Refresh
    $this->post('refresh-token', 'ApiAuthController@refresh');
});


$this->group(['namespace' => 'Api', 'middleware' => 'jwt.auth'], function () {

    //Lista ligas no app
    $this->get('list-leagues', 'MatchController@getLeagues')->name('list.leagues');
    $this->get('list-leagues-main', 'MatchController@getLeaguesMain');

    //Lista partidas home
    $this->get('partidas-home', 'MatchController@showHomeHoje')->name('partidas-home');

    //Lista partidas amanha
    $this->get('partidas-amanha', 'MatchController@showHomeAmanha')->name('partidas-amanha');

    //Lista partidas depois de amanhã
    $this->get('partidas-depois-amanha', 'MatchController@depoisAmanha')->name('partidas-depois-amanha');

    $this->get('all-matchs', 'MatchController@allMatchs');

    //Pesquisa Liga
    $this->post('search-league', 'MatchController@searchLeague')->name('search.league');

    //Lita mais odds
    $this->get('list-odds/{id}', 'MatchController@showOdds')->name('list.odd.match');

    //Get Bilhetes
    $this->get('bilhetes', 'BilheteController@bilhetes');

    //Post Bilhetes
    $this->post('search-bilhetes', 'BilheteController@searchBilhete');

    //Dados do usuário logado
    $this->get('user-logado', 'BilheteController@dadosLogado');

    //Validação dos códigos
    $this->post('print-bilhete-get-cod', 'BilheteController@printBilheteGetCod');

    //Validação dos códigos
    $this->post('print-bilhete-get-cod-site', 'BilheteController@printBilheteGetCodSite');

    $this->post('valida-cod', 'BilheteController@validaCod');
    //End Válida dados

    //envio de aposta
    $this->post('send-aposta', 'BilheteController@sendAposta');

    $this->post('send-aposta-site', 'BilheteController@sendApostaSite');

    //Envio aposta live
    $this->post('send-aposta-live', 'BilheteController@sendApostaLive');

    //Envio aposta
    $this->post('send-valid-live', 'BilheteController@validLive');

    //App
    //Envio aposta live App
    $this->post('send-aposta-live-app', 'BilheteController@sendApostaLiveApp');

    $this->post('send-valid-live-app', 'BilheteController@validLiveApp');


    //Envio de aposta loto
    $this->post('send-aposta-loto', 'BilheteController@sendApostaLoto');

    //Logout
    $this->get('logout', 'ApiAuthController@logout');

    $this->get('cancela-bilhete/{id}', 'BilheteController@cancelaBilhete');

    $this->post('relatorio-cambista', 'BilheteController@relatorio');
});
