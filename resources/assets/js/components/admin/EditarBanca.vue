<template>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box box-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Nome</label>
                                    <input type="text" class="form-control" name="nome"  placeholder="Insira o nome">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">E-mail</label>
                                    <input type="email" name="email" class="form-control"  placeholder="Insira o nome">
                                </div>
                            </div>
                            <div class="col-md-3">
                                 <label >Logomarca</label>
                                 <div class="input-group">
                                    <input type="text" :value="files.logo" readonly class="form-control" placeholder="Insira a logo aqui">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" @click="showFilePicker">
                                            <i class="fa  fa-file-image-o"></i>
                                        </button>
                                    </span>
                                    <input type="file" ref="logo" style="display:none;" @change="onChange">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-3">
                                  <label >Banner 1</label>
                                 <div class="input-group">
                                    <input type="text" :value="files.name" readonly class="form-control" placeholder="Insira o Banner">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" @click="showFilePicker">
                                            <i class="fa  fa-file-image-o"></i>
                                        </button>
                                    </span>
                                    <input type="file" ref="banner1" style="display:none;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Banner 2</label>
                                    <input type="file" name="banner2" id="exampleInputFile">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Banner 3</label>
                                    <input type="file" name="banner3" id="exampleInputFile">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row">
                                <button class="btn btn-success" @click="updateDados()"><i class="fa fa-refresh"></i> Atualizar</button>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row">
                                <h2><i class="fa fa-info-circle"></i> Dimensões das imagens:</h2>
                                <h3><i class="fa fa-file-photo-o"></i> Logomarca:<small> 55 X 55 px</small></h3>
                                <h3><i class="fa fa-file-photo-o"></i> Banners:<small> 1024 X 270 px</small></h3>  
                            </div>
                        </div>
                     
                        <br><br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>


export default {
    
    created() {
        
        console.log('editar banca');
   
        
    },
    data() {
        return {
            loading: true,
            files: {},
        }
    },
    methods: {
        updateDados () {
                alert("estou aqui")
        },
        showFilePicker () {
            this.$refs.logo.click();
        },
        onChange (event) {
            this.files = event.target.files[0]
        }
    }
}
</script>

