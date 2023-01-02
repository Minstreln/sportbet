<template>

  <div class="box box-primary">
    <div class="content" >
 <notifications group="foo" />
      <div class="row" v-if="formulario">
        <div class="col-md-4">
              <div class="form-group">
                  <label for="exampleInputEmail1">Nome</label>
                  <input type="text" class="form-control" v-model="gerente.name" placeholder="Insira o nome">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Login</label>
                  <input type="text" class="form-control" v-model="gerente.username"  placeholder="Insira o login">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Senha</label>
                  <input type="text" class="form-control" v-model="gerente.password" placeholder="Insira a senha">
            </div>
           
        </div>
         <div class="col-md-4">
            <div class="form-group">
                  <label for="exampleInputEmail1">Contato</label>
                  <input type="tel" v-mask="['(##) ####-####', '(##) #####-####']"  class="form-control" v-model="gerente.contato"  placeholder="Insira o contato">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Endereço</label>
                  <input type="text" class="form-control" v-model="gerente.endereco" placeholder="Insira o Endereço">
            </div>
             <div class="form-group">
                  <label for="exampleInputEmail1">Comissão Sobre Lucro %</label>
                  <input type="number" class="form-control" v-model="gerente.comissao_gerente"  placeholder="Insira a Comissão Sobre Lucro">
            </div>  
           
           
        </div>
         <div class="col-md-4">
            
            <div class="form-group">
                  <label for="exampleInputEmail1">Saldo</label>
                  <input type="number" class="form-control" v-model="gerente.saldo_gerente" placeholder="Insira o Saldo">
            </div>
        
        </div>

      <div class="content">
            <div class="col-md-12">
                  <button class="btn btn-success" @click="sendGerente(gerente)"><i class="fa fa-send-o"></i> Cadastrar</button>
            </div>     
      </div>

       </div>

      </div>
  </div>
</template>





<script>
export default {
    
    created() {  

          
    },
    data() {
        return {
           formulario: true,
           loading: false, 
           gerente: {},       
          
        }
    },

    computed: {
 
      },
    methods: {
        sendGerente(gerente) {  
            this.gerente = gerente;
            this.formulario = false;
             axios.post('/admin/cadastrar-gerente', this.gerente)
            .then(()=>{
                  this.$notify({
                  group: 'foo',
                  title: 'Sucesso!',
                  text: 'Cadastro efetuado com sucesso!',
                  type: 'success',
                  duration: 4000,
                  speed: 1000,
            })
                  this.gerente = '';
            })
            .catch(error=> {
            this.$notify({
                  group: 'foo',
                  title: 'Erro!',
                  text: 'Erro ao cadastrar!',
                  type: 'error',
                  duration: 4000,
                  speed: 1000,
            })
            console.log(error);
            })
            .finally(()=>{
                 this.formulario = true;
                 this.gerente = {}; 
            })
        }
    }
}
</script>

