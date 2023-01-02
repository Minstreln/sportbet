<template>
    <div class="box box-primary">
        <div class="content">
            <div class="row">
            <notifications group="foo" />

                        

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
                                <b><h4>Total: {{leagues.length}}</h4> </b>
                            </div>
                        </div>

                    </div> 

                    <div class="row">
               <div class="col-md-12">
                    <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr class="theader-table">
                                            <th>SPORT</th>
                                            <th>LIGA</th>
                                            <th>ACÃO</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                       

                                      <tr v-for="league in leagues" :key="league.id">
                                          <td><b>{{league.sport}}</b></td>
                                          <td>{{league.league}}</td>
                                          <td><button class="btn btn-success" title="Bloquear" @click="addMain(league)"><i class="fa fa-check"></i></button></td>
                                        
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
  .theader-table {
     background: #1E282C;
     color: #FFF;
 }
</style>

<script>
export default {
    created() {
        this.dateMatch = moment(new Date()).format('YYYY-MM-DD')
      
        this.esporte = 'Todos';

        this.loadLigas()
    },
    data () {
        return {
            partidas:[],
            esporte: '',
            dateMatch: new Date(),
            loading: false,
            countPartidas: 0,
            leagues: [],
         
            
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
        
        loadLigas () {
            this.loading = true;
            axios.get('/admin/show-ligas-list')
                    .then((response)=>{
                        this.leagues  = response.data
                    })
                    .finally(()=>{
                        this.loading = false
                    })
        },
        addMain (data) {
             this.loading = true;
             this.leagues = [];
                axios.post('/admin/insert-league-main', {sport:data.sport, league:data.league, league_id: data.league_id})
                        .then((response)=>{
                                this.$notify({            
                                    group: 'foo',
                                    title: 'Sucesso!',
                                    text: 'Liga adicionado com sucesso!',
                                    type: 'success',
                                    duration: 2000,
                                    speed: 1000,
                                    
                                })
                        })
                        .catch(error=>{
                            console.log(error)
                            this.$notify({
                                    group: 'foo',
                                    title: 'Erro!',
                                    text: 'Erro ao cadastrar!',
                                    type: 'error',
                                    duration: 2000,
                                    speed: 1000,
                                })    
                        })
                        .finally(()=>{
                            this.loading = false;
                            this.loadLigas();
                        })
            
        },
      

        
        deleteBlock(id) {
                     axios.delete('/admin/deletar-ligas/'+id)
                        .then((response)=>{
                            this.loadLigas()
                        console.log(response)
                            this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Bloqueio efetuado  com sucesso!',
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
                                text: 'Erro ao bloquear!',
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
        detalheMatch(match){
                this.match = match;
                this.mercados = [];
                
                axios.get('/admin/list-odd-match/'+match.id)
                        .then((response)=> {
                            this.mercados = response.data
                        })
                 $('#modal-match').modal('show');
        },
        updateOdd (id, valor) {

               axios.put('/admin/update-odd-match/'+id,{cotacao:valor})
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
        }
    }
}
</script>

