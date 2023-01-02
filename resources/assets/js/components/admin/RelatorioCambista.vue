<template>
  
    <div class="box box-primary">
        <div class="content">
           <div class="row">
         
           

               <div class="col-md-2">
                      <div class="form-group">
                        <label>Cambista:</label>
                        <select class="form-control" v-model="idCambista">
                            <option>Todos</option>
                            <option  v-for="cambista in cambistas" :value="cambista.id" :key="cambista.id">{{cambista.name}}</option>
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
                                            <th class="tabela-class">CAMBISTA</th>
                                            <th class="tabela-class">QUANTIDADE</th>
                                            <th class="tabela-class">ENTRADAS </th>
                                            <th class="tabela-class">SAÍDAS</th>
                                            <th class="tabela-class">COMISSÕES {{cambistas_relatorios.length}}</th>
                                            <th class="tabela-class">SALDO</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">                                 
                                        <tr v-for="cambista_relatorio in cambistas_relatorios" :key="cambista_relatorio.id" > 
                                            <td>{{cambista_relatorio.name}}</td>
                                            <td>{{cambista_relatorio.quantidade}}</td>
            
                                            <td class="positivo">{{cambista_relatorio.entradas | formatMoeda()  }}</td>
                                            <td class="negativo">{{cambista_relatorio.saidas | formatMoeda()  }}</td>
                                            <td>{{cambista_relatorio.comissaocambista | formatMoeda()  }}</td>
                                            <td v-if="cambista_relatorio.saldo < 0" class="negativo">{{cambista_relatorio.saldo | formatMoeda() }}</td>
                                            <td v-if="cambista_relatorio.saldo >= 0" class="positivo">{{cambista_relatorio.saldo | formatMoeda() }}</td>
                                        </tr> 
                                        <tr class="gerente" v-if="total_quantidade_apostas > 0 && cambistas_relatorios.length > 1">
                                            <td>TOTAL</td>
                                            <td>{{total_quantidade_apostas }}</td>
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
      this.loadCambistas();  
      this.date1 = moment(new Date()).format('YYYY-MM-DD')
      this.date2 = moment(new Date()).format('YYYY-MM-DD')
    },
    data() {
        return {
        
           
           idCambista: 'Todos',
           loading: false,
           cambistas_relatorios: [],
           cambistas: [],
           total_quantidade_apostas: 0,
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
           
        loadCambistas() {
             axios.get('/admin/list-cambistas')
                    .then((response)=>{
                        this.cambistas = response.data; 
                       
                    })
                    .catch(()=> {
                        this.loading = false;
                       
                    })
                    .finally(()=>{
                        this.loading = false;
                      
                    })
        },
       
           
        searchRelatorio () {
            this.loading = true;
            this.cambistas_relatorios = [];
            this.total_geral = 0;
            this.total_quantidade_apostas = 0;
            this.total_entradas = 0;
            this.total_saidas = 0;
            this.total_comissaocambista = 0;
            this.total_geral = 0;
              axios.post('/admin/search-relatorio-cambista', {cambista:this.idCambista, date1:this.date1, date2:this.date2})
                    .then((response)=>{
                     
                           this.cambistas_relatorios = response.data;
                            console.log(this.cambistas_relatorios)
                            for (var i = 0; i < this.cambistas_relatorios.length; i++) {

                                console.log(this.cambistas_relatorios[i].quantidade)
                                    this.total_quantidade_apostas   = this.cambistas_relatorios[i].quantidade + this.total_quantidade_apostas;
                                    this.total_entradas             = this.cambistas_relatorios[i].entradas + this.total_entradas;
                                    this.total_saidas               = this.cambistas_relatorios[i].saidas + this.total_saidas;
                                    this.total_comissaocambista     = this.cambistas_relatorios[i].comissaocambista + this.total_comissaocambista;
                                    this.total_geral                = this.cambistas_relatorios[i].saldo + this.total_geral;
                            }
                    }).finally(()=>{
                        this.loading = false;
                    })
        }
        

      
    }

}
</script>

