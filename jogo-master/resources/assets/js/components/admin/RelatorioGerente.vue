<template>
  
    <div class="box box-primary">
        <div class="content">
           <div class="row">
         
           

               <div class="col-md-2">
                      <div class="form-group">
                        <label>Gerente:</label>
                        <select class="form-control" v-model="idGerente">
                            <option>Todos</option>
                            <option  v-for="gerente in gerentes" :value="gerente.id" :key="gerente.id">{{gerente.name}}</option>
                        </select>

                       </div>
               </div>   

            
               <div class="col-md-2">
                      <div class="form-group">
                        <label>De:</label>
                        <input type="date" v-model="date1" class="form-control" >
                    </div>
               </div>

                <div class="col-md-2">
                      <div class="form-group">
                        <label>Até:</label>
                        <input type="date" v-model="date2" class="form-control" >
                    </div>
               </div>

               <div class="col-md-2">
                   <div class="form-group">
                       <label>Persquisar</label>
                         <button class="btn btn-success form-control" @click="searchRelatorio()"><i class="fa fa-search "></i></button>
                   </div>
               </div>

               
           </div>

           <div class="row">
               <div class="col-md-12">
                    <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="tabela-class" >
                                        <tr class="gerente">
                                            <th class="tabela-class">GERENTE</th>
                                            <th class="tabela-class">QUANTIDADE</th>
                                            <th class="tabela-class">COMISSÕES (GERENTE)</th>
                                            <th class="tabela-class">ENTRADAS </th>
                                            <th class="tabela-class">SAÍDAS</th>
                                            <th class="tabela-class">COMISSÕES (CAMBISTAS)</th>
                                            <th class="tabela-class">SALDO</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">                                 
                                        <tr v-for="gerente_relatorio in gerentes_relatorios" :key="gerente_relatorio.id" > 
                                            <td>{{gerente_relatorio.name}}</td>
                                            <td>{{gerente_relatorio.quantidade}}</td>
                                            <td>{{gerente_relatorio.comissao_gerente | formatMoeda()  }}</td>
                                            <td class="positivo">{{gerente_relatorio.entradas | formatMoeda()  }}</td>
                                            <td class="negativo">{{gerente_relatorio.saidas | formatMoeda()  }}</td>
                                            <td>{{gerente_relatorio.comissaocambista | formatMoeda()  }}</td>
                                            <td v-if="gerente_relatorio.saldo < 0" class="negativo">{{gerente_relatorio.saldo | formatMoeda() }}</td>
                                            <td v-if="gerente_relatorio.saldo >= 0" class="positivo">{{gerente_relatorio.saldo | formatMoeda() }}</td>
                                        </tr> 
                                        <tr class="gerente" v-if="total_quantidade_apostas > 0 && gerentes_relatorios.length > 1">
                                            <td>TOTAL</td>
                                            <td>{{total_quantidade_apostas }}</td>
                                            <td>{{total_comissao_gerente | formatMoeda()}}</td>
                                            <td class="positivo">{{total_entradas | formatMoeda()}}</td>
                                            <td class="negativo">{{total_saidas | formatMoeda()}}</td>
                                            <td >{{ total_comissaocambista | formatMoeda() }}</td>
                                            <td v-if="total_geral < 0" class="negativo">{{ total_geral | formatMoeda() }}</td>
                                            <td v-if="total_geral >= 0" class="positivo">{{ total_geral | formatMoeda() }}</td>
                                           
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
    .body-bilhete {
        background:#F8ECC2;
        color: #000;   
    }
    .gerente {
        background: #00466A;
        color:#FFF;
        font-size: 13px;
        text-align: center;
    }
    .tabela-class {
        text-align: center;
    }
    .positivo {
        background: #009688;
        color: #FFF;
    }

    .negativo {
        background: #FF0000;
        color: #FFF;
    }

    .title-bilhete {
        text-align: center;
    }
    .tipo-aposta {
        text-align: center;
        font-size: 16px;
        border-bottom: #000 1px dashed;
        padding-bottom: 8px;
    }
    .info-aposta-header {
        padding: 5px;
        border-bottom: #000 1px dashed;
        padding-bottom: 8px;
  
    }
    .info-aposta-header p {
        margin-bottom: 0px;
    }
    .aposta-footer {
        margin-bottom: 0px;
    }
    .header-palpite {
        width: 100%;
        height: 22px;
        margin-bottom: 20px;
        border-bottom:  1px  #000 dashed;
        font-size: 15px;
        padding-bottom: 9px;
    }
    .palpite-left {
      width: 49%;
      float: left;
      text-align: left;

    }
    .palpite-right {
        width: 49%;
        float: right;
        text-align: right;
    }
    .body-palpite {
        width: 100%;
        height: 170px;;
        margin-bottom: 20px;
        border-bottom:  1px  #000 dashed;
        font-size: 15px;
        padding-bottom: 9px;
    }
    .body-palpite p {
        margin-bottom: 0px;
    }
    .body-palpite-left {
      width: 49%;
      float: left;
      text-align: left;

    }
    .body-palpite-right {
        width: 49%;
        float: right;
        text-align: right;
    }
    .aposta-footer {
        width: 100%;
        height: 100px;
        margin-bottom: 20px;
        border-top: 1px  #000 dashed;
        border-bottom:  1px  #000 dashed;
    }
    .info-aposta-footer-left p {
        margin-bottom: 0px;
        
    }
    .info-aposta-footer-left {
      width: 49%;
      height: auto;
      float: left;
      text-align: left;
      font-size: 15px;
    }
    .info-aposta-footer-right p {
        margin-bottom: 0px;
    }
    .info-aposta-footer-right {
        width: 49%;
        height: auto;
        float: right;
        text-align: right;
        font-size: 15px;
    }
    .cupom-bilhete {
        text-align: center;
    }


    .Aberto {
        background: #00C0EF;
        color: #FFF;
    }
    .Perdeu {
        background: #FF0000;
        color: #FFF;
    }
    .Ganhou {
        background: #008D4C;
        color: #FFF;
    }
    .Cancelado {
        background: #E69222;
        color: #FFF;
    }

    .Devolvido {
        background: #331E1B;
        color: #FFF;
    }

    .vivo {
        background: #C06576;
        color: #000;
    }
    

    .pre {
        background: #075758;
        color: #FFFFFF;
    }

  
   
</style>
 
<script>
export default {

    
    created() {
      this.loadGerentes();  
      this.date1 = moment(new Date()).format('YYYY-MM-DD')
      this.date2 = moment(new Date()).format('YYYY-MM-DD')
    },
    data() {
        return {
        
           
           idGerente: 'Todos',
           statusAposta: 'Todos',
           tipoAposta: 'Todos',
           loading: false,
           loadingBilhete: false,
           gerentes_relatorios: [],
           gerentes: [],
           total_quantidade_apostas: 0,
           total_comissao_gerente: 0,
           total_comissaocambista: 0,
           total_entradas: 0,
           total_saidas: 0,
           total_geral: 0,
                //Price mask
                money: {
                    decimal: ',',
                    thousands: '.',
                    prefix: 'R$ ',
                    suffix: ' #',
                    precision: 2,
                    masked: false /* doesn't work with directive */
                }

           
        
        }
    },

  
    filters: {
            formatDate(date) {
                return moment(date).format('DD/MM HH:mm');
            },
            formatDate2(date) {
                return moment(date).format('DD/MM hh:mm');
            },
             formatCotacao(numero) {
                var numero = numero.toFixed(2).split('.');
                numero[0] =  numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
                
            },
            formatMoeda(numero) {
                    return "R$ " + numero.toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1.");
            },
            andamentoPalp(acertos, erros){


            },
            
    },
    methods: {
        loadBilhetes() {
                this.loading = true
                axios.get('mostra-bilhetes/')
                        .then((response)=>{
                            this.bilhetes = response.data
                                this.loading = false
                        })
                        .catch((err) =>{
                            console.log(err)
                        })
                        .finally(()=>{
                            this.loading = false
                        })
        },
     
        loadGerentes() {
             axios.get('/admin/list-gerentes')
                    .then((response)=>{
                        this.gerentes = response.data; 
                       
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
       
           
        searchRelatorio () {
            this.loading = true;
            this.gerentes_relatorios = [];
            this.total_geral = 0;
            this.total_quantidade_apostas = 0;
            this.total_comissao_gerente = 0;
            this.total_entradas = 0;
            this.total_saidas = 0;
            this.total_comissaocambista = 0;
              axios.post('/admin/search-relatorio-gerente', {gerente:this.idGerente, date1:this.date1, date2:this.date2})
                    .then((response)=>{
                           this.gerentes_relatorios = response.data;

                       
                            for (var i = 0; i < this.gerentes_relatorios.length; i++) {
                                    this.total_quantidade_apostas   = this.gerentes_relatorios[i].quantidade + this.total_quantidade_apostas;
                                    this.total_comissao_gerente     = this.gerentes_relatorios[i].comissao_gerente + this.total_comissao_gerente;
                                    this.total_entradas             = this.gerentes_relatorios[i].entradas + this.total_entradas;
                                    this.total_saidas               = this.gerentes_relatorios[i].saidas + this.total_saidas;
                                    this.total_comissaocambista     = this.gerentes_relatorios[i].comissaocambista + this.total_comissaocambista;
                                    this.total_geral                = this.gerentes_relatorios[i].saldo + this.total_geral;
                            }
                    }).finally(()=>{
                        this.loading = false;
                    })
        }
        

      
    }

}
</script>

