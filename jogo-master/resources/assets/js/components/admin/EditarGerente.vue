<template>
  <div class="box box-primary">
    <div class="content">
        <div class="col-md-4">
            <div class="form-group">
                  <label for="exampleInputEmail1">Nome</label>
                  <input type="text" class="form-control"  placeholder="Insira o nome">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">E-mail</label>
                  <input type="text" class="form-control"  placeholder="Insira o e-maii">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Senha</label>
                  <input type="text" class="form-control"  placeholder="Insira a senha">
            </div>
           
        </div>
         <div class="col-md-4">
            <div class="form-group">
                  <label for="exampleInputEmail1">Contato</label>
                  <input type="text" class="form-control"  placeholder="Insira o contato">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Endereço</label>
                  <input type="text" class="form-control"  placeholder="Insira o Endereço">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Saldo</label>
                  <input type="number" class="form-control"  placeholder="Insira o Saldo">
            </div>
           
        </div>
         <div class="col-md-4">

            <div class="form-group">
                  <label for="exampleInputEmail1">Comissão Líquida %</label>
                  <input type="number" class="form-control"  placeholder="Insira a Comissão Líquida">
            </div>
            <div class="form-group">
                  <label for="exampleInputEmail1">Comissão Bruta %</label>
                  <input type="number" class="form-control"  placeholder="Insira a Comissão Bruta">
            </div>

            <div class="form-group">
                  <label>Situação</label>
                  <select class="form-control">
                    <option>Ativo</option>
                    <option>Bloqueado</option>
                  </select>
            </div>

        </div>

       
        
    </div>

      <div class="content">
            <div class="col-md-12">
                  <button class="btn btn-success"><i class="fa fa-edit"></i> Editar</button>
            </div>     
      </div>

  </div>
</template>


<script>
export default {
    
}
</script>

