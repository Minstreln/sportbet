<template>
    <div class="box box-primary">
        <notifications group="foo" />
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-4"  v-for="dezena in dezenas" :key="dezena.id">
                            <label>{{ dezena.dezena }} Dezenas</label>
                            <div class="input-group percent">
                                <input type="text" v-model="dezena.taxa"  class="form-control">
                                <span class="input-group-btn">
                                    <button  class="btn btn-success" type="button" title="Alterar" @click="updateTaxa(dezena.id, dezena.taxa)" >
                                        <i class="fa fa-check"></i>
                                    </button>
                                </span>
                            </div>
                            <div class="input-group" v-if="dezena.status == 1">
                            
                                <input   type="text" value="Ativo"   readonly class="form-control">
                                <span class="input-group-btn">
                                    <button  class="btn btn-danger" type="button" title="Bloquear" @click="updateStatus(dezena.id, 0)">
                                        <i class="fa fa-close"></i>
                                    </button>
                                </span>
                            </div>

                            <div class="input-group" v-if="dezena.status == 0">
                            
                                <input   type="text" value="Bloqueado"   readonly class="form-control">
                                <span class="input-group-btn">
                                    <button  class="btn btn-primary" type="button" title="Bloquear" @click="updateStatus(dezena.id, 1)">
                                        <i class="fa fa-send"></i>
                                    </button>
                                </span>
                            </div>

                            <br>
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
     
        this.modalidade = 'Quininha';
        this.loadDezenas();

        
    },
    data () {
        return {

            modalidade: '',
            dezenas: [],
          
         
            
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
        
        loadDezenas () {
            this.loading = true;
            axios.get('/admin/list-taxas-seninha')
                    .then((response)=>{
                        this.dezenas  = response.data
                    })
                    .finally(()=>{
                        this.loading = false
                    })
        },
        updateStatus(id, valor) {
               
               axios.put('/admin/update-status-sena/'+id,{status:valor})
                    .then((response)=>{
                       
                         this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                                
                            })
                            this.loadDezenas();
                        
                    }).catch(()=>{
                             this.$notify({
                                group: 'foo',
                                title: 'Erro!',
                                text: 'Erro ao atualizar!',
                                type: 'error',
                                duration: 3000,
                                speed: 1000,
                            })  
                            this.loadDezenas();  
                    })  
        },

        updateTaxa(id, valor) {

               axios.put('/admin/update-taxa-sena/'+id,{taxa:valor})
                    .then((response)=>{
                        
                         this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
                                
                            })
                            this.loadDezenas();
                        
                    }).catch(()=>{
                             this.$notify({
                                group: 'foo',
                                title: 'Erro!',
                                text: 'Erro ao atualizar!',
                                type: 'error',
                                duration: 3000,
                                speed: 1000,
                            })    
                            this.loadDezenas();
                    })  
        }
    }
}
</script>