<template>
    <div class="box box-primary">
        <div class="content">
             <notifications group="foo" />
            <div class="row" v-for="conf in configuracoes" :key="conf.id">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Valor mínimo por aposta (R$):</label>
                        <input type="number"  v-model="conf.valor_mini_aposta" class="form-control">
                      
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Valor máximo por aposta (R$):</label>
                        <input type="number" v-model="conf.valor_max_aposta" class="form-control">
                    </div>
                       <div class="form-group">
                        <label for="exampleInputEmail1">Valor mínimo por aposta(Loto) (R$):</label>
                        <input type="number"  v-model="conf.menor_valor_loto" class="form-control">
                      
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Valor máximo por aposta(Loto) (R$):</label>
                        <input type="number" v-model="conf.max_valor_loto" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Prêmio Máximo (R$):</label>
                        <input type="number" v-model="conf.premio_max" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Cotação Mínima no Bilhete:</label>
                        <input type="text" v-model="conf.cotacao_mini_bilhete" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Cotação Máxima no Bilhete:</label>
                        <input type="text" v-model="conf.cotacao_max_bilhete" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Tempo Limite Ao Vivo:</label>
                        <input type="number" v-model="conf.time_live" class="form-control">
                    </div>

                     <div class="form-group">
                        <label for="exampleInputEmail1">Cotação Ao Vivo:</label>
                        <input type="number" v-model="conf.cotacao_live" class="form-control">
                    </div>
                </div>
        

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Quantidade mínima de jogos por bilhete:</label>
                        <input type="number" v-model="conf.quantidade_jogos_mini_bilhete" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Quantidade máxima de jogos por bilhete:</label>
                        <input type="number" v-model="conf.quantidade_jogos_max_bilhete" class="form-control">
                    </div>

                  
                    <div class="form-group">
                        <label>Texto rodapé bilhete:</label>
                        <textarea class="form-control" v-model="conf.texto_rodape" rows="5" placeholder="Rodapé Bilhete"></textarea>
                    </div>


                    <div class="form-group">
                        <label for="exampleInputEmail1">Comissão sobre prêmio %:</label>
                        <input type="number" v-model="conf.comissao_premio" class="form-control">
                    </div>

                   

                    
                </div>

                <div class="col-md-3">
                   
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email (Alertas):</label>
                        <input type="text" v-model="conf.email_alerta" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Alertar apostas acima de (R$):</label>
                        <input type="number" v-model="conf.alerta_aposta_acima" class="form-control">
                    </div>
                     <div class="form-group">
                        <label>Cambistas Podem Cancelas os Bilhetes:</label>
                        <select class="form-control" v-model="conf.cambista_pode_cancelar">
                             <option>{{conf.cambista_pode_cancelar}}</option>
                             <option v-if="conf.cambista_pode_cancelar == 'Não'">Sim</option>
                             <option v-if="conf.cambista_pode_cancelar == 'Sim'">Não</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Tempo limite para cambistas cancelarem as apostas (minutos):</label>
                        <input type="number"  v-model="conf.tempo_limite_camb_cancela_aposta" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Travar odd acima de:</label>
                        <input type="number"  v-model="conf.travar_odd_acima" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="exampleInputEmail1">Bloquear odd abaixo de:</label>
                        <input type="number"  v-model="conf.bloquear_odd_abaixo" class="form-control">
                    </div>

                        <div class="form-group">
                        <label>Apostas Ativas:</label>
                        <select class="form-control" v-model="conf.aposta_ativa">
                             <option>{{conf.aposta_ativa}}</option>
                             <option v-if="conf.aposta_ativa == 'Não'">Sim</option>
                             <option v-if="conf.aposta_ativa == 'Sim'">Não</option>
                        </select>
                    </div>
                   

                </div>

                <div class="col-md-3">

                     <div class="form-group">
                        <label>Bloquear apostas na madrugada ( 01:00 às 05:59 ):</label>
                        <select class="form-control" v-model="conf.bloq_aposta_madrugada">
                             <option>{{conf.bloq_aposta_madrugada}}</option>
                             <option v-if="conf.bloq_aposta_madrugada == 'Não'">Sim</option>
                             <option v-if="conf.bloq_aposta_madrugada == 'Sim'">Não</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Data limite de jogos no sistema:</label>
                        <input type="date" class="form-control" v-model="conf.data_limite_jogos"  >
                    </div>


                     <div class="form-group">
                        <label>Futebol Ao Vivo? </label>
                        <select class="form-control" v-model="conf.futebol_ao_vivo">
                             <option>{{conf.futebol_ao_vivo}}</option>
                             <option v-if="conf.futebol_ao_vivo == 'Não'">Sim</option>
                             <option v-if="conf.futebol_ao_vivo == 'Sim'">Não</option>
                        </select>
                    </div>

                      <div class="form-group">
                        <label>Quininha</label>
                        <select class="form-control" v-model="conf.op_quininha">
                             <option>{{conf.op_quininha}}</option>
                             <option v-if="conf.op_quininha == 'Não'">Sim</option>
                             <option v-if="conf.op_quininha == 'Sim'">Não</option>
                        </select>
                    </div>

                      <div class="form-group">
                        <label>Seninha</label>
                        <select class="form-control" v-model="conf.op_seninha">
                             <option>{{conf.op_seninha}}</option>
                             <option v-if="conf.op_seninha == 'Não'">Sim</option>
                             <option v-if="conf.op_seninha == 'Sim'">Não</option>
                        </select>
                    </div>

                     <div class="form-group">
                        <label>Ufc/Box?</label>
                        <select class="form-control" v-model="conf.op_ufcbox" >
                             <option>{{conf.op_ufcbox}}</option>
                             <option v-if="conf.op_ufcbox == 'Não'">Sim</option>
                             <option v-if="conf.op_ufcbox == 'Sim'">Não</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Basquete?</label>
                        <select class="form-control" v-model="conf.op_basquete" >
                             <option>{{conf.op_basquete}}</option>
                             <option v-if="conf.op_basquete == 'Não'">Sim</option>
                             <option v-if="conf.op_basquete == 'Sim'">Não</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tênis?</label>
                        <select class="form-control" v-model="conf.op_tenis" >
                             <option>{{conf.op_tenis}}</option>
                             <option v-if="conf.op_tenis == 'Não'">Sim</option>
                             <option v-if="conf.op_tenis == 'Sim'">Não</option>
                        </select>
                    </div>




                </div>

                 <div class="col-md-12">
                    <button class="btn btn-success" @click="updateConfiguracao(conf.id, configuracoes)"><i class="fa fa-send-o"></i> Salvar</button>
                 </div>     
            </div>
       
        </div>
        
           
    
    </div>
</template>

<script>
export default {
    created() {
        this.loadConfiguracao()

        
    },

    data() {
        return {
            configuracoes: [],
            configuracao:{},
        }
    },
    methods:{
        loadConfiguracao() {
            axios.get('/admin/list-configuracoes')
                    .then((response)=>{
                        this.configuracoes = response.data;
                    }).catch(()=>{

                    })
        },
        updateConfiguracao(id, data) {

                axios.put('/admin/edit-configuracao/'+id,{data})
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
                         this.$notify({
                            group: 'foo',
                            title: 'Error!',
                            text: 'Erro ao alterar os dados!',
                            type: 'error',
                            duration: 4000,
                            speed: 1000,
                         })
                     })
                               
        }
    }

}
</script>
