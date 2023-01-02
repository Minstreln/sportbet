 <template>

    <div class="row"> 
         <notifications group="foo" />
      <!--MODAL EXCLUIR USER-->
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
                                  <h4 >Deseja realtemente excluir o gerente(a):  <b>{{gerente.name}}</b>?</h4>
                              </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger pull-left" data-dismiss="modal"><i class="fa fa-thumbs-o-down"></i> NÃO</button>
                      <button type="button" class="btn btn-success" @click="deleteUser(gerente.id)" ><i class="fa fa-thumbs-o-up"></i> SIM</button>
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
                      <h4 class="modal-title"><i class="fa fa-user"></i> <b>{{gerente.name}}</b></h4>
                    </div>
                   
                    <div class="modal-body box box-primary">
                        <div class="row">
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
                <label for="exampleInputEmail1">Alterar Senha</label>
                  <input type="text" v-model="gerente.password"  class="form-control"  placeholder="Nova senha">
            </div>     
            
        </div>
         <div class="col-md-4">
            <div class="form-group">
                  <label for="exampleInputEmail1">Contato</label>
                  <input type="tel" v-mask="['(##) ####-####', '(##) #####-####']"  class="form-control" v-model="gerente.contato"  placeholder="Insira o contato">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Endereço</label>
                  <input type="text" class="form-control"  v-model="gerente.endereco" placeholder="Insira o Endereço">
            </div>
  
           
        </div>
         <div class="col-md-4">

            <div class="form-group" >
                  <label for="exampleInputEmail1">Saldo</label>
                  <input type="number" class="form-control" v-model="gerente.saldo_gerente" placeholder="Insira o Saldo">
            </div>
   
            <div class="form-group" >
                  <label for="exampleInputEmail1">Comissão Sobre Lucro %</label>
                  <input type="number" class="form-control" v-model="gerente.comissao_gerente"  placeholder="Insira a Sobre Lucro">
            </div>
        
              
          

        </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-success" @click="editUser(gerente)" ><i class="fa fa-send"></i> ALTERAR</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
      </div><!--End Modal excluir-user-->
  
    <!--Corpo da página-->
    <div class="box box-primary">
        <div class="content">
            <div class="box-header with-border">
                <div class="row">
                     <div class="input-group input-group col-md-3 pull-left">
                        <input type="text" class="form-control"   v-model="searchName"  placeholder="Buscar por nome">
                        <span class="input-group-btn">
                          <button type="button" class="btn btn-success btn-flat" @click="searchUser()"><i class="fa fa-search" ></i></button>
                        </span>
                      </div>
                      
                       <div class="box-tools  pull-right">
                          <a href="cadastrar-gerentes" class="btn btn-success"><i class="fa fa-plus"></i> Novo Gerente</a>
                      </div>  
                </div>

                <div class="row">
                        <div class="box-body"> 

                               
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover ">
                                    <thead class="tabela-class" >
                                        <tr>
                                            <th class="tabela-class">NOME</th>
                                            <th class="tabela-class">CADASTRADO</th>
                                            <th class="tabela-class">SITUACAO</th>
                                            <th class="tabela-class">SALDO</th>
                                            <th class="tabela-class">CONTATO</th>
                                            <th class="tabela-class">EDITAR</th>
                                            <th class="tabela-class">AÇÃO</th>
                                            <th class="tabela-class">EXCLUIR</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class" v-if="dadosGerentes">
                                       
                                      <tr v-for="gerente in gerentes"   :key="gerente.id" v-if="gerente.nivel != 'adm'">
                                        <td>{{ gerente.name }}</td>
                                        <td>{{ gerente.created_at | formatDate() }}</td>
                                        <td v-if="gerente.situacao == 'ativo'"><i class="fa fa-check ativo"></i>Ativo</td>
                                        <td v-if="gerente.situacao == 'bloqueado'" ><i class="fa fa-close bloqueado"></i>Bloqueado</td>
                                        <td>
                                          {{gerente.saldo_gerente | formatMoeda()}}
                                        </td>

                                        <td>{{ gerente.contato}}</td>                                  
                                        <td><button class="btn btn-primary" @click="viewUser(gerente)" title="Editar"><i class="fa fa-edit"></i></button></td>
                                        <td v-if="gerente.situacao == 'ativo'" ><button class="btn btn-warning" @click="editeruserSituacao(gerente.id, 'bloqueado')" title="Bloquear"><i class="fa fa-unlock-alt"></i></button></td>
                                        <td v-if="gerente.situacao == 'bloqueado'" ><button class="btn btn-success" @click="editeruserSituacao(gerente.id, 'ativo')" title="Ativar"><i class="fa fa-unlock"></i></button></td>
                                        <td><button class="btn btn-danger" @click="deleteGerente(gerente)" title="Excluir"><i class="fa fa-trash" ></i></button></td>
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
 .adm {
     background: #1A2226;
     color: #FFF;
 }
 .ativo {

     color: #008D4C;
 }
 .bloqueado {

     color: #D73925;
 }
 </style>
 
<script>
export default {
    
    created() {
      this.loadGerentes()               
    },
    data() {
        return {
           dadosGerentes: true,
           searchName: '',
           table: false,
           loading: false,
           nameGerente: '',
           gerente: {},
           gerentes:[],          
        }
    },

     filters: {
        formatMoeda(numero) {
                var numero = numero.toFixed(2).split('.');
                numero[0] = "R$ " + numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
            },
        formatDate(date) {
                return moment(date).format('DD/MM/YYYY HH-mm-ss');
        },
      },
    methods: {
        loadGerentes () {
            this.loading = true;
            axios.get('/admin/list-gerentes')
                    .then((response)=>{
                        this.gerentes = response.data; 
                    })
                    .catch(()=> {
                        this.loading = false;
                        this.dadosGerentes = true;
                    })
                    .finally(()=>{
                        this.loading = false;
                        this.dadosGerentes = true;
                    })
                
        },
        viewUser(gerente) {
            this.gerente = gerente;
            this.dados = gerente.dados_gerente;
            $('#modal-editar-user').modal('show');
        },
        editUser(gerente) {
             this.gerente = gerente;
            axios.put('editar-gerente/'+gerente.id,this.gerente)
                    .then((response)=>{
                        

                    this.$notify({
                        group: 'foo',
                        title: 'Sucesso!',
                        text: 'Dados alterados com sucesso!',
                        type: 'success',
                        duration: 4000,
                        speed: 1000,
                    })
                    }).catch(()=>{
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
        editeruserSituacao(id, situacao) {
           
             axios.post('/admin/alterar-user',{id:id, situacao:situacao})
                    .then((response)=>{
                    
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
                        this.loadGerentes();       
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
            this.dadosGerentes = false;


            axios.get('/admin/search-gerente/'+this.searchName)
                    .then((response)=>{
                        this.gerentes = response.data; 
                    })
                    .catch(()=> {
                        this.loading = false;
                        this.dadosGerentes = true;
                    })
                    .finally(()=>{
                        this.loading = false;
                        this.dadosGerentes = true;
                    })
                
        },
        deleteGerente(gerente) {
            this.gerente = gerente;
            $('#modal-excluir-user').modal('show');
        },
        deleteUser(id){
            axios.delete('/admin/deletar-gerente/'+id)
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
                        this.loadGerentes();
                   
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

        }
    }
}
</script>