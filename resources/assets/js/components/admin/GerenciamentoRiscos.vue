<template>
    <div class="box box-primary">
        <div class="content">
            <div class="row">
                <!--Modal Bilhete-->
                <div class="modal fade in" id="modal-bilhete">
                    <div class="modal-dialog">
                        <div class="modal-content" v-for="palpite in palpites" :key="palpite.id">
                            <div class="modal-header" v-bind:class="palpite.status">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><i class="fa fa-rotate-left"></i></span></button>
                                    <h4 class="modal-title title-bilhete"><b>{{palpite.status}}</b></h4>                          
                            </div>
                            <div class="modal-body body-bilhete" >
                                <div class="tipo-aposta"><b>Aposta {{palpite.tipo}}</b></div>

                                <div class="info-aposta-header">
                                   <p><b>DATA:</b> {{palpite.created_at | formatDate()}}</p> 
                                    <p><b>VENDEDOR:</b> {{palpite.vendedor}}</p>
                                    <p><b>CLIENTE:</b> {{palpite.cliente}}</p>
                                </div>

                                <div class="header-palpite">
                                    <div class="palpite-left">
                                        <p>APOSTA</p>
                                    </div>
                                    <div class="palpite-right">
                                        <p>COTAÇÃO</p>
                                    </div>
                                </div>

                              

                                <div class="body-palpite" v-for="palp in palpite.palpites" :key="palp.id">
                                    <p><b>{{palp.sport}} - {{palp.match_temp | formatDate() }}</b></p>
                                    <p>{{palp.league}}</p>
                                    <p>{{palp.home}}  X  {{palp.away}}</p>
                                    <p><b>{{palp.group_opp}}</b></p>
                                    <span class="body-palpite-left">
                                        <p>{{palp.palpite}} -</p>
                                         <p>Status:</p>
                                    </span>
                                    <span class="body-palpite-right">
                                        <p>{{palp.cotacao | formatCotacao() }}</p>
                                        <p  v-bind:class="palp.status">{{palp.status}}</p>
                                    </span>
                                </div>

                                

               
                                
                                <h3 class="cupom-bilhete"><b>{{bilheteCupm}}</b></h3>

                                <div class="aposta-footer">
                                    <div class="info-aposta-footer-left">
                                        <p><b>Quantidade de Jogos:</b></p>
                                        <p><b>Cotação:</b></p>
                                        <p><b>Total Apostado:</b></p>
                                        <p><b>Possível Retorno:</b></p>
                                    </div>
                                    
                                    <div class="info-aposta-footer-right">
                                        <p>{{palpite.total_palpites}}</p>
                                        <p>{{palpite.cotacao | formatCotacao() }}</p>
                                        <p>{{palpite.valor_apostado | formatMoeda() }}</p>
                                        <p>{{palpite.retorno_possivel | formatMoeda()  }}</p>
                                    </div>
                                </div>
                                
                                


                            </div>
                           
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
               <!--End Modal Bilhete-->
                
               <div class="col-md-4">
                      <div class="form-group">
                        <label>Escolha uma Opção:</label>
                         <select class="form-control" v-model="opcao_risco" @change="searchGerente(opcao_risco)">
                            <option>Possível Retorno</option>
                            <option>Quantida de Bilhetes</option>
                            <option>Valor Apostado</option>
                            <option>Quantidade de Apostas em Aberto</option>
                            <option>Quntidade de Apostas no Bilhete</option>
                        </select>

                       
                    </div>
               </div>
               
            </div>

            <div class="row">
                <div class="col-md-12">
                        <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="tabela-class" >
                                        <tr class="header-tabela">
                                            <th class="tabela-class">CUPOM</th>
                                            <th class="tabela-class">VALOR APOSTADO</th>
                                            <th class="tabela-class">POSSÍVEL RETORNO</th>
                                            <th class="tabela-class">APOSTAS EM ABERTO</th>
                                            <th class="tabela-class">CONFERIR BILHETE</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                   
                                        <tr class="tbody-table" v-for="bilhete in bilhetes" :key="bilhete.id">
                                            <td class="body-cupom"><b>{{bilhete.cupom}}</b></td>
                                            <td><b>{{bilhete.valor_apostado | formatMoeda()}}</b></td>
                                            <td><b>{{bilhete.retorno_possivel  | formatMoeda()}}</b></td>
                                            <td><b>{{bilhete.andamento_palpites}}/{{bilhete.total_palpites}}</b></td>
                                            <td><button class="btn btn-success" @click="viewBilhete(bilhete.id, bilhete.status, bilhete.cupom, bilhete.created_at, bilhete.vendedor, bilhete.cliente, bilhete.total_palpites, bilhete.cotacao, bilhete.valor_apostado, bilhete.retorno_possivel,bilhete.tipo )"><i class="fa fa-tags"></i></button></td>
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
    .header-tabela {
        background: #00466A;
        color:#FFF;
        font-size: 13px;
        text-align: center;
    }
    .tbody-table {
        text-align: center;
        padding-bottom: 5px;
    }
    .body-cupom{
        color: red;
    }
    .body-bilhete {
        background:#F8ECC2;
        color: #000;   
    }
    .title-bilhete {
        text-align: center;
    }
    .tipo-aposta {
        text-align: center;
        font-size: 16px;
        border-bottom: #000 1px dashed;
        padding-bottom: 8px;
    }
    .info-aposta-header {
        padding: 5px;
        border-bottom: #000 1px dashed;
        padding-bottom: 8px;
  
    }
    .info-aposta-header p {
        margin-bottom: 0px;
    }
    .aposta-footer {
        margin-bottom: 0px;
    }
    .header-palpite {
        width: 100%;
        height: 22px;
        margin-bottom: 20px;
        border-bottom:  1px  #000 dashed;
        font-size: 15px;
        padding-bottom: 9px;
    }
    .palpite-left {
      width: 49%;
      float: left;
      text-align: left;

    }
    .palpite-right {
        width: 49%;
        float: right;
        text-align: right;
    }
    .body-palpite {
        width: 100%;
        height: 170px;;
        margin-bottom: 20px;
        border-bottom:  1px  #000 dashed;
        font-size: 15px;
        padding-bottom: 9px;
    }
    .body-palpite p {
        margin-bottom: 0px;
    }
    .body-palpite-left {
      width: 49%;
      float: left;
      text-align: left;

    }
    .body-palpite-right {
        width: 49%;
        float: right;
        text-align: right;
    }
    .aposta-footer {
        width: 100%;
        height: 100px;
        margin-bottom: 20px;
        border-top: 1px  #000 dashed;
        border-bottom:  1px  #000 dashed;
    }
    .info-aposta-footer-left p {
        margin-bottom: 0px;
        
    }
    .info-aposta-footer-left {
      width: 49%;
      height: auto;
      float: left;
      text-align: left;
      font-size: 15px;
    }
    .info-aposta-footer-right p {
        margin-bottom: 0px;
    }
    .info-aposta-footer-right {
        width: 49%;
        height: auto;
        float: right;
        text-align: right;
        font-size: 15px;
    }
    .cupom-bilhete {
        text-align: center;
    }


    .Aberto {
        background: #00C0EF;
        color: #FFF;
    }
    .Perdeu {
        background: #FF0000;
        color: #FFF;
    }
    .Ganhou {
        background: #008D4C;
        color: #FFF;
    }
    .Cancelado {
        background: #E69222;
        color: #FFF;
    }

    .Devolvido {
        background: #331E1B;
        color: #FFF;
    }
</style>

<script>
export default {
    created() {
         this.searchGerente('Possível Retorno');
    },
    data() {
        return {
            bilhetes: [],
            palpites: [],
            bilheteCupm: '',
            opcao_risco: 'Possível Retorno',
            loading: true,
        }
    },
    filters: {
            formatDate(date) {
                return moment(date).format('DD/MM HH:mm');
            },
            formatDate2(date) {
                return moment(date).format('DD/MM hh:mm');
            },
             formatCotacao(numero) {
                var numero = numero.toFixed(2).split('.');
                numero[0] =  numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
            },
            formatMoeda(numero) {
                var numero = numero.toFixed(2).split('.');
                numero[0] = "R$ " + numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
            },
            andamentoPalp(acertos, erros){


            },
    },
    methods:{
        
        searchGerente(opcao) {
            this.bilhetes = [];
            this.loading = true;
            axios.post('/admin/list-bilhete-risco',{opcao:opcao})
                    .then((response)=>{
                        
                            this.bilhetes = response.data;
                    })
                    .catch(error => {
                        console.log(error)
                    })
                    .finally(()=>{
                        this.loading = false;
                    })
        },
        viewBilhete(id) {

            $('#modal-bilhete').modal('show');
                this.palpites = []
                axios.get('/admin/palpites-bilhete/'+id)
                        .then((response)=>{
                            this.palpites = response.data
                                
                        })
                        .catch((err) =>{
                            console.log(err)
                        })
                        .finally(()=>{
                            
                        })

                

                

            //Carrega os Palpites

    },
        
    }
}
</script>
