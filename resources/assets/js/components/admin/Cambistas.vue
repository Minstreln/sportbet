 <template>

    <div class="row"> 
         <notifications group="foo" />
      <!--Espação para os modals-->
        <div class="modal fade in"  id="modal-excluir-user" >
              <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span></button>
                      <h4 class="modal-title"><i class="fa fa-trash"></i> <b>Excluir</b></h4>
                    </div>

                    <div class="modal-body box box-primary">
                        <div class="row">
                            <div class="col-md-12">
                              <div class="callout callout-warning">                            
                                  <h4 >Deseja realtemente excluir o cambista(a): {{cambista.name}}</h4>
                              </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-thumbs-o-down"></i> NÃO</button>
                      <button type="button" class="btn btn-success" @click="deleteUser(cambista.id)" ><i class="fa fa-thumbs-o-up"></i> SIM</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
        </div><!--End Modal excluir-user-->

        <!--MODAL EDITAR USER-->
      <div class="modal fade in"  id="modal-editar-user" >
              <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-rotate-left"></i></span></button>
                      <h4 class="modal-title"><i class="fa fa-user"></i> <b>{{cambista.name}}</b></h4>
                    </div>
                   
<div class="modal-body box box-primary">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                  <label for="exampleInputEmail1">Nome</label>
                  <input type="text" v-model="cambista.name" class="form-control"  placeholder="Insira o nome">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Login</label>
                  <input type="text" v-model="cambista.username"  class="form-control"  placeholder="Insira o login">
            </div>
           
            <div class="form-group">
                  <label for="exampleInputEmail1">Contato</label>
                  <input type="tel" v-model="cambista.contato"  v-mask="['(##) ####-####', '(##) #####-####']" class="form-control"  placeholder="Insira o contato">
            </div>

            <div class="form-group">
                <label for="exampleInputEmail1">Alterar Senha</label>
                  <input type="text" v-model="cambista.password"  class="form-control"  placeholder="Nova senha">
            </div>      
  
        </div>
         <div class="col-md-6">
             
            <div class="form-group">
                  <label for="exampleInputEmail1">Endereço</label>
                  <input type="text" class="form-control" v-model="cambista.endereco"   placeholder="Insira o Endereço">
            </div>

            <div class="form-group" >
                  <label for="exampleInputEmail1">Saldo Ap. Simples</label>
                  <input type="number" v-model="cambista.saldo_simples" class="form-control"  placeholder="Saldo Ap. Simples">
            </div>

            <div class="form-group">
                  <label for="exampleInputEmail1">Saldo Ap. Casadinha</label>
                  <input type="number" v-model="cambista.saldo_casadinha" class="form-control"  placeholder="Saldo Ap. Casadinha">
             </div>

              <div class="form-group">
                  <label for="exampleInputEmail1">Saldo (Loto)</label>
                  <input type="number" v-model="cambista.saldo_loto" class="form-control"  placeholder="Saldo (Loto)">
             </div>

            <div class="form-group" >
                <label>Alterar Gerente:</label>
                 <select class="form-control" v-model="cambista.gerente_id">
                            <option v-for="g in gerentes" :key="g.id" :value="g.id">{{g.name}}</option>       
                </select> 
             </div>

           
        </div>
       

         <div class="col-md-12">
                <div class="int-comissao box box-primary">
                    <h4>Comissão Esportiva</h4>
                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 1 Jogo %</label>
                        <input type="number" v-model="cambista.comissao1" class="form-control"  placeholder="C. 1 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 2 Jogo %</label>
                        <input type="number" class="form-control"  v-model="cambista.comissao2"  placeholder="C. 2 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 3 Jogo %</label>
                        <input type="number" class="form-control" v-model="cambista.comissao3"  placeholder="C. 3 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 4 Jogo %</label>
                        <input type="number" class="form-control" v-model="cambista.comissao4"  placeholder="C. 4 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 5 Jogo %</label>
                        <input type="number" class="form-control" v-model="cambista.comissao5"  placeholder="C. 5 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 6 Jogo %</label>
                        <input type="number" v-model="cambista.comissao6" class="form-control"  placeholder="C. 1 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 7 Jogo %</label>
                        <input type="number" class="form-control"  v-model="cambista.comissao7"  placeholder="C. 2 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 8 Jogo %</label>
                        <input type="number" class="form-control" v-model="cambista.comissao8"  placeholder="C. 3 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 9 Jogo %</label>
                        <input type="number" class="form-control" v-model="cambista.comissao9"  placeholder="C. 4 Jogo %">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. 10 Jogo %</label>
                        <input type="number" class="form-control" v-model="cambista.comissao10"  placeholder="C. 5 Jogo %">
                    </div>
                </div>
          </div>  

          <div class="col-md-12">
                 <div class="int-comissao box box-primary">
                    <h4>Comissão Loto</h4>
                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">C. Loto %</label>
                        <input type="number" v-model="cambista.comissao_loto" class="form-control"  placeholder="C. 1 Jogo %">
                    </div>
                 </div>
          </div>

              
    </div>
</div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-success" @click="editUser(cambista)" ><i class="fa fa-send"></i> ALTERAR</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
      </div><!--End Modal editar-->
  
    <!--Corpo da página-->
    <div class="box box-primary">
        <div class="content">
            <div class="box-header with-border">
                <div class="row">
                     <div class="input-group input-group col-md-3 pull-left">
                        <input type="text" class="form-control"  v-model="searchName"   placeholder="Buscar por nome">
                        <span class="input-group-btn">
                          <button type="button" class="btn btn-success btn-flat" @click="searchUser()"><i class="fa fa-search"></i></button>
                        </span>
                      </div>
                      
                       <div class="box-tools  pull-right">
                          <a href="cadastrar-cambistas" class="btn btn-success"><i class="fa fa-plus"></i> Novo Cambista</a>
                      </div>  
                </div>

                <div class="row">
                        <div class="box-body"> 
                        
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="tabela-class" >
                                        <tr>
                                            <th class="tabela-class">NOME</th>
                                            <th class="tabela-class">CADASTRADO</th>
                                            <th class="tabela-class">SITUACAO</th>
                                            <th class="tabela-class">CONTATO</th>
                                            <th class="tabela-class">SALDO SIMPLES</th>
                                            <th class="tabela-class">SALDO CASADINHA</th>
                                            <th class="tabela-class">SALDO TOTAL</th>
                                            <th class="tabela-class">EDITAR</th>
                                            <th class="tabela-class">AÇÃO</th>
                                            <th class="tabela-class">EXCLUIR</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                       
                                      <tr v-for="cambista in cambistas"   :key="cambista.id" >
                                        <td>{{ cambista.name }}</td>
                                        <td>{{ cambista.created_at | formatDate() }}</td>
                                        <td v-if="cambista.situacao == 'ativo'"><i class="fa fa-check ativo"></i>Ativo</td>
                                        <td v-if="cambista.situacao == 'bloqueado'" ><i class="fa fa-close bloqueado"></i>Bloqueado</td>
                                        <td>{{cambista.contato}}</td>

                                        <td >
                                          {{cambista.saldo_simples | formatMoeda()}}
                                        </td>

                                        <td>
                                          {{cambista.saldo_casadinha | formatMoeda()}}
                                        </td>

                                        <td>
                                          {{cambista.saldo_casadinha+cambista.saldo_simples | formatMoeda()}}
                                        </td>  

                                        <td><button class="btn btn-primary" @click="viewUser(cambista)"><i class="fa fa-edit"></i></button></td>
                                           <td v-if="cambista.situacao == 'ativo'" ><button class="btn btn-warning" @click="editeruserSituacao(cambista.id, 'bloqueado')" title="Bloquear"><i class="fa fa-unlock-alt"></i></button></td>
                                        <td v-if="cambista.situacao == 'bloqueado'" ><button class="btn btn-success" @click="editeruserSituacao(cambista.id, 'ativo')" title="Ativar"><i class="fa fa-unlock"></i></button></td>
                                        <td><button class="btn btn-danger" @click="deleteCambista(cambista)" ><i class="fa fa-trash" ></i></button></td>
                                      </tr>
                                    </tbody>

                                  </table>

                                   <scale-loader :loading="loading"></scale-loader>
                            </div>
                        </div>
                    </div>

                </div>             
            </div>
        </div>
   </div>
   
 </template>


 <style>
 .ativo {

     color: #008D4C;
 }
 .bloqueado {

     color: #D73925;
 }
 .int-comissao {
     padding-top: 10px;
 }
 </style>
 
<script>
export default {
    
    created() {
      this.loadCambista()
      this.loadGerente()    
      this.loadUserLogado()

             
    },
    data() {
        return {
           table: false,
           loading: false,
           searchName: '', 
           nameCambista: '',
           cambistas: [],
           cambista: {},
           gerente: '',
           gerentes: [],
           user: {},
           
          
        }
    },

    filters: {
        formatMoeda(numero) {
                var numero = numero.toFixed(2).split('.');
                numero[0] = "R$ " + numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
            },
        formatDate(date) {
                return moment(date).format("DD/MM HH:mm");
        },
      },
    methods: {
        loadUserLogado () {
            axios.get('/admin/user-logado')
                    .then((response)=>{

                            this.user = response.data;
                            console.log(this.user)
                    })
        },
       loadCambista () {
           this.loading = true;
            axios.get('/admin/list-cambistas')
                    .then((response)=>{
                        this.cambistas = response.data; 
                        console.log(this.cambistas);
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
        viewUser(cambista) {
            this.cambista = cambista;
             $('#modal-editar-user').modal('show');
        },
        editUser(cambista) {
            if(cambista.saldo_simples == "") return;
            this.cambista = cambista;
            axios.put('editar-cambista/'+cambista.id,this.cambista)
                    .then(()=>{
                        this.$notify({
                            group: 'foo',
                            title: 'Sucesso!',
                            text: 'Dados alterados com sucesso!',
                            type: 'success',
                            duration: 4000,
                            speed: 1000,
                        })
                    })
                     .catch(()=>{
                         $('#modal-editar-user').modal('hide');
                         this.$notify({
                            group: 'foo',
                            title: 'Error!',
                            text: 'Erro ao alterar os dados!',
                            type: 'error',
                            duration: 4000,
                            speed: 1000,
                         })
                    })
                    .finally(()=> {
                         $('#modal-editar-user').modal('hide');
                    })
        },
        searchUser() {
            
             if(this.searchName.length == 0){
                    this.$notify({
                        group: 'foo',
                        title: 'Erro!',
                        text: 'Campo de busca Vazio!',
                        type: 'warn',
                        duration: 4000,
                        speed: 1000,
                    })
                    return false;
            }
            this.loading = true;
            this.dadosCambistas = false;


            axios.get('/admin/search-cambista/'+this.searchName)
                    .then((response)=>{
                        this.cambistas = response.data; 
                    })
                    .catch(()=> {
                        this.loading = false;
                        this.dadosGerentes = true;
                    })
                    .finally(()=>{
                        this.loading = false;
                        this.dadosCambista = true;
                    })
                
        },
        deleteCambista(cambista) {
            this.cambista = cambista;
            $('#modal-excluir-user').modal('show');
        },
        editeruserSituacao(id, situacao) {
           

             axios.post('/admin/alterar-user',{id:id, situacao:situacao})
                    .then((response)=>{
                        
                        console.log(response)
                    this.$notify({
                        group: 'foo',
                        title: 'Sucesso!',
                        text: 'Dados alterados com sucesso!',
                        type: 'success',
                        duration: 4000,
                        speed: 1000,
                    })
                    }).catch(()=>{
                        
                         this.$notify({
                            group: 'foo',
                            title: 'Error!',
                            text: 'Erro ao alterar os dados!',
                            type: 'error',
                            duration: 4000,
                            speed: 1000,
                         })
                    })
                    .finally(()=>{
                         this.loadCambista();  
                    })
        },
        deleteUser(id){
            axios.delete('/admin/deletar-cambista/'+id)
                    .then(() =>{
                        this.$notify({
                                group: 'foo',
                                title: 'Erro!',
                                text: 'Usuário deletado com sucesso!',
                                type: 'success',
                                duration: 4000,
                                speed: 1000,
                          })
                        $('#modal-excluir-user').modal('hide');
                        this.loadCambista();
                   
                    })
                    .catch((err) =>{
                        $('#modal-excluir-user').modal('hide');
                          this.$notify({
                                group: 'foo',
                                title: 'Erro!',
                                text: 'Usuário não excluído!',
                                type: 'error',
                                duration: 4000,
                                speed: 1000,
                          })
                    })

        },
        loadGerente() {
            axios.get('/admin/list-gerentes')
                .then((response)=>{
                    this.gerentes = response.data; 
                    console.log(response)
                })
                .catch(()=> {
                    this.loading = false;
                    this.dadosgerentes = true;
                })
                
    },
    }
}
</script>