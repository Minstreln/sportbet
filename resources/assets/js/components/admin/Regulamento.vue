<template>
    <div class="box box-info">
        <div class="content">
          <notifications group="foo" />
           <div class="row" >
               <div class="box-body" >
                     <vue-ckeditor name="texto" v-model="content" :config="config" @blur="onBlur($event)" @focus="onFocus($event)" />
               </div>
                
                <div class="col-md-12">
                    <button class="btn btn-success" @click="updateRegulamento()"><i class="fa fa-send-o" ></i> Cadastrar</button>
                </div>   

           </div>
        </div>
    </div>
</template>

<script>
import VueCkeditor from 'vue-ckeditor2';

export default {
 components: { VueCkeditor },

    created () {
        this.loadRegulamento();
         
       
    },
 
    data () {
        return {
            regulamento: {},
            texto: '',
            idRegulamento: '',

            //Configuração do CkEditor
            content: '',
            config: {
                toolbar: [
                ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']
                ],
                height: 400,
                enterMode: CKEDITOR.ENTER_BR
            }


        }
    },
    methods: {
        loadRegulamento () {
            axios.get('/admin/regulamento-list')
                    .then((response)=>{                
                            this.content = response.data[0]['regulamento']
                            this.idRegulamento = response.data[0]['id'];
                            console.log(response)

                                 
                    })
                    .catch(error=> {
                        console.log(error)
                    })
        },
        updateRegulamento () {
                axios.put('/admin/regulamento-update/'+this.idRegulamento,{regulamento:this.content})
                        .then((response)=>{
                            this.$notify({            
                                group: 'foo',
                                title: 'Sucesso!',
                                text: 'Atualizado  com sucesso!',
                                type: 'success',
                                duration: 3000,
                                speed: 1000,
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
                        .finally(()=>{
                              
                                
                            })
                        })

        },
       
        onBlur(editor) {
            console.log(editor);
        },
        onFocus(editor) {
            console.log(editor);
        }
        
    }

}

</script>
