<template>
  
    <div class="box box-primary">
        <div class="content">
           <div class="row">
            <notifications group="foo"/>
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
                                <div class="tipo-aposta"><b>{{palpite.tipo}}</b></div>

                                <div class="info-aposta-header">
                                   <p><b>DATA:</b> {{palpite.created_at | formatDate()}}</p> 
                                    <p><b>VENDEDOR:</b> {{palpite.vendedor}}</p>
                                    <p><b>CLIENTE:</b> {{palpite.cliente}}</p>
                                    <p v-if="palpite.modalidade == 'Loto'"><b>SORTEIO:</b> {{palpite.concurso}}</p>
                                </div>

                             
                                <div v-if="palpite.modalidade != 'Loto'">
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
                                    <p>{{palp.home}}  X  {{palp.away}} - <b class="score">{{palp.score}}</b></p>
                                    <p><b>{{palp.group_opp}}</b></p>
                                    <span class="body-palpite-left">
                                        <p>{{palp.palpite}} - <span v-if="palp.type=='ao-vivo'">({{palp.type}})</span></p>
                                         <p>Status:</p>
                                    </span>
                                    <span class="body-palpite-right">
                                        <p>{{palp.cotacao | formatCotacao() }}</p>
                                        <p  v-bind:class="palp.status">{{palp.status}}</p>
                                    </span>
                                </div>
                                </div>

                                <div v-if="palpite.modalidade == 'Loto'">
                                  <div class="header-palpite-loto">

                                        <div  v-for="palp in palpite.palpites_loto" :key="palp.id" class="dezenas">-{{palp.dezena}}- </div>
                                  </div>

                                  <div class="resultado" v-if="palpite.status != 'Aberto'">
                                      <h4>RESULTADO</h4>
                                      <p>{{palpite.resultado_loto}}</p>
                                  </div>

                                </div>
                             
                                
                             <h3 class="cupom-bilhete"><b>{{palpite.cupom}}</b></h3>

                             <div class="aposta-footer" v-if="configuracoes.comissao_premio > 0">
                                    <div class="info-aposta-footer-left">
                                        <p><b>Quantidade de Jogos:</b></p>
                                        <p><b>Acertos:</b></p>
                                        <p><b>Cotação:</b></p>
                                        <p><b>Total Apostado:</b></p>
                                        <p><b>Site Paga:</b></p>
                                        <p><b>Cambista Paga:</b></p>
                                      
                                    </div>
                                    
                                    <div class="info-aposta-footer-right">
                                        <p>{{palpite.total_palpites}}</p>
                                        <p>{{palpite.acertos_palpites}}</p>
                                        <p>{{palpite.cotacao | formatCotacao() }}</p>
                                        <p>{{palpite.valor_apostado | formatMoeda() }}</p>
                                        <p>{{palpite.retorno_possivel | formatMoeda()  }}</p>
                                        <p>{{palpite.retorno_cambista | formatMoeda()  }}</p>
                                      
                                    </div>
                                </div>

                                <div class="aposta-footer" v-else>
                                    <div class="info-aposta-footer-left">
                                        <p><b>Quantidade de Jogos:</b></p>
                                        <p><b>Acertos:</b></p>
                                        <p><b>Cotação:</b></p>
                                        <p><b>Total Apostado:</b></p>
                                        <p><b>Retorno Possível:</b></p>                                     
                                    </div>
                                    
                                    <div class="info-aposta-footer-right">
                                        <p>{{palpite.total_palpites}}</p>
                                        <p>{{palpite.acertos_palpites}}</p>
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

               <div class="col-md-2">
                      <div class="form-group">
                        <label>Cambista:</label>
                        <select class="form-control" v-model="idCambista">
                            <option>Todos</option>
                            <option  v-for="cambista in cambistas" :value="cambista.id" :key="cambista.id">{{cambista.name}}</option>
                        </select>

                       </div>
               </div>   

               <div class="col-md-2">
                      <div class="form-group">
                        <label>Status:</label>
                        <select class="form-control" v-model="statusAposta" >
                            <option>Todos</option>
                            <option>Aberto</option>
                            <option>Ganhou</option>
                            <option>Perdeu</option>
                            <option>Devolvido</option>
                            <option>Cancelado</option>

                        </select>
                    </div>
               </div>
               <div class="col-md-2">
                      <div class="form-group">
                        <label>Tipo:</label>
                        <select class="form-control" v-model="tipoAposta">
                            <option>Todos</option>
                            <option>Multipla</option>
                            <option>Simples</option>
                            <option>Quininha</option>
                            <option>Seninha</option>
                         
                        </select>
                    </div>
               </div>
               <div class="col-md-2">
                      <div class="form-group">
                        <label>De:</label>
                        <input type="date" v-model="date1" class="form-control" >
                    </div>
               </div>

                <div class="col-md-2">
                      <div class="form-group">
                        <label>Até:</label>
                        <input type="date" v-model="date2" class="form-control" >
                    </div>
               </div>

               <div class="col-md-2">
                   <div class="form-group">
                       <label>Persquisar</label>
                         <button class="btn btn-success form-control" @click="searchBilhetes()"><i class="fa fa-search "></i></button>
                   </div>
               </div>

               
           </div>

           <div class="row">
               <div class="col-md-12">
                    <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="tabela-class" >
                                        <tr>
                                            <th class="tabela-class">CUPOM</th>
                                            <th class="tabela-class">DATA</th>
                                            <th class="tabela-class">STATUS</th>
                                            <th class="tabela-class">APOSTADO</th>
                                            <th class="tabela-class">RETORNO</th>
                                            <th class="tabela-class">VENDEDOR</th>
                                            <th class="tabela-class">CLIENTE</th>
                                            <th class="tabela-class">COMISSÃO</th>
                                            <th class="tabela-class">COTAÇÃO</th>
                                            <th class="tabela-class">TIPO</th>  
                                            <th class="tabela-class">AP. ABERTAS</th>            
                                            <th class="tabela-class">MOSTRAR</th>
                                            <th class="tabela-class">CANCELAR</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                       

                                        <tr v-for="bilhete in bilhetes" :key="bilhete.id">
                                            
                                            <td v-bind:class="bilhete.tipo_aposta"><b>{{bilhete.cupom}}</b></td>
                                            <td>{{bilhete.created_at | formatDate() }}</td>
                                            <td v-bind:class="bilhete.status">{{bilhete.status}}</td>
                                            <td>{{bilhete.valor_apostado | formatMoeda()}}</td>
                                            <td>{{bilhete.retorno_possivel | formatMoeda() }}</td>
                                            <td>{{bilhete.vendedor}}</td>
                                            <td>{{bilhete.cliente}}</td>
                                            <td>{{bilhete.comicao | formatMoeda() }}</td>
                                            <td>{{bilhete.cotacao | formatCotacao() }}</td>
                                            <td>{{bilhete.tipo}}</td>
                                            <td>{{bilhete.andamento_palpites}}/{{bilhete.total_palpites}}</td>
                                            <td><button class="btn btn-primary" @click="viewBilhete(bilhete.id, bilhete.status, bilhete.cupom, bilhete.created_at, bilhete.vendedor, bilhete.cliente, bilhete.total_palpites, bilhete.cotacao, bilhete.valor_apostado, bilhete.retorno_possivel,bilhete.tipo )"><i class="fa fa-eye"></i></button></td>
                                            <td>
                                                <button v-if="bilhete.status == 'Cancelado'" class="btn btn-defaul" disabled ><i class="fa fa-exclamation-circle "></i></button>
                                                <button v-if="bilhete.status != 'Cancelado'" class="btn btn-danger" @click="alterarBilhete(bilhete, bilhete.id, bilhete.user_id, bilhete.valor_apostado, bilhete.retorno_possivel,  bilhete.total_palpites, bilhete.comicao, bilhete.status)"><i class="fa fa-remove "></i></button>
                                            </td>

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
    .score {
        color:#b12323;
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

    .vivo {
        background: #C06576;
        color: #000;
    }
    

    .pre {
        background: #075758;
        color: #FFFFFF;
    }

  
   
</style>
 
<script>
export default {

    
    created() {
      this.loadBilhetes();
      this.loadCambista();  
      this.loadConfiguracao();
      this.date1 = moment(new Date()).format('YYYY-MM-DD')
      this.date2 = moment(new Date()).format('YYYY-MM-DD')
    },
    data() {
        return {         
           idCambista: 'Todos',
           statusAposta: 'Todos',
           tipoAposta: 'Todos',
           loading: false,
           loadingBilhete: false,
           cambistas: [],
           //Dados Modal
           bilheteId: '',
           bilheteCupm: '',
           bilheteStatus: '',
           bilheteDate: '',
           bilheteOperador: '',
           bilheteCliente: '',
           bilhetePalpites: '',
           bilheteCotacao: '',
           bilheteApostado: '',
           bilheteRetorno: '',
           palpites: [],
           bilhetes:[],
           configuracoes: {}
           
        
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
    methods: {
        loadBilhetes() {
                this.loading = true
                axios.get('mostra-bilhetes/')
                        .then((response)=>{
                            this.bilhetes = response.data
                                this.loading = false
                        })
                        .catch((err) =>{
                            console.log(err)
                        })
                        .finally(()=>{
                            this.loading = false
                        })
        },
     
        loadCambista() {
             axios.get('/admin/list-cambistas')
                    .then((response)=>{
                        this.cambistas = response.data; 
                    })
                    .catch(()=> {
                        this.loading = false;
                        this.dadosCambistas = true;
                    })
                    .finally(()=>{
                        this.loading = false;
                        this.dadosCambista = true;
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


        },
        alterarBilhete (bilhete, id, user_id , valor_apostado, retorno_possivel,  total_palpites, comissao, bilhete_status) {
        
            var r = confirm("Deseja realmente excluir o bilhete?");
            if (r == true) {
                    let value = 'Aberto'
                    //this.bilhetes = [];
                    axios.put('/admin/bilhete-update/'+id,{status:bilhete_status, user_id:user_id, valor_apostado: valor_apostado, retorno_possivel:retorno_possivel, total_palpites:total_palpites, comissao:comissao, bilhete_status:bilhete_status})
                        .then((response)=>{
                                this.$notify({            
                                        group: 'foo',
                                        title: 'Sucesso!',
                                        text: 'Bilhete Atualizado  com sucesso!',
                                        type: 'success',
                                        duration: 3000,
                                        speed: 1000,
                                })
                                bilhete.status = "Cancelado";
                        })
                        .catch(()=>{
                            this.$notify({
                                        group: 'foo',
                                        title: 'Erro!',
                                        text: 'Erro ao Atualiza o Bilhete!',
                                        type: 'error',
                                        duration: 3000,
                                        speed: 1000,
                                    })
                        })
                        .finally(()=>{
                           
                        })
            } else {
            
            return;
            }
            
           
        },
        searchBilhetes () {
            this.loading = true
            this.bilhetes = []
              axios.post('/admin/search-bilhete', {cambista:this.idCambista, status:this.statusAposta, tipo:this.tipoAposta, date1:this.date1, date2:this.date2})
                    .then((response)=>{
                            this.bilhetes = response.data
                    }).finally(()=>{
                        this.loading = false;
                    })
        },
           loadConfiguracao() {
            axios.get('/admin/list-configuracoes')
                    .then((response)=>{
                        console.log(response)
                        this.configuracoes = response.data[0];
                    }).catch(()=>{

                    })
        },
        

      
    }

}
</script>

