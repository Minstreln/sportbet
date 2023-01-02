<template>
   
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box box-body">
                     <notifications group="foo" />
                    <div class="container">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Nome</label>
                                    <input type="text" v-model="user.name" class="form-control"   placeholder="Insira o nome">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Login</label>
                                    <input type="text" v-model="user.username"  class="form-control"   placeholder="Insira o login">
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">E-mail</label>
                                    <input type="text" v-model="user.email"  class="form-control"   placeholder="Insira o e-mail">
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Alterar Senha</label>
                                    <input type="text"  class="form-control" v-model="user.password"  placeholder="Nova senha">
                                </div>   
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                 <button class="btn btn-success" @click="edtUser(user)"><i class="fa fa-edit" ></i> Alterar</button>
                            </div>    
                        </div>
                        
                    </div>
            </div>
        </div>
    </div>
</template>

<script>


export default {
    
    created() {
        
        this.loadUserLogado();
   
        
    },
    data() {
        return {
           user: {},
        }
    },
    methods: {
        loadUserLogado () {
            axios.get('/admin/user-logado')
                    .then((response)=>{

                            this.user = response.data;

                            console.log(response)
                            
                    })
        },
        edtUser(user) {
            this.user = user;
            axios.put('editar-cambista/'+user.id,this.user)
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
                        
                    })
        },

    }
}
</script>

