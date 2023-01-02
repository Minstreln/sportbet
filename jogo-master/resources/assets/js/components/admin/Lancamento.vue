<template>
    <div class="box box-primary">
        <div class="content">
            <div class="row">
               <div class="col-md-4">
                   <div class="box box-success">
                        <h4>Novo Lançamento</h4>
                            <div class="form-group">
                                    <label>Selecione um cambista</label>
                                    <select class="form-control" v-model="cambista_id">
                                        <option v-for="cambista in cambistas" :value="cambista.id" :key="cambista.id">{{cambista.name}}</option>
                                    </select>
                            </div>

                            <div class="form-group">
                                    <label>Tipo de Lançamento</label>
                                    <select class="form-control" v-model="tipo">
                                        <option >Crédito</option> 
                                        <option >Débito</option> 
                                    </select>
                            </div>
                            
                            <div class="form-group">
                                    <label for="exampleInputEmail1">Descrição</label>
                                    <input type="text" class="form-control" v-model="descricao"  placeholder="Insira uma descrição">
                            </div>

                            <div class="form-group">
                                    <label for="exampleInputEmail1">Valor</label>
                                    <input type="text" class="form-control"  v-model="valor"  placeholder="Insira um valor">
                            </div>

                            <button class="btn btn-success btn-block" @click="sendLancamento">Incluir Lançamento</button>


                        </div>
               </div>

                <div class="col-md-8">
                     <div class="box box-success">
                            <h4>Lançamentos de Caixa</h4>

                              <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="tabela-class" >
                                        <tr class="header-tabela">
                                            <th class="tabela-class">COLABORADOR</th>
                                            <th class="tabela-class">TIPO</th>
                                            <th class="tabela-class">DESCRIÇÃO </th>
                                            <th class="tabela-class">VALOR</th>
                                            <th class="tabela-class">DATA</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                        <tr v-for="lancamento in lancamentos" :key="lancamento.id">   
                                        <td>{{lancamento.name}}</td>
                                        <td>{{lancamento.tipo}}</td>
                                        <td>{{lancamento.descricao}}</td>
                                        <td>{{lancamento.valor | formatMoeda() }}</td>
                                        <td>{{lancamento.created_at | formatDate() }} hs</td>
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
</template>
<style>
     .header-tabela {
        background: #00466A;
        color:#FFF;
    }
</style>
<script>
export default {
    created() {
        this.loadCambista()
        this.loadLancamentos()
    },
    data () {
        return {
            cambista_id: '',
            cambistas: [],
            tipo: '',
            valor: 0,
            descricao: '',
            loading: false,
            lancamentos:[],
        }
    },
    filters: {
        formatMoeda(numero) {
                var numero = numero.toFixed(2).split('.');
                numero[0] = "R$ " + numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
        },
        formatDate(date) {
                return moment(date).format('DD/MM HH:mm');
        },
      },
    methods: {
        loadCambista() {
            axios.get('/admin/list-cambistas')
                .then((response)=>{
                    this.cambistas = response.data; 
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
        sendLancamento () {
            this.loading = true
                axios.post('/admin/lancamentos',{tipo:this.tipo, user_id:this.cambista_id, descricao:this.descricao, valor:this.valor})
                        .then((response)=>{
                            
                        })
                        .catch(()=>{

                        })
                        .finally(()=>{
                            this.tipo = ''
                            this.valor = ''
                            this.descricao = ''
                            this.cambista_id = ''
                            this.loading = false
                            this.loadLancamentos()
                        })
        },
        loadLancamentos () {
                this.loading = true
                axios.get('/admin/list-lancamentos')
                        .then((response)=>{
                            this. lancamentos = response.data
                        }).catch(()=>{

                        })
                        .finally(()=>{

                            this.loading = false
                        })
        }
    }
}
</script>
