<template>
    <div class="box box-primary">
        <div class="content">
            <div class="row">
            <notifications group="foo" />
            <!--Espação para os modals-->
            <div class="modal fade in"  id="modal-match" >
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="fa fa-close"></i></span></button>
                            <h4><b><i class="fa fa-trophy"></i> {{headerLeague}}</b></h4>
                        <h5 class="modal-title"><i class="fa fa-star"></i> {{match.home}} X {{match.away}} - <span class="date-modal">{{match.date | formatDate() }} Hs </span></h5>
                        <p><b>Ativa: {{match.visible}}</b></p>
                        </div>

                        <div class="modal-body box box-primary">
                            <div class="row">
                                <div class="col-md-12" v-for="mercado in mercados" :key="mercado.id">
                                        <h4 class="mercado-match"><b>{{mercado.name}}</b></h4>
                                            <div class="row">
                                                <div class="col-md-4" v-for="odd in mercado.odds" :key="odd.id">
                                                        <label>{{odd.odd}} </label>
                                                        <div class="input-group percent">
                                                            <input type="text" v-model="odd.cotacao"  class="form-control">
                                                            <span class="input-group-btn">
                                                                <button  class="btn btn-success" type="button" title="Alterar" @click="updateOdd(match.event_id, odd.id ,odd.cotacao, odd.odd, 0)" >
                                                                    <i class="fa fa-check"></i>
                                                                </button>
                                                            </span>
                                                        </div>

                                                        <div class="input-group percent" v-if="odd.status == 1">
                                                            <input type="text" value="Ativa" readonly  class="form-control">
                                                            <span class="input-group-btn">
                                                                <button  class="btn btn-danger" type="button" title="Alterar" @click="updateOdd(match.event_id, odd.id, odd.cotacao, odd.odd, 1)" >
                                                                    <i class="fa fa-close"></i>
                                                                </button>
                                                            </span>
                                                        </div>

                                                          <div class="input-group percent" v-if="odd.status == 0">
                                                            <input type="text" value="Bloqueada" readonly  class="form-control">
                                                            <span class="input-group-btn">
                                                                <button  class="btn btn-primary" type="button" title="Alterar" @click="updateOdd(match.event_id, odd.id, odd.cotacao, odd.odd, 3)" >
                                                                    <i class="fa fa-check"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                        <span class="automatica" v-if="odd.alterada == 0"><b>Automática</b></span>
                                                        <span class="manual" v-if="odd.alterada == 1"><b>Manual</b></span>
                                                </div>
                                            </div>
                                </div>
                            </div>
                        </div>
                       
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div><!--End Modal excluir-user-->

                     <div class="col-md-3">
                        <div class="form-group">
                            <label>Esporte:</label>
                            <select v-model="esporte" class="form-control">
                                <option>Todos</option>
                                <option>Futebol</option>
                            </select>
                        </div>
                        </div>
                        
                        <div class="col-md-3">
                                <div class="form-group">
                                    <label>Data:</label>
                                    <input type="date"  class="form-control" v-model="dateMatch">
                                </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Persquisar</label>
                                    <button class="btn btn-success form-control" @click="seachMatch()"><i class="fa fa-search "></i></button>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <b><h4>Total: {{countPartidas}}</h4> </b>
                            </div>
                        </div>

                    </div> 

                    <div class="row">
               <div class="col-md-12" v-for="league in leagues" :key="league.id">
                   <div class="header-leagues"><i class="fa fa-trophy"> </i> {{league.league}} <button class="btn btn-danger btn-league" title="Bloquear" @click="bloquearLeague(league.league)"><i class="fa fa-close"></i></button></div>
                    <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr class="theader-table">
                                            <th style="width:20%">SPORT</th>
                                            <th style="width:30%">CONFRONTO</th>
                                            <th style="width:20%">DATA </th>
                                            <th style="width:10%">ATIVA</th>
                                            <th style="width:10%">DETALHE</th>
                                            <th style="width:10%">ACÃO</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                       

                                      <tr v-for="partida in league.match" :key="partida.id">
                                          <td><b> <i class="fa fa-futbol-o"> </i>  {{partida.sport}}</b></td>
                                       
                                          <td>{{partida.home}} <b>X</b> {{partida.away}}</td>
                                          <td><b>{{partida.date | formatDate() }}</b></td>
                                          <td><h4><i v-if="partida.visible == 'Sim'" class="fa fa-check sim"></i> <i v-if="partida.visible == 'Não'"  class="fa fa-close nao"></i></h4></td> 
                                          <td><button class="btn btn-info" title="Detalhes" @click="detalheMatch(league.league, partida)"><i class="fa fa-plus"></i></button></td>
                                          <td>
                                              <button class="btn btn-danger" title="Bloquear" @click="bloquearMatch(partida.event_id, partida.sport, partida.home, partida.away, partida.date)"><i class="fa fa-close"></i></button>
                                              
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
 .sim {
     color:green;
 }
 .nao {
     color:red;
 }
 .date-modal {
     color: red;
     font-size: 15px;
 }
 .mercado-match {
     width: 100%;
     height: auto;
     padding: 8px;
     text-align: center;
     color: #FFF;
     background: #0086A7
 }
 .header-leagues {
     width: 100%;
     height: 50px;
     font-size: 16px;
     padding: 8px;
     background: #0086A7;
     color: #FFF;
 }
 .btn-league {
    float: right;
 }
 .theader-table {
     background: #1E282C;
     color: #FFF;
 }
 .automatica {
     color:darkgreen;
 }
 .manual {
     color:red;
 }
</style>

<script>
export default {
    created() {
        this.dateMatch = moment(new Date()).format('YYYY-MM-DD')
        this.loadPartidas();
        this.esporte = 'Todos';
    },
    data () {
        return {
            partidas:[],
            esporte: '',
            dateMatch: new Date(),
            loading: false,
            countPartidas: 0,
            match: {},
            mercados: [],
            leagues:[],
            headerLeague: '',
            
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
    },
    methods: {
        
        loadPartidas () {
            this.loading = true;
            axios.get('/admin/list-confrontos')
                    .then((response)=>{
                        this.leagues = response.data;
                        
                    })
                    .finally(()=>{
                        this.loading = false
                    })
        },
        seachMatch () {
            this.partidas = [];
            this.loading = true
            axios.post('/admin/search-match', {esporte:this.esporte, date:this.dateMatch})
                    .then((response)=>{
                        this.leagues = response.data;
                      
                    }).catch(error =>{
                            console.log(error)
                            this.loading = false;
                    }).finally(()=>{
                        this.loading = false;
                    })
        },

          bloquearLeague(data) {
              
                     axios.post('/admin/bloquear-ligas',{league:data})
                    .then((response)=>{

                        console.log(response)
                            this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Bloqueio efetuado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                                
                            })
                        
                            //this.loadLigas()
                            
                        
                    }).catch(()=>{
                             this.$notify({
                                group: 'foo',
                                title: 'Erro!',
                                text: 'Erro ao bloquear!',
                                type: 'error',
                                duration: 3000,
                                speed: 1000,
                            })    
                    })  
        },



        bloquearMatch(event_id, sport, home, away, date) {
                    let confronto = home+" X "+away
                     axios.post('/admin/update-match',{event_id:event_id, sport:sport, confronto:confronto, date:date })
                    .then((response)=>{
                            this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                                
                            })
                          
                           
                            
                        
                    }).catch(()=>{
                             this.$notify({
                                group: 'foo',
                                title: 'Erro!',
                                text: 'Erro ao atualizar!',
                                type: 'error',
                                duration: 3000,
                                speed: 1000,
                            })    
                    })  
        },

        liberarMatch(id) {
              let date = this.dateMatch
                let valor = 'Sim'
                axios.put('/admin/update-match/'+id,{visible:valor})
                    .then((response)=>{
                       
                         this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                                
                            })
                            if(this.dateMatch != ' '){
                                this.seachMatch();
                            }
                        
                    }).catch(()=>{
                             this.$notify({
                                group: 'foo',
                                title: 'Erro!',
                                text: 'Erro ao atualizar!',
                                type: 'error',
                                duration: 3000,
                                speed: 1000,
                            })    
                    })  
        },
        detalheMatch(league, match){
               
                this.match = match;
                this.headerLeague = league
                this.mercados = [];

                axios.get('/admin/list-odd-match/'+match.id)
                        .then((response)=> {
                            this.mercados = response.data
                        })
                 $('#modal-match').modal('show');
        },
        updateOdd (id, odd_uid, valor, odd, tipo) {
              
               axios.put('/admin/update-odd-match/'+id,{cotacao:valor, odd_uid: odd_uid, odd:odd, tipo:tipo})
                    .then((response)=>{
                            console.log(response)
                         this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                                
                            })

                     axios.get('/admin/list-odd-match/'+this.match.id)
                        .then((response)=> {
                            this.mercados = response.data
                        })
                           
                        
                    }).catch(()=>{
                             this.$notify({
                                group: 'foo',
                                title: 'Erro!',
                                text: 'Erro ao atualizar!',
                                type: 'error',
                                duration: 3000,
                                speed: 1000,
                            })    
                            
                    })  
        }
    }
}
</script>

