<template>
   <div class="container">
        <scale-loader :loading="loading"></scale-loader>
            <div class="header-print-share" v-if="print">
               <a class="share-float" onclick="window.print();return false;" ><i class="fa fa-print send-print"></i></a>
            </div>
      
      <div class="row"  v-for="palpite in bilhetes" :key="palpite.id">
          <div class="bilhete">  
           
           <div v-bind:class="palpite.status" class="header-cupom"><h4>{{palpite.status}}</h4></div>
          
                     <div class="logo-bilhete"><b> <a><img  width="180" height="140" src="/img/logo.png"></a></b></div>

                <div class="tipo-aposta"><b>{{palpite.tipo}}</b></div>

                                <div class="info-aposta-header">
                                   <p><b>DATA:</b> {{palpite.created_at | formatDate()}}</p> 
                                    <p><b>VENDEDOR:</b> {{palpite.vendedor}}</p>
                                    <p><b>CLIENTE:</b> {{palpite.cliente}}</p>
                                    <p v-if="palpite.modalidade == 'Loto'"><b>SORTEIO:</b> {{palpite.concurso}}</p>
                                </div>


                                <div v-if="palpite.modalidade != 'Loto'">
                                <div class="header-palpite">
                                    <div class="palpite-left">
                                        <p>APOSTA</p>
                                    </div>
                                    <div class="palpite-right">
                                        <p>COTAÇÃO</p>
                                    </div>
                                </div>

                                <div class="body-palpite" v-for="palp in palpite.palpites" :key="palp.id">
                                    <p><b>{{palp.sport}} - {{palp.match_temp | formatDate() }}</b></p>
                                    <p>{{palp.league}}</p>
                                    <p>{{palp.home}}  X  {{palp.away}} - <b class="score">{{palp.score}}</b></p>
                                    <p><b>{{palp.group_opp}}</b></p>
                                    <span class="body-palpite-left">
                                      <p>{{palp.palpite}} - <span v-if="palp.type=='ao-vivo'">({{palp.type}})</span></p>
                                         <p>Status:</p>
                                    </span>
                                    <span class="body-palpite-right">
                                        <p>{{palp.cotacao | formatCotacao() }}</p>
                                        <p  v-bind:class="palp.status">{{palp.status}}</p>
                                    </span>
                                </div>
                                </div>

                                <div v-if="palpite.modalidade == 'Loto'">
                                  <div class="header-palpite-loto">

                                        <div  v-for="palp in palpite.palpites_loto" :key="palp.id" class="dezenas">-{{palp.dezena}}- </div>
                                  </div>

                                  <div class="resultado" v-if="palpite.status != 'Aberto'">
                                      <h4>RESULTADO</h4>
                                      <p>{{palpite.resultado_loto}}</p>
                                  </div>

                                </div>
                             

                                <h3 class="cupom-bilhete"><b>{{palpite.cupom}}</b></h3>

          <div class="aposta-footer" v-if="configuracoes.comissao_premio > 0">
              <div class="info-aposta-footer-left">
                <p>
                  <b>Quantidade de Jogos:</b>
                </p>
                <p>
                  <b>Acertos:</b>
                </p>
                <p>
                  <b>Cotação:</b>
                </p>
                <p>
                  <b>Total Apostado:</b>
                </p>
                <p>
                  <b>Site Paga:</b>
                </p>
                <p>
                  <b>Cambista Paga:</b>
                </p>
              </div>

              <div class="info-aposta-footer-right">
                <p>{{palpite.total_palpites}}</p>
                <p>{{palpite.acertos_palpites}}</p>
                <p>{{palpite.cotacao | formatCotacao() }}</p>
                <p>{{palpite.valor_apostado | formatMoeda() }}</p>
                <p>{{palpite.retorno_possivel | formatMoeda() }}</p>
                <p>{{palpite.retorno_cambista  | formatMoeda() }}</p>
              </div>
            </div>

             <div class="aposta-footer" v-else>
              <div class="info-aposta-footer-left">
                <p>
                  <b>Quantidade de Jogos:</b>
                </p>
                <p>
                  <b>Acertos:</b>
                </p>
                <p>
                  <b>Cotação:</b>
                </p>
                <p>
                  <b>Total Apostado:</b>
                </p>
                <p>
                  <b>Retorno Possível:</b>
                </p>
              </div>

              <div class="info-aposta-footer-right">
                <p>{{palpite.total_palpites}}</p>
                <p>{{palpite.acertos_palpites}}</p>
                <p>{{palpite.cotacao | formatCotacao() }}</p>
                <p>{{palpite.valor_apostado | formatMoeda() }}</p>
                <p>{{palpite.retorno_possivel | formatMoeda() }}</p>
              </div>
            </div>

            <div class="tipo-aposta"><b><h4>REGRAS</h4></b></div>

            <div class="regras"> {{regras}} </div>

          </div>
      </div>
    </div>
</template>
<style>

@media print {
 
.share-float, .header-cupom {
        display: none;
    }
  }

  @page{
  size: auto;
  margin: 0mm;
}

.bilhete {
  background: #f8ecc2;
  color: #000;
  padding: 8px;
}

.regras {
    width: 100%;
    height: auto;
    /* text-align: justify; */
    font-size: 12px;
    margin-top: 20px;
}

.dezenas {
  float: left;
}

.resultado {
  width: 100%;
  height: 100px;
  border-bottom: dashed 1px #000;
  padding: 4px;
  text-align: center;
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
.logo-bilhete {
  text-align: center;
  font-size: 16px;
  border-bottom: #000 1px dashed;
  padding-bottom: 8px;
  margin-top: 15px;
  margin-bottom: 20px;
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
.header-cupom {
  width: 100%;
  height: auto;
  padding: 4px;
  text-align: center;
}
.header-palpite {
  width: 100%;
  height: 22px;
  margin-bottom: 20px;
  border-bottom: 1px #000 dashed;
  font-size: 15px;
  padding-bottom: 9px;
}
.header-palpite-loto {
  width: 100%;
  height: 80px;
  margin-bottom: 20px;
  border-bottom: 1px #000 dashed;
  font-size: 15px;
  padding: 9px;
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
  height: 170px;
  margin-bottom: 20px;
  border-bottom: 1px #000 dashed;
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
  height: 145px;
  margin-bottom: 20px;
  border-top: 1px #000 dashed;
  border-bottom: 1px #000 dashed;
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
  background: #00c0ef;
  color: #fff;
}
.Perdeu {
  background: #ff0000;
  color: #fff;
}
.Ganhou {
  background: #008d4c;
  color: #fff;
}
.Cancelado {
  background: #e69222;
  color: #fff;
}
</style>

<script>
export default {
    created() {
           //  let bilheteId = window.location.pathname.split("/");
            //   console.log(bilheteId[3])
            this.loadRegras();
            let bilheteId = window.location.pathname.split("/");
            this.loadBilhete(bilheteId[3]);
            if(localStorage.getItem('token') != null) {
              this.print = true;
            } else {
              this.print = false;
            }
            
         
    },
    data() {
        return {
              bilhetes: [],
              loading: true,
              regras: '',
              print: '',
              configuracoes: {}
              
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
                var numero = numero.toFixed(2).split('.');
                numero[0] = "R$ " + numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
            },
            andamentoPalp(acertos, erros){


            },
            
    },
    methods: {
      loadRegras() {
        axios.get("/api/list-limites")
                .then((response) => {
                  this.configuracoes = response.data[0]
                   this.regras = response.data[0].texto_rodape
                   
                })
      },
  
        loadBilhete(id) {
            this.loading = true;
            axios.get('/api/print-bilhete-id/'+id)
                    .then((response)=> {
                      this.bilhetes = response.data;

                    })
                    .catch(error => {

                      console.log(error)
                    })
                    .finally(()=> {
                      this.loading = false;
                    })
        }
    }

   // /api/bilhete/
}
</script>
