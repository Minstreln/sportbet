<template>  
    <div class="box box-primary">
        <div class="content">
           <div class="row">

        <div class="modal fade in"  id="modal-fechamento" >
              <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span></button>
                      <h4 class="modal-title"><i class="fa fa-bank"></i> <b>{{colaboradorName}}</b></h4>
                    </div>
                    <div class="modal-body box box-primary">
                        <div class="row">
                            <div class="col-md-12" v-for="u in user" :key="u.id">
                                <div class="valor-fechamento-positivo">Entradas: {{u.entradas | formatMoeda() }} </div>
                                <div class="valor-fechamento-total-aberto">Entradas (Em Aberto): {{u.entradas_abertas | formatMoeda() }} </div>
                                <div class="valor-fechamento">Comissões (Gerente): {{u.comissao_gerente | formatMoeda() }} </div>  
                                <div class="valor-fechamento-total-negativo">Saídas: {{u.saidas | formatMoeda() }} </div>  
                                <div class="valor-fechamento">Comissões(Cambistas): {{u.comissoes | formatMoeda() }} </div>  
                                <div v-if="u.total > 0 " class="valor-fechamento-total-positivo">Total: {{u.total - u.comissao_gerente | formatMoeda() }} </div> 
                                <div v-if="u.total < 0 " class="valor-fechamento-total-negativo">Total: {{u.total | formatMoeda() }} </div>                                    
                                <button class="btn btn-primary btn-lg btn-block"  @click="fechamento(u.id)"  v-if="u.entradas_abertas < 1 " >Prestar Conta</button>
                            </div>
                            <scale-loader :loading="loadingCaixa"></scale-loader>
                        </div>
                    </div>
                  </div>
                  <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
        </div><!--End Modal excluir-user-->
        

               <div class="col-md-3">
                      <div class="form-group">
                        <label>Gerente:</label>
                         <select class="form-control" v-model="idGerente" @change="searchGerente(idGerente)">
                            <option>Todos</option>
                            <option  v-for="gerente in gerentes" :value="gerente.id" :key="gerente.id">{{gerente.name}}</option>
                        </select>

                       
                    </div>
               </div>
               
                 
               
           </div>

           <div class="row">
               <div class="col-md-12">
                    <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="tabela-class" >
                                        <tr class="gerente">
                                            <th class="tabela-class">GERENTE</th>
                                            <th class="tabela-class">QUANTIDADE</th>
                                            <th class="tabela-class">COMISSÕES (GERENTE)</th>
                                            <th class="tabela-class">ENTRADAS </th>
                                            <th class="tabela-class">ENTRADAS EM ABERTO</th>
                                            <th class="tabela-class">SAÍDAS</th>
                                            <th class="tabela-class">COMISSÕES (CAMBISTAS)</th>
                                            <th class="tabela-class">TOTAL</th>
                                            <th class="tabela-class">FECHAR</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                        <tr v-for="caixa in caixas" :key="caixa.id" >
                                            
                                            <td>{{caixa.colaborador}}</td>
                                            <td>{{caixa.quantidade}}</td>
                                            <td> {{caixa.comissao_gerente | formatMoeda()}}</td>
                                            <td class="positivo">{{caixa.entradas | formatMoeda() }}</td>
                                            <td class="aberto">{{caixa.entradas_abertas  | formatMoeda() }}</td>
                                            <td class="negativo">{{caixa.saidas  | formatMoeda() }}</td>
                                            <td>{{caixa.comissoes | formatMoeda() }}</td>
                                            <td class="positivo" v-if="caixa.total >= 0"> {{caixa.total - caixa.comissao_gerente  | formatMoeda()}}</td>
                                            <td class="negativo" v-if="caixa.total < 0"> {{caixa.total | formatMoeda()}}</td>
                                             <td><button class="btn btn-success" @click="sendFechamendo(caixa.id, caixa.colaborador)"><i class="fa fa-send"></i></button></td>
                                        </tr>
                                               
 
                                       <tr class="gerente" v-if="idGerente =='Todos'">
                                            <td>TOTAL</td>
                                            <td>{{ total_apostas  }}</td>
                                            <td>{{total_comissao_gerente  | formatMoeda()}}</td>
                                            <td class="positivo">{{total_entradas | formatMoeda()}}</td>
                                            <td class="aberto">{{total_entradas_abertas | formatMoeda()}}</td>
                                            <td class="negativo">{{total_saidas | formatMoeda()}}</td>
                                            <td >{{ total_comissaocambista | formatMoeda() }}</td>
                                            <td v-if="total_geral < 0" class="negativo">{{ total_geral  | formatMoeda() }}</td>
                                            <td v-if="total_geral >= 0" class="positivo">{{ total_geral - total_comissao_gerente  | formatMoeda() }}</td>
                                        </tr>   


                                    </tbody>
                                </table>
                                 <scale-loader :loading="loading"></scale-loader>

                    </div>
               </div>
           </div>
        </div>
    </div>
</template>
<style>
    .gerente {
        background: #00466A;
        color:#FFF;
        font-size: 13px;
        text-align: center;
    }
    .tabela-class {
        text-align: center;
    }
    .valor-total{
        background: #000;
        color: #FFF;
    }
    .positivo {
        background: #009688;
        color: #FFF;
    }

    .negativo {
        background: #FF0000;
        color: #FFF;
    }
    .aberto {
        background: #2C3B41;
        color: #FFF;
    }
    .valor-fechamento {
        width: 100%;
        height: auto;
        padding: 8px;
        background: #00466A;
        color: #FFF;
        font-size: 20px;
        text-align: center;
        border-bottom: 1pt solid #FFF;
    }
     .valor-fechamento-positivo {
        width: 100%;
        height: auto;
        padding: 8px;
        background: #009688;
        color: #FFF;
        font-size: 20px;
        text-align: center;
        border-bottom: 1pt solid #FFF;
    }
    .valor-fechamento-total-positivo {
        width: 100%;
        height: auto;
        padding: 8px;
        background: #11721D;
        color: #FFF;
        font-size: 20px;
        text-align: center;
    }

    .valor-fechamento-total-negativo {
        width: 100%;
        height: auto;
        padding: 8px;
        background:  #D73925;
        color: #FFF;
        font-size: 20px;
        text-align: center;
    }

    .valor-fechamento-total-aberto {
        width: 100%;
        height: auto;
        padding: 8px;
        background: #2C3B41;
        color: #FFF;
        font-size: 20px;
        text-align: center;
    }

   


        
</style>

<script>
export default {
    
    created () {
        this.loadCaixa()
        this.loadGerentes()    
    },
    data () {
        return {
            idGerente: 'Todos',
            colaboradorName: '',
            gerentes: [],
            caixas: [],
            loading: false,
            loadingCaixa: false,
            user: [],
            total_apostas: 0,
            total_comissao_gerente: 0,
            total_apostado: 0,
            total_saidas: 0,
            total_entradas: 0,
            total_entradas_abertas: 0,
            total_comissaocambista: 0,
            total_lancamentos: 0,
            total_geral: 0,
        }
    },
    filters: {
        formatMoeda(numero) {
                return "R$ " + numero.toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1.");
        },
    },
    methods: {
         loadGerentes() {
             axios.get('/admin/list-gerentes')
                    .then((response)=>{
                        this.gerentes = response.data; 
                       
                    })
                    .catch(()=> {
                      
                        this.dadosCambistas = true;
                    })
                    .finally(()=>{
                     
                        this.dadosCambista = true;
                    })
        },
                                             
        loadCaixa () {
              this.loading = true
              axios.get('/admin/list-caixa-adm-gerente')
                    .then((response)=>{
                        this.caixas = response.data


                         for (var i = 0; i < this.caixas.length; i++) {
                                    this.total_apostas              = this.caixas[i].quantidade + this.total_apostas;
                                    this.total_comissao_gerente     = this.caixas[i].comissao_gerente + this.total_comissao_gerente;
                                    this.total_entradas             = this.caixas[i].entradas + this.total_entradas;
                                    this.total_entradas_abertas     = this.caixas[i].entradas_abertas + this.total_entradas_abertas;
                                    this.total_saidas               = this.caixas[i].saidas + this.total_saidas;
                                    this.total_comissaocambista     = this.caixas[i].comissoes + this.total_comissaocambista;
                                    this.total_lancamentos          = this.caixas[i].lancamentos + this.total_lancamentos;
                                    this.total_geral                = this.caixas[i].total + this.total_geral;
                            }
                        
                    })
                    .catch(()=> {
                       
                       
                    })
                    .finally(()=>{
                        this.loading = false
                       
                    })
        },
        sendFechamendo (id, name) {
            this.loadingCaixa = true
            this.user = []
            this.colaboradorName = name
            $('#modal-fechamento').modal('show');
            
                axios.get('/admin/list-gerente-caixa/'+id)
                    .then((response)=>{
                        this.user = response.data;
                        
                    
                    })
                    .catch(()=> {
                       
                       
                    })
                    .finally(()=>{
                       this.loadingCaixa = false
                       
                    })
        
             
        },
        searchGerente(id) {
            this.loading = true
            if(id == 'Todos') {
                   this.loadCaixa()
                   return;
            }
                axios.get('/admin/list-gerente-caixa/'+id)
                    .then((response)=>{
                        console.log(response)
                        this.caixas = response.data;
                        
                    
                    })
                    .catch(()=> {
                       
                       
                    })
                    .finally(()=>{
                       this.loading = false
                       
                    })
        
        },
        fechamento (id) {
         
            this.loadingCaixa = true
            axios.put('/admin/encerrar-caixa/'+id)
                    .then((response) =>{

                        this.loadCaixa()

                        
                    })
                    .catch(()=>{
                        
                    })
                    .finally(()=>{
                        this.loadingCaixa = false
                        $('#modal-fechamento').modal('hide');
                        
                    })
        }
    }
}
</script>


