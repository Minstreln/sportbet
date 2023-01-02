<template>
    <div class="content">
        <div class="row">
                <div class="col-md-12">
                    <div class="box box-success">                   
                    <div class="box-header with-border">
                        <h3 class="box-title"> <i class="fa  fa-flask"></i> Termômetro de Apostas </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>                              
                    </div>
                    <div class="box-body">
                            <div class="info-box bg-cinza-claro">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Quantidade de Apostas</span>
                                    <span class="info-box-number">{{quantidade  }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div> <!-- /.quantidade -->

                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Entradas</span>
                                    <span class="info-box-number"> {{entradas | formatMoeda() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div> <!-- /.entradas -->

                            <div class="info-box bg-yellow">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Entradas em Aberto</span>
                                    <span class="info-box-number"> {{entradas_abertas | formatMoeda() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div> <!-- /.entradas em aberto -->

                            <div class="info-box bg-red">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Saídas</span>
                                    <span class="info-box-number"> {{saidas | formatMoeda() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div> <!-- /.saídas -->

                             <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Lançamentos</span>
                                    <span class="info-box-number"> {{lancamentos | formatMoeda() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div> <!-- /.lançamentos-->

                             <div class="info-box bg-cinza">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Comissões</span>
                                    <span class="info-box-number"> {{comissoes | formatMoeda() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div> <!-- /.comissões-->

                             <div class="info-box bg-green" v-if="total >= 0">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Saldo</span>
                                    <span class="info-box-number"> {{total | formatMoeda() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div> 

                             <div class="info-box bg-red" v-if="total < 0">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Saldo</span>
                                    <span class="info-box-number"> {{total | formatMoeda() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div> 
                            
                            
                            <!-- /.saldo-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
    .valor-total{
        background: #000;
        color: #FFF;
    }
    .valor-fechamento {
        width: 100%;
        height: auto;
        padding: 8px;
        background: #00466A;
        color: #FFF;
        font-size: 20px;
        text-align: center;
        border-bottom: 1pt solid #FFF;
    }
    .bg-cinza {
       background:  #2C3B41;
       color: #FFF;
    }
    .bg-cinza-claro {
        background: #D2D6DE;
        color: #000;
    }
    .valor-fechamento-total-positivo {
        width: 100%;
        height: auto;
        padding: 8px;
        background: #11721D;
        color: #FFF;
        font-size: 20px;
        text-align: center;
    }

    .valor-fechamento-total-negativo {
        width: 100%;
        height: auto;
        padding: 8px;
        background: #D73925;
        color: #FFF;
        font-size: 20px;
        text-align: center;
    }
</style>

<script>
export default {
    
    created() {
        this.loadRelatorio()
    },
    data () {
      return {
        quantidade: 0,
        entradas: 0,
        entradas_abertas: 0,
        saidas: 0,
        comissoes: 0,
        lancamentos: 0,
        total: 0,
        loading: true,
      }
    },
    filters: {
        formatMoeda(numero) {
                return "R$ " + numero.toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1.");
        },
    },
    methods: {
        loadRelatorio () {
            
            axios.get('/admin/relatorio-home')
                .then((response) => {

                    this.relaorios = response.data
                    this.quantidade = response.data.quantidade;
                    this.entradas = response.data.entradas;
                    this.entradas_abertas = response.data.entradas_abertas;
                    this.saidas = response.data.saidas;
                    this.comissoes = response.data.comissoes;
                    this.lancamentos = response.data.lancamentos;
                    this.total = response.data.total;
                })
                .catch(()=>{

                })
                .finally(()=>{
                    this.loading = false
                })
        }
    }
}
</script>

