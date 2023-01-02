<?php
Route::get('/', function () {
    return view('home');
});

Auth::routes();


$this->get('admin/login', function(){
      return view('auth.login');
})->name('admin.login');

$this->group(['prefix' => 'admin', 'middleware' => 'auth', 'namespace' =>'Admin'], function(){

        //Página home
        $this->get('/', 'HomeController@index')->name('home');
        $this->get('home', 'HomeController@index')->name('home');

        //Valores da página Inicial
        $this->get('relatorio-home', 'HomeController@relatorioHome');

        //Editar banca
        $this->get('editar-banca', 'HomeController@editBanca')->name('editar.banca');
        $this->get('dados-banca', 'HomeController@mostraDadosAdm')->name('dados.banca');
        $this->get('regulamento', 'HomeController@viewRegulamento')->name('regulamento');
        $this->get('regulamento-list', 'HomeController@indexRegulamento')->name('regulamento.list');
        $this->put('regulamento-update/{id}','HomeController@regulamentoUpdate')->name('regulamento.update');
        
        //Configurações banca
        $this->get('configuracoes', 'ConfiguracaoController@indexView')->name('configuracoes');
        $this->get('list-configuracoes', 'ConfiguracaoController@index')->name('list-configuracoes');
        $this->put('edit-configuracao/{id}', 'ConfiguracaoController@update')->name('edit.configuracao');

        //Bloquear usuário
        $this->post('alterar-user', 'ConfiguracaoController@bloquearUser');

        //Usuário Logado
        $this->get('user-logado', 'HomeController@userLogado');

        //Editar dados do adm
        $this->get('editar-perfil', function() {

            return view('admin.editar-perfil');
        });
        

        //Mapa Apostas
        $this->get('mapa-apostas', 'MapaController@index')->name('mapa.apostas');
        //Gerentes 
        //Cadastrar
        $this->get('cadastrar-gerentes', 'GerenteController@storeView')->name('cadastrar.gerente');
        $this->post('cadastrar-gerente', 'GerenteController@store')->name('cadastrar.gerente');
        //Editar
        $this->get('editar-gerente', 'GerenteController@edtView')->name('editar.gerente');
        $this->put('editar-gerente/{id}', 'GerenteController@update')->name('editar.gerente');
        //Deletar
        $this->delete('deletar-gerente/{id}', 'GerenteController@destroy')->name('deletar.gerente');
        //ViewBlad
        $this->get('gerentes', 'GerenteController@indexView')->name('gerentes');
        //List Json
        $this->get('list-gerentes','GerenteController@index')->name('list.gerentes');
        //Search 
        $this->get('search-gerente/{name}','GerenteController@searchUser')->name('search.gerente');



        //Cambistas
        //Cadastrar
        $this->get('cadastrar-cambistas', 'CambistaController@storeView')->name('cadastrar.cambistas');
        $this->post('cadastrar-cambista', 'CambistaController@store')->name('cadastrar.cambista');
        
        //Editar
        $this->get('editar-cambista', 'CambistaController@edtView')->name('editar.cambista');
        $this->put('editar-cambista/{id}', 'CambistaController@update')->name('editar.cambista');
        
        //ViewBlad
        $this->get('cambistas', 'CambistaController@indexView')->name('cambistas');
        //Deletar
        $this->delete('deletar-cambista/{id}', 'CambistaController@destroy')->name('deletar.cambista');
        //List Json
        $this->get('list-cambistas','CambistaController@index')->name('list.cambistas');
        //Search 
        $this->get('search-cambista/{name}','CambistaController@searchUser')->name('search.cambista');
        
        //Lançamentos Cambistas
        $this->get('lancamentos','CambistaController@lancamento')->name('store.lancemento');
        $this->post('lancamentos','CambistaController@storeLancamento')->name('store.lancemento');
        $this->get('list-lancamentos','CambistaController@lancamentos');
        
        //Bilhetes adm
        $this->get('bilhetes', 'BilheteController@indexView')->name('bilhetes');
        $this->get('mostra-bilhetes', 'BilheteController@index')->name('bilhetes');
        //Alterar condição de bilhete
        $this->put('bilhete-update/{id}', 'BilheteController@update')->name('bilhete.update');
        //Pesquisar Bilhetes
        $this->post('search-bilhete', 'BilheteController@search')->name('search.bilhete');


        //Palpites Do bilhete
        $this->get('palpites-bilhete/{id}', 'PalpiteController@show')->name('bilhetes');

        //Financeiro ADM Gerente
        $this->get('caixa-adm-gerente', 'FinanceiroController@indexViewAdmGerente')->name('caixa.adm.gerente');
        $this->get('list-caixa-adm-gerente', 'FinanceiroController@caixaGerente')->name('list.caixa.adm.gerente');

        $this->get('list-gerente-caixa/{id}', 'FinanceiroController@viewCaixaGerente');
       

        //Financeiro ADM Cambista
        $this->get('caixa-adm-cambista', 'FinanceiroController@indexViewAdmCambista')->name('caixa.adm.cambista');
        $this->get('list-caixa-adm-cambista', 'FinanceiroController@caixaCambista')->name('list.caixa.adm.cambista');
        $this->get('list-caixa-adm-cambista/{id}', 'FinanceiroController@caixaUser')->name('lancamentos');
        $this->get('list-caixa-cambista/{id}', 'FinanceiroController@caixaUserCambista')->name('lancamentos');
        $this->put('encerrar-caixa/{id}', 'FinanceiroController@encerraCaixa');
        
        //Gerenciar confrontos ao vivo
        $this->get('confrontos-aovivo', 'ConfrontosController@viewAovivo')->name('confrontos-aovivo');
        $this->get('confrontos-aovivo-show', 'ConfrontosController@indexAovivo')->name('confrontos-aovivo-show');
        
            
        //Gerenciamento de confrontos
        $this->get('confrontos', 'ConfrontosController@indexView')->name('confrontos');
        $this->get('list-confrontos', 'ConfrontosController@index')->name('list-confrontos');
        $this->post('search-match', 'ConfrontosController@searchMatch')->name('search-match');
        //List odds confronto
        $this->get('list-odd-match/{id}', 'MercadosController@show')->name('list.odd.match');
        //Updadte Visible Match
        $this->post('update-match', 'ConfrontosController@blockMatch')->name('update.match');
        //Update valor Odd
        $this->put('update-odd-match/{id}', 'ConfrontosController@updateOdd')->name('update.odd.match');

        //Ligas
        $this->get('list-ligas-bloqueadas', 'ConfrontosController@indexLigasBlock')->name('list.ligas');

        
        //Mapa de apostas
        $this->get('list-map-aposta', 'MapaController@mapAposta');
       
        //Gerenciar Ligas
        $this->get('gerenciar-ligas', 'ConfiguracaoController@gerenciarLigas');

        //Listar Ligas gerais do sistema
        $this->get('adm-ligas-list', function(){
                return view('admin.list-adm-ligas');
        });
        $this->get('show-ligas-list', 'ConfiguracaoController@showLigas');

        $this->get('gerenciar-ligas-principais', function(){
                return view('admin.ligas-principais');
        });
        $this->get('show-ligas-principais', 'ConfrontosController@listLeagueMain');
      

        $this->post('insert-league-main', 'ConfrontosController@insertLeagueMain');

        //Gerenciar Confrontos
        $this->get('gerenciar-matchs', 'ConfiguracaoController@gerenciarMatchs');

        //Bloquear ligas
        $this->post('bloquear-ligas', 'ConfrontosController@blockLeague');
        
        //Desbloquear ligas
        $this->delete('deletar-ligas/{id}', 'ConfrontosController@deleteLeague');

        //Desbloquear ligas
        $this->delete('deletar-ligas-main/{id}', 'ConfiguracaoController@deleteLeague');



        //Delete Match
        $this->delete('deletar-match/{id}', 'ConfrontosController@deleteMatch');

        //indexMatchsBloc
        $this->get('list-matchs-bloqueadas', 'ConfrontosController@indexMatchsBlock');


        

        //Gerenciar mercados
        $this->get('mercados', 'MercadosController@indexView')->name('mercados');
        $this->get('mercados-user', 'ConfiguracaoController@gerenciarCotacoes');
        $this->get('list-mercados', 'MercadosController@index')->name('list.mercados');
        $this->put('update-mercado/{id}', 'MercadosController@update')->name('update.mercado');
        //Listar Mercados por user
        $this->get('mercado-user/{id}', 'MercadosController@mercadoUser');

        //Gerenciar odds
        $this->get('odds', 'OddsController@indexView')->name('odds');
        $this->post('list-odds', 'OddsController@index')->name('list.odds');
        $this->put('update-odd/{id}', 'OddsController@update')->name('update.odd');
        //Listar Odds por user
        $this->get('odds-user/', 'OddsController@indexViewCambista');
        $this->get('odds-user/{id}', 'OddsController@oddsUser');


        //Gerenciamento de riscos
        $this->get('gerenciaento-riscos', 'GerenciamentoRiscos@viewGerenciamento')->name('gerenciaento-riscos');

        $this->post('list-bilhete-risco', 'GerenciamentoRiscos@riscos');


        //Relatórios
        $this->get('relatorio-cambista', function(){
            return view('admin.relatorio-cambista');
        });

        $this->get('relatorio-gerente', function(){
            return view('admin.relatorio-gerente');
        });

        $this->post('search-relatorio-gerente', 'RelatorioController@relatorioGerente');
        $this->post('search-relatorio-cambista', 'RelatorioController@relatorioCambista');


        //Loto Quina
        $this->get('cotacao-loto-quina', function(){
            return view('admin.loto-taxas-quininha');
        })->name('cotacao-loto-quina');

        //Listar cotações QUININHA
        $this->get('list-taxas-quininha', 'QuininhaSeninhaController@listTaxasQuininha');

        //Altera cotação Quininha
        $this->put('update-taxa-quina/{id}', 'QuininhaSeninhaController@alteraCotacaoQuina');
        //Altera Status Quininha
        $this->put('update-status-quina/{id}', 'QuininhaSeninhaController@bloqueiaCotacaoQuina');


        //Loto Sena
        $this->get('cotacao-loto-sena', function(){
            return view('admin.loto-taxas-seninha');
        })->name('cotacao-loto-sena');

        //Listar cotações QUININHA
        $this->get('list-taxas-seninha', 'QuininhaSeninhaController@listTaxasSeninha');

        //Altera cotação Quininha
        $this->put('update-taxa-sena/{id}', 'QuininhaSeninhaController@alteraCotacaoSena');
        //Altera Status Quininha
        $this->put('update-status-sena/{id}', 'QuininhaSeninhaController@bloqueiaCotacaoSena');


        

        

        

        

        


});