<template>
    <div class="box box-primary">
        <notifications group="foo" />
                <div class="content">
                <div class="row">
                   
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Selecione um cambista:</label>
                        <select v-model="idCambista" class="form-control" @change="loadOdds(idCambista)">
                           <option  v-for="cambista in cambistas" :value="cambista.id" :key="cambista.id">{{cambista.name}}</option>
                        </select>
                       </div>
                    </div>  
                </div>
                <div class="row" v-for="mercado in odds" :key="mercado.id">
                     
                            <div class="col-md-12 ">
                                   <div class="cabec-mercado">{{ mercado.mercado}} </div>
                                   <div class="row" >
                                        <div class="col-md-4"  v-for=" odd in mercado.odds" :key="odd.id">
                                             <label>{{odd.name}}</label>
                                            <div class="input-group percent">
                                                <input type="text" v-model="odd.porcentagem"  class="form-control">
                                                <span class="input-group-btn">
                                                    <button  class="btn btn-success" type="button" title="Alterar" @click="alterarOdd(odd.id, odd.porcentagem)" >
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                </span>
                                            </div> 
                                        </div>
                                   </div>
                                  
                             </div>
     
       
                   
                </div>
                 <scale-loader :loading="loading"></scale-loader>
                </div>
    </div>

</template>
<style>
    .cabec-mercado {
        width: 100%;
        height: 45px;
        background: #253136;
        color: #FFF;
        font-size: 16px;
        padding: 6px;
        margin-bottom: 5px;
    }
    .flutuante {
        width: 30%;
        height: 45px;
        float: left;
    }
</style>
<script>
export default {
    created () {
        this.loadCambista();
    
    },  
    data () {
        return {
            idCambista: '',
            cambistas: [],
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
        loadOdds (id) {
            this.odds = [];
            this.loading = true
            axios.get('/admin/odds-user/'+id)
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
        
    }
}
</script>