<template>
 <div class="box box-primary">
 <notifications group="foo" />
        <div class="content">
            <div class="row">
                 <div class="col-md-4">
                     <div class="form-group">
                        <label>Selecione um Cambista:</label>
                        <select class="form-control" v-model="idCambista" @change="loadMercados(idCambista)">
                            <option  v-for="cambista in cambistas" :value="cambista.id" :key="cambista.id">{{cambista.name}}</option>
                        </select>
                    </div>
               </div>
            </div>
           <div class="row">
                <scale-loader :loading="loading"></scale-loader>
                
               <div class="col-md-4" v-for="mercado in mercados" :key="mercado.id">
                    <label>{{mercado.name}}</label>
                    <div class="input-group percent">
                        <input type="text" v-model="mercado.porcentagem"  class="form-control">
                        <span class="input-group-btn">
                            <button  class="btn btn-success" type="button" title="Alterar" @click="alterarMercado(mercado.id, mercado.porcentagem)" >
                                <i class="fa fa-check"></i>
                            </button>
                        </span>
                    </div>
                  
                     <br>
               </div>
           </div>
       

      
        </div>
</div>   
</template>
<style>
    .percent{
        margin-bottom: 4px;
    }
</style>

<script>
export default {

    created() {
        this.loadCambista()
    }, 
    data () {
        return {
            idCambista: '',
            cambistas: [],
            mercados: [],
            name:'',
            loading:false,
        }
    },
    methods: {
        bloquearMercado (id) {
           let name = 0
                axios.put('/admin/update-mercado/'+id,{status:name})
                        .then((response)=>{
                      
                             this.$notify({
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                            })

                            this.loadMercados(this.idCambista) 
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
        liberarMercado (id) {
              let name = 1
                axios.put('/admin/update-mercado/'+id,{status:name})
                        .then((response)=>{
                      
                             this.$notify({
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                            })

                            this.loadMercados(this.idCambista) 

                        
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
        alterarMercado (id, porcentagem) {
                 axios.put('/admin/update-mercado/'+id,{porcentagem:porcentagem})
                        .then((response)=>{
                  
                             this.$notify({
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                            })

                            this.loadMercados(this.idCambista) 

                      
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
         loadMercados (id) {
            this.mercados = [];
            this.loading = true
            axios.get('/admin/mercado-user/'+id)
                .then((response) => {                    
                    this.mercados = response.data
                })
                .catch(error=>{
                     console.log(error)
                })
                .finally(()=>{
                    this.loading = false
                })
        },
    }

}
</script>