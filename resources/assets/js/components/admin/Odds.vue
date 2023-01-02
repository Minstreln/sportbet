<template>
    <div class="box box-primary">
        <notifications group="foo" />
                <div class="content">
                <div class="row">
                   
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Selecione um mercado:</label>
                        <select v-model="mercado" class="form-control" @change="loadOdds()">
                             <option  v-for="merc in mercados" :key="merc.id">{{merc.name}}</option>    
                        </select>
                       </div>
                    </div>  
                </div>
                <div class="row">
                    <div class="col-md-4" v-for="odd in odds" :key="odd.id">
                    <label>{{odd.name}}</label>
                    <div class="input-group percent">
                        <input type="text" v-model="odd.porcentagem"  class="form-control">
                        <span class="input-group-btn">
                            <button  class="btn btn-success" type="button" title="Alterar" @click="alterarOdd(odd.id, odd.porcentagem)" >
                                <i class="fa fa-check"></i>
                            </button>
                        </span>
                    </div>
                    <div class="input-group">
                        <input v-if="odd.status == 0"  type="text" value="Bloqueado" readonly class="form-control">
                          <input v-if="odd.status == 1"  type="text" value="Ativo" readonly class="form-control">
                        <span class="input-group-btn">

                            <button v-if="odd.status == 1" class="btn btn-danger" type="button" title="Bloquear" @click="bloquearOdd(odd.id)" >
                                <i class="fa fa-close"></i>
                            </button>

                            <button v-if="odd.status == 0" class="btn btn-primary" type="button" title="Desbloquear" @click="liberarOdd(odd.id)" >
                                <i class="fa fa-check"></i>
                            </button>
                        </span> 
                    </div>
                     <br>
               </div>
                   
                </div>
                 <scale-loader :loading="loading"></scale-loader>
                </div>
    </div>

</template>

<script>
export default {
    created () {
        this.loadMercados();
    
    },  
    data () {
        return {
            mercado: '',
            mercados: [],
            odds: [],
            odd: {},
            loading: false,

        }
    },
    methods: {

        loadMercados () {
             axios.get('/admin/list-mercados')
                .then((response) => {                    
                    this.mercados = response.data
                }).catch(error=>{
                     console.log(error)
                })
        },
        loadOdds () {
            this.odds = [];
            this.loading = true
            axios.post('/admin/list-odds',{mercado_name:this.mercado})
                .then((response) => {                    
                    this.odds = response.data
                })
                .catch(error=>{
                     console.log(error)
                })
                .finally(()=>{
                    this.loading = false
                })
        },
        bloquearOdd (odd) {
            let id = odd
            let name = 0
                axios.put('/admin/update-odd/'+id,{status:name})
                        .then((response)=>{
                            this.odd = odd;
                             this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                                
                            })
                           
                            this.loadOdds()
                        })
                        .catch(()=>{
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
          liberarOdd (odd) {
               let id = odd
               let name = 1
                axios.put('/admin/update-odd/'+id,{status:name})
                        .then((response)=>{
                              this.odd = odd;
                             this.$notify({
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                            })

                            this.loadOdds()
                
                        })
                        .catch(()=>{
                           
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
        
        alterarOdd (id, porcentagem) {
                 axios.put('/admin/update-odd/'+id,{porcentagem:porcentagem})
                        .then((response)=>{
                  
                             this.$notify({
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                            })

                            this.loadOdds(this.idCambista) 

                      
                        })
                        .catch(()=>{
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
        
    }
}
</script>