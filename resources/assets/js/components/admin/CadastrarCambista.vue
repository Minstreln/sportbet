<template>
  <div class="box box-primary">
       <notifications group="foo" />
        <scale-loader :loading="loading"></scale-loader>
    <div class="content" v-if="formulario">
         
        <div class="col-md-4">
          
            <div class="form-group">
                  <label for="exampleInputEmail1">Nome</label>
                  <input type="text" v-model="cambista.name" class="form-control"  placeholder="Insira o nome">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Login</label>
                  <input type="text" class="form-control" v-model="cambista.username"  placeholder="Insira o login">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Senha</label>
                  <input type="text" class="form-control" v-model="cambista.password"  placeholder="Insira a senha">
            </div>

              <div class="form-group">
                <label>Selecione um Gerente:</label>
                 <select class="form-control" v-model="cambista.gerente_id">
                            <option v-for="g in gerentes" :key="g.id" :value="g.id">{{g.name}}</option>       
                </select> 
             </div>
           
        </div>
         <div class="col-md-4">
            <div class="form-group">
                  <label for="exampleInputEmail1">Contato</label>
                  <input type="tel" v-mask="['(##) ####-####', '(##) #####-####']" v-model="cambista.contato" class="form-control"  placeholder="Insira o contato">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Endereço</label>
                  <input type="text" class="form-control" v-model="cambista.endereco"  placeholder="Insira o Endereço">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Saldo Ap. Simples</label>
                  <input type="number" class="form-control" v-model="cambista.saldo_simples"  placeholder="Saldo Ap. Simples">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Saldo Ap. Casadinha</label>
                  <input type="number" class="form-control" v-model="cambista.saldo_casadinha"  placeholder="Saldo Ap. Casadinha">
             </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Saldo (Loto)</label>
                  <input type="number" class="form-control" v-model="cambista.saldo_loto"  placeholder="Saldo (Loto)">
             </div>

           
        </div>
       

         <div class="col-md-2">

                <div class="form-group">
                    <label for="exampleInputEmail1">C. 1 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao1"  placeholder="C. 1 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. 2 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao2"  placeholder="C. 2 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. 3 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao3"  placeholder="C. 3 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. 4 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao4"  placeholder="C. 4 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. 5 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao5"  placeholder="C. 5 Jogo %">
                </div>

          </div>  
             <div class="col-md-2">
                  <div class="form-group">
                    <label for="exampleInputEmail1">C. 6 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao6"  placeholder="C. 6 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. 7 Jogo %</label>
                    <input type="number" class="form-control"  v-model="cambista.comissao7" placeholder="C. 7 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. 8 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao8"  placeholder="C. 8 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. 9 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao9"  placeholder="C. 9 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. 10 Jogo %</label>
                    <input type="number" class="form-control" v-model="cambista.comissao10"  placeholder="C. 10 Jogo %">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">C. Loto</label>
                    <input type="number" class="form-control" v-model="cambista.comissao_loto"  placeholder="C. Loto">
                </div>
             </div>

            <div class="col-md-12">
                <button class="btn btn-success" @click="sendCambista(cambista)"><i class="fa fa-send-o" ></i> Cadastrar</button>
            </div>    
            
        </div>

        <div class="content">
           
        </div>

       
    
  </div>
</template>


<script>
export default {
    
    created() {  
    
    this.loadGerente() 
          
    },
    data() {
        return {
           loading: false, 
           cambista: {
               comissao1: 0,
               comissao2: 0,
               comissao3: 0,
               comissao4: 0,
               comissao5: 0,
               comissao6: 0,
               comissao7: 0,
               comissao8: 0,
               comissao9: 0,
               comissao10: 0,
               comissao_loto: 0,
               gerente_id: 0,
               saldo_simples: 0,
               saldo_casadinha: 0,
           },    
            formulario: true,   
            gerente: '',
            gerentes: [],          
        }
    },

    computed: {
      
      
      },
    methods: {
         sendCambista(cambista) {  
             this.loading = true;
             this.formulario = false;

             this.cambista = cambista;
             axios.post('/admin/cadastrar-cambista', this.cambista)
            .then((response)=>{
                  this.cambista = '';
                  this.$notify({
                  group: 'foo',
                  title: 'Sucesso!',
                  text: 'Cadastro efetuado com sucesso!',
                  type: 'success',
                  duration: 4000,
                  speed: 1000,
                  })
             
                  
              
            }).catch(()=>{
                 this.$notify({
                  group: 'foo',
                  title: 'Erro!',
                  text: 'Erro ao cadastrar!',
                  type: 'error',
                  duration: 4000,
                  speed: 1000,
                 })
            }).finally(()=>{
                this.cambista = {
                    comissao1: 0,
                    comissao2: 0,
                    comissao3: 0,
                    comissao4: 0,
                    comissao5: 0,
                    comissao6: 0,
                    comissao7: 0,
                    comissao8: 0,
                    comissao9: 0,
                    comissao10: 0,
                    gerente_id: 0,
                    saldo_simples: 0,
                    saldo_casadinha: 0,
                }
                 this.loading = false;
                  this.formulario = true;
            })
                 
           
        },
        
        loadGerente() {
            axios.get('/admin/list-gerentes')
                .then((response)=>{
                    this.gerentes = response.data; 
                    
                })
                .catch(()=> {
                    this.loading = false;
                    this.dadosgerentes = true;
                })
                
                
    },
    }
}
</script>
