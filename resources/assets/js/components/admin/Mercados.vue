<template>
 <div class="box box-primary">
 <notifications group="foo" />
        <div class="content">
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
                    <div class="input-group">
                        <input v-if="mercado.status == 0"  type="text" value="Bloqueado" readonly class="form-control">
                          <input v-if="mercado.status == 1"  type="text" value="Ativo" readonly class="form-control">
                        <span class="input-group-btn">

                            <button v-if="mercado.status == 1" class="btn btn-danger" type="button" title="Bloquear" @click="bloquearMercado(mercado.id)" >
                                <i class="fa fa-close"></i>
                            </button>

                            <button v-if="mercado.status == 0" class="btn btn-primary" type="button" title="Desbloquear" @click="liberarMercado(mercado.id)" >
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
        this.loadMercados()
    }, 
    data () {
        return {
            mercados: [],
            name:'',
            loading:false,
        }
    },
    methods: {
        loadMercados () {
            this.mercados = [];
            this.loading = true;
            axios.get('/admin/list-mercados')
                .then((response) => {

                     
                    this.mercados = response.data
                }).finally(()=>{
                     this.loading = false;
                })
        },
        bloquearMercado (id) {
           let name = 0
                axios.put('/admin/update-mercado/'+id,{status:name})
                        .then((response)=>{
                            this.loadMercados()
                             this.$notify({
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                            })
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
                            this.loadMercados()
                             this.$notify({
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                            })
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
                            this.loadMercados()
                             this.$notify({
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                            })
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
        }
    }

}
</script>