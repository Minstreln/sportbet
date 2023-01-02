<template>
    <div class="box box-primary">
        <div class="content">
            <div class="row">
            <notifications group="foo" />
               <div class="col-md-12" v-for="league in leagues" :key="league.id">
                   <div class="header-leagues"><i class="fa fa-trophy"> </i> {{league.league}} <button class="btn btn-danger btn-league" title="Bloquear" @click="bloquearLeague(league.league)"><i class="fa fa-close"></i></button></div>
                    <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr class="theader-table">
                                            <th style="width:20%">SPORT</th>
                                            <th style="width:40%">CONFRONTO</th>
                                            <th style="width:20%">DATA </th>
                                            <th style="width:10%">ACÃO</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                       

                                      <tr v-for="partida in league.match" :key="partida.id">
                                          <td><b> <i class="fa fa-futbol-o"> </i>  {{partida.sport}}</b></td>
                                       
                                          <td>{{partida.home}} <b>X</b> {{partida.away}}</td>
                                          <td><b>{{partida.time }} <span class="pisca">'</span></b></td>
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

        window.Echo.channel('live-futebol-live')
                    .listen('LiveFutebol', (data)=> {
                       this.leagues =  data;
        })
     
                
    },
    data () {
        return {
            partidas:[],
            esporte: 'Futebol',
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
            axios.get('/admin/confrontos-aovivo-show')
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
        updateOdd (id, valor, tipo) {
              
               axios.put('/admin/update-odd-match/'+id,{cotacao:valor, tipo:tipo})
                    .then((response)=>{
                        
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

