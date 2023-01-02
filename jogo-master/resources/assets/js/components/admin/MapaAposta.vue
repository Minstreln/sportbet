<template>
    <div class="box box-primary">
        <div class="content">
            <div class="row">

                    <div class="modal fade in"  id="modal-options" >
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header top-match">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true"><i class="fa fa-close"></i></span></button>
                                <h4 class="modal-title"><i class="fa fa-futbol-o"></i> <span class="top-match"><b>{{detalhe_match.confronto}}</b></span></h4>
                                </div>

                                <div class="modal-body box box-primary">
                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="box-info-match"> DATA </div>
                                            <div class="box-valor-match"> {{detalhe_match.date | formatDate() }} </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="box-info-match"> TOTAL APOSTADO </div>
                                            <div class="box-valor-match"> {{total_apostado_geral | formatMoeda()}} </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="box-info-match"> QUANTIDADE  </div>
                                            <div class="box-valor-match"> {{detalhe_match.quantidade}} </div>
                                        </div>


                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead class="tabela-class" >
                                                                <tr class="header-tabela">
                                                                    <th class="tabela-class">TIPO DE APOSTA</th>
                                                                    <th class="tabela-class">OPÇÃO</th>
                                                                    <th class="tabela-class">QTD.</th>
                                                                    <th class="tabela-class">VALOR APOSTADO </th>                                                                    
                                                                </tr>
                                                            </thead>
                                                            
                                                            <tbody class="tabela-class">
                                                            
                                                                <tr v-for="opp in detalhe_match.opps" :key="opp.id">
                                                                    <td>{{opp.group_opp}}</td>
                                                                    <td>{{opp.opcao}}</td>
                                                                    <td>{{opp.quantidade}}</td>
                                                                    <td>{{opp.total_apostado | formatMoeda()}}</td>
                                                                </tr>
                                                                
                                                            </tbody>
                                                        </table>
                                        

                                            </div>
                                    </div>


                                    </div>
                                </div>
                                <div class="modal-footer">
                              
                                </div>
                            </div>
                            <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                </div><!--End Modal Optiona-->

               <div class="col-md-12">
                    <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="tabela-class" >
                                        <tr class="header-tabela">
                                            <th class="tabela-class">ESPORTE</th>
                                            <th class="tabela-class">DATA | HORA</th>
                                            <th class="tabela-class">PARTIDA</th>
                                            <th class="tabela-class">QUANTIDADE </th>
                                            <th class="tabela-class">VALOR ACUMULADO </th>
                                            <th class="tabela-class">DETALHES</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                       
                                        <tr v-for="match in maps_match" :key="match.id">
                                            <td>{{match.sport}}</td>
                                            <td>{{match.date | formatDate() }}</td>
                                            <td>{{ match.confronto}} </td>
                                            <td>{{match.quantidade}}</td>
                                            <td>{{match.total_apostado | formatMoeda() }}</td>
                                            <td><button class="btn btn-success" @click="viewOptions(match)"><i class="fa fa-plus"></i></button></td>
                                            
                                        </tr>
                                        
                                    </tbody>
                                </table>
                                <scale-loader :loading="loading"></scale-loader>

                    </div>
               </div>
           </div>
        </div>
    </div>
</template>
<style>
    .tabela-class {
        text-align: center;
    }

     .header-tabela {
        background: #00466A;
        color:#FFF;
    }
    .top-match {
        text-align: center;
        color: #00466A;
    }
    .box-info-match {
        background: #00466A;
        color: #FFF;
        width: 100%;
        height: auto;
        padding: 8px;
        font-size: 16px;
        text-align: center;
        display: block;
    }
    .box-valor-match {
        width: 100%;
        height: auto;
        font-size: 16px;
        color: black;
        border: 1pt solid #00466A;
        padding: 8px;
        text-align: center;
        margin-bottom: 10px;
    }
</style>

<script>
export default {
    
    created () {
        this.loadMaps()
    },
    data () {
        return {

            maps_match: [],
            detalhe_match: {},
            loading: false,
            total_apostado_geral: 0,
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
        
        loadMaps () {
            this.loading = true
            axios.get('/admin/list-map-aposta')
                    .then((response)=>{
                        this.maps_match = response.data
                    })
                    .catch(()=>{

                    })
                    .finally(()=>{
                        this.loading = false
                    })
        },
        viewOptions (data) {
            this.total_apostado_geral = data.total_apostado
            this.detalhe_match = data
            $('#modal-options').modal('show');
               
        }
    }    
}
</script>