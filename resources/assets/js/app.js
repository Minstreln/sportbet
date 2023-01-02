import store from './store/store'
import Snotify from 'vue-snotify'
import VueTheMask from 'vue-the-mask';
import Notifications from 'vue-notification';

require('./bootstrap');



window.Vue = require('vue');
window.moment = require('moment');


Vue.use(Notifications)
Vue.use(Snotify)

Vue.use(VueTheMask);
//Componente de loading
Vue.component('scale-loader', require('vue-spinner/src/ScaleLoader.vue').default);
Vue.component('clip-loader', require('vue-spinner/src/ClipLoader.vue').default);
//Editar perfil adm
Vue.component('edt-perfil-adm-component', require('./components/admin/EditarPerfilAdm.vue').default);
//Home
Vue.component('home-component', require('./components/admin/Home.vue').default);
//Editar banca
Vue.component('editar-banca-component', require('./components/admin/EditarBanca.vue').default);
//Editar Regulamento
Vue.component('regulamento-component', require('./components/admin/Regulamento.vue').default);

//Configuraçao
Vue.component('configuracao-banca-component', require('./components/admin/Configuracao.vue').default);

//Mappa Aposta
Vue.component('mapa-aposta-component', require('./components/admin/MapaAposta.vue').default);

//Bilhetes Adm 
Vue.component('finacneiro-adm-gerente-component', require('./components/admin/FinanceiroAdmGerente.vue').default);

//Financeiro Adm Gerente
Vue.component('bilhete-adm-component', require('./components/admin/Bilhete.vue').default);
//Financeiro Adm Cambista 
Vue.component('finacneiro-adm-cambista-component', require('./components/admin/FinanceiroAdmCambista.vue').default);

//Lançamentos
Vue.component('lancamentos-component', require('./components/admin/Lancamento.vue').default);

//Cadastrar Gerentes
Vue.component('cadastro-gerentes-component', require('./components/admin/CadastrarGerente.vue').default);
//Editar Gerente
Vue.component('editar-gerentes-component', require('./components/admin/EditarGerente.vue').default);
//List Gerentes
Vue.component('gerentes-component', require('./components/admin/Gerentes.vue').default);

//Cadastrar Cambista
Vue.component('cadastro-cambistas-component', require('./components/admin/CadastrarCambista.vue').default);
//Editar Gerente
Vue.component('editar-cambistas-component', require('./components/admin/EditarCambista.vue').default);
//List Cambistas
Vue.component('cambistas-component', require('./components/admin/Cambistas.vue').default);

//Gerenciamento de riscos
Vue.component('gerenciar-riscos-component', require('./components/admin/GerenciamentoRiscos.vue').default);

//Gerenciar Confrontos ao vivo
Vue.component('confrontos-aovivo-component', require('./components/admin/ConfrontosAovivo.vue').default);

//Gerenciar Confrontos
Vue.component('confrontos-component', require('./components/admin/Confrontos.vue').default);

//Gerenciar Ligas
Vue.component('gerenciar-ligas-component', require('./components/admin/GerenciarLigas.vue').default);
//Listar Ligas
Vue.component('list-ligas-component', require('./components/admin/ListAdmLigas.vue').default);
//Ligas Princiapais
Vue.component('list-ligas-main-component', require('./components/admin/LigasPrincipais.vue').default);

//Gerenciar Matchs
Vue.component('gerenciar-matchs-component', require('./components/admin/GerenciarMatchs.vue').default);

//Gerenciar Mercados
Vue.component('mercados-component', require('./components/admin/Mercados.vue').default);

//Gerenciar Mercados Cambistas
Vue.component('mercados-cambista-component', require('./components/admin/MercadosCambista.vue').default);

//Gerenciar Odds
Vue.component('odds-component', require('./components/admin/Odds.vue').default);

//Gerenciar Odds Cambistas
Vue.component('odds-cambista-component', require('./components/admin/OddsCambista.vue').default);

//Relatórios
Vue.component('relatorio-cambista-component', require('./components/admin/RelatorioCambista.vue').default);

Vue.component('relatorio-gerente-component', require('./components/admin/RelatorioGerente.vue').default);

//Página home do site
Vue.component('geral-component', require('./components/home/Geral.vue').default);

//Bilhete rela time
Vue.component('bilhete-realtime', require('./components/home/BilheteRealtime.vue').default);

//Gerenicar Taxas Loto
Vue.component('taxa-loto-quina', require('./components/admin/TaxasLotoQuina.vue').default);

Vue.component('taxa-loto-sena', require('./components/admin/TaxasLotoSena.vue').default);

const app = new Vue({
    store,
    el: '#app',
});


 