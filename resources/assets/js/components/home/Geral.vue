
<template>
  <div class="wrapper">
    <notifications group="foo" />
    <!--Dados logado-->
    <div class="modal fade in" id="modal-login">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title"><i class="fa fa-lock"></i> Login</h4>
          </div>

          <div class="modal-body box box-primary">
            <div class="login-box-body">
              <div
                class="alert alert-danger alert-dismissible"
                v-if="errorLogin"
              >
                <h4><i class="icon fa fa-ban"></i> Alerta!</h4>
                {{ messageError }}
              </div>

              <div class="form-group has-feedback">
                <input
                  type="text"
                  class="form-control"
                  v-model="username"
                  placeholder="Login"
                />
                <span
                  class="glyphicon glyphicon-envelope form-control-feedback"
                ></span>
              </div>
              <div class="form-group has-feedback">
                <input
                  type="password"
                  v-model="password"
                  @keyup.enter="login()"
                  class="form-control"
                  placeholder="Senha"
                />
                <span
                  class="glyphicon glyphicon-lock form-control-feedback"
                ></span>
              </div>
              <div class="row">
                <div class="col-xs-8">
                  <div class="checkbox icheck">
                    <label>
                      <div
                        class="icheckbox_square-blue"
                        aria-checked="false"
                        aria-disabled="false"
                        style="position: relative"
                      >
                        <input
                          type="checkbox"
                          style="
                            position: absolute;
                            top: -20%;
                            left: -20%;
                            display: block;
                            width: 140%;
                            height: 140%;
                            margin: 0px;
                            padding: 0px;
                            background: rgb(255, 255, 255);
                            border: 0px;
                            opacity: 0;
                          "
                        />
                        <ins
                          class="iCheck-helper"
                          style="
                            position: absolute;
                            top: -20%;
                            left: -20%;
                            display: block;
                            width: 140%;
                            height: 140%;
                            margin: 0px;
                            padding: 0px;
                            background: rgb(255, 255, 255);
                            border: 0px;
                            opacity: 0;
                          "
                        ></ins>
                      </div>
                    </label>
                  </div>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                  <button
                    class="btn btn-success btn-block btn-flat"
                    @click="login()"
                  >
                    {{ text_btn_login }}
                  </button>
                </div>
                <!-- /.col -->
              </div>

              <!-- /.social-auth-links -->

              <a href="password/reset">Esqueceu a senha?</a>
              <br />
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal -->

    <div class="modal fade in" id="modal-caixa" v-if="logado">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-money"></i>
              <b>{{ name }}</b>
            </h4>
          </div>

          <div class="modal-body box box-primary">
            <div class="row">
              <!-- <scale-loader :loading="loadingCaixa"></scale-loader> -->
              <div class="col-md-12">
                <div class="valor-fechamento-positivo">
                  Quantidade de Bilhetes: {{ caixaUser.quantidade }}
                </div>
                <div class="valor-fechamento-positivo">
                  Apostas no Ponto: {{ caixaUser.entradas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-total-aberto">
                  Apostas Aguardando :
                  {{ caixaUser.entradas_abertas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-total-negativo">
                  Total Prêmios: {{ caixaUser.saidas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-positivo">
                  Adiantamentos: {{ caixaUser.lancamentos | formatMoeda() }}
                </div>
                <div class="valor-fechamento-positivo">
                  Comissões: {{ caixaUser.comissoes | formatMoeda() }}
                </div>
                <div
                  class="valor-fechamento-total-positivo"
                  v-if="caixaUser.total >= 0"
                >
                  Total: {{ caixaUser.total | formatMoeda() }}
                </div>
                <div
                  class="valor-fechamento-total-negativo"
                  v-if="caixaUser.total < 0"
                >
                  Total: {{ caixaUser.total | formatMoeda() }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal Caixa-->

    <div class="modal fade in" id="modal-relatorio" v-if="logado">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-money"></i>
              <b>Relatório</b>
            </h4>
          </div>

          <div class="modal-body box box-primary">
            <div class="form-inline relatorio">
              <div class="form-group">
                <label>De:</label>

                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input
                    type="date"
                    v-model="date1"
                    class="form-control pull-right"
                    id="datepicker"
                    @change="sendRelatorio()"
                  />
                </div>
                <!-- /.input group -->
                <label>Até:</label>

                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input
                    type="date"
                    v-model="date2"
                    class="form-control pull-right"
                    id="datepicker"
                    @change="sendRelatorio()"
                  />
                </div>
                <!-- /.input group -->
              </div>
            </div>
            <div class="row">
              <clip-loader
                :loading="loadingCaixa"
                :color="color"
                :size="size"
              ></clip-loader>
              <div
                class="col-md-12"
                v-if="Object.values(this.relatorio).length > 0"
              >
                <div class="valor-fechamento-positivo">
                  Quantidade: {{ relatorio.quantidade }}
                </div>
                <div class="valor-fechamento-positivo">
                  Entradas: {{ relatorio.entradas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-total-negativo">
                  Saídas: {{ relatorio.saidas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-positivo">
                  Comissões: {{ relatorio.comissaocambista | formatMoeda() }}
                </div>
                <div
                  class="valor-fechamento-total-positivo"
                  v-if="relatorio.saldo >= 0"
                >
                  Total: {{ relatorio.saldo | formatMoeda() }}
                </div>
                <div
                  class="valor-fechamento-total-negativo"
                  v-if="relatorio.saldo < 0"
                >
                  Total: {{ relatorio.saldo | formatMoeda() }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal Caixa-->
    <!--End dados logado-->

    <!--Modal Bilhete-->
    <div class="modal fade in" id="modal-bilhete">
      <div class="modal-dialog">
        <div
          class="modal-content"
          v-for="palpite in bilhetes"
          :key="palpite.id"
        >
          <div class="modal-header" v-bind:class="palpite.status">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title title-bilhete">
              <b>{{ palpite.status }}</b>
            </h4>
          </div>
          <div class="pre-aposta-resul" v-if="logar">
            <h4>
              OBS esses valores poderam ser alterados em decorência de algo que
              aconteça antes de começar o jogo. Procure o cambista mais próximo,
              informando o seguinte código:
              <b>{{ cupom_pre_aposta }}</b
              >. Para validação desta aposta:
            </h4>
          </div>
          <div class="header-print-share">
            <a v-bind:href="link" class="share-float">
              <i class="fa fa-whatsapp send-whats"></i>
            </a>
            <a
              v-if="logado"
              class="share-float"
              @click="printJogos(palpite.id)"
            >
              <i class="fa fa-print send-print"></i>
            </a>
          </div>

          <div class="modal-body body-bilhete">
            <div class="tipo-aposta">
              <b>{{ palpite.tipo }}</b>
            </div>

            <div class="info-aposta-header">
              <p>
                <b>DATA:</b>
                {{ palpite.created_at | formatDate() }}
              </p>
              <p>
                <b>VENDEDOR:</b>
                {{ palpite.vendedor }}
              </p>
              <p>
                <b>CLIENTE:</b>
                {{ palpite.cliente }}
              </p>
              <p v-if="palpite.modalidade == 'Loto'">
                <b>SORTEIO:</b>
                {{ palpite.concurso }}
              </p>
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

              <div
                class="body-palpite"
                v-for="palp in palpite.palpites"
                :key="palp.id"
              >
                <p>
                  <b>{{ palp.sport }} - {{ palp.match_temp | formatDate() }}</b>
                </p>
                <p>{{ palp.league }}</p>
                <p>
                  {{ palp.home }} X {{ palp.away }} -
                  <b class="score">{{ palp.score }}</b>
                </p>
                <p>
                  <b>{{ palp.group_opp }}</b>
                </p>
                <span class="body-palpite-left">
                  <p>
                    {{ palp.palpite }} -
                    <span v-if="palp.type == 'ao-vivo'">({{ palp.type }})</span>
                  </p>
                  <p>Status:</p>
                </span>
                <span class="body-palpite-right">
                  <p>{{ palp.cotacao | formatCotacao() }}</p>
                  <p v-bind:class="palp.status">{{ palp.status }}</p>
                </span>
              </div>
            </div>

            <div v-if="palpite.modalidade == 'Loto'">
              <div class="header-palpite-loto">
                <div
                  v-for="palp in palpite.palpites_loto"
                  :key="palp.id"
                  class="dezenas"
                >
                  -{{ palp.dezena }}-
                </div>
              </div>

              <div class="resultado" v-if="palpite.status != 'Aberto'">
                <h4>RESULTADO</h4>
                <p>{{ palpite.resultado_loto }}</p>
              </div>
            </div>

            <h3 class="cupom-bilhete">
              <b>{{ palpite.cupom }}</b>
            </h3>

            <div class="aposta-footer" v-if="limitesUser.comissao_premio > 0">
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
                <p>{{ palpite.total_palpites }}</p>
                <p>{{ palpite.acertos_palpites }}</p>
                <p>{{ palpite.cotacao | formatCotacao() }}</p>
                <p>{{ palpite.valor_apostado | formatMoeda() }}</p>
                <p>{{ palpite.retorno_possivel | formatMoeda() }}</p>
                <p>{{ palpite.retorno_cambista | formatMoeda() }}</p>
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
                <p>{{ palpite.total_palpites }}</p>
                <p>{{ palpite.acertos_palpites }}</p>
                <p>{{ palpite.cotacao | formatCotacao() }}</p>
                <p>{{ palpite.valor_apostado | formatMoeda() }}</p>
                <p>{{ palpite.retorno_possivel | formatMoeda() }}</p>
              </div>
            </div>

            <div class="tipo-aposta">
              <b>
                <h4>REGRAS</h4>
              </b>
            </div>

            <div class="regras">{{ limitesUser.texto_rodape }}</div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal Bilhete-->

    <div class="modal fade in" id="modal-validar-pin">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-ticket"></i> VALIDAR PIN
            </h4>
          </div>
          <div class="modal-body box box-primary">
            <div class="alert alert-danger alert-dismissible" v-if="errorLogin">
              <h4><i class="icon fa fa-ban"></i> Alerta!</h4>
              {{ messageError }}
            </div>
            <div class="input-group busca-time">
              <input
                type="text"
                placeholder="PIN..."
                v-model="pin"
                class="form-control pin"
              />
              <div class="input-group-addon" @click="validaPin()">
                <i class="fa fa-send"></i>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal -->

    <!--Espação para os modals-->
    <div class="modal fade in" id="modal-cupon">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title"><i class="fa fa-ticket"></i> Bilhete</h4>
          </div>

          <div class="modal-body box box-primary">
            <div class="col-md-4">
              <ul class="cupon-title">
                <li class="cupon-title ativo">
                  <a>
                    <i class="fa fa-fw fa-sticky-note"></i>
                    CUPOM DE APOSTAS ({{ selection.length }})
                  </a>
                </li>
              </ul>

              <div class="box box-success">
                <div
                  class="box-cupon"
                  v-for="select in selection"
                  :key="select.id"
                >
                  <h5 class="header-campeonato-cupon">
                    <i class="fa fa-trophy"></i>
                    {{ select.league }}
                    <span
                      class="delete-palpite-cupon"
                      @click="removePalpite(select.idOdd)"
                    >
                      <i class="fa fa-trash"></i>
                    </span>
                  </h5>
                  <li class="cupon-confronto">
                    {{ select.home }} X {{ select.away }}
                  </li>
                  <li class="cupon-data">
                    {{ select.date | formatDate() }} hs
                  </li>
                  <li>
                    {{ select.sport }}
                  </li>
                  <li>
                    <b>{{ select.group_opp }}</b>
                  </li>
                  <li>
                    <span class="cupon-left" v-if="select.type == 'ao-vivo'">
                      <b>{{ select.odd }} ({{ select.type }})</b>
                    </span>
                    <span class="cupon-left" v-else>
                      <b>{{ select.odd }}</b>
                    </span>
                    <span class="cupon-right">{{
                      select.cotacao | formatCotacao()
                    }}</span>
                  </li>
                </div>

                <div class="box-rodape-cupon">
                  <h4 class="retorno-individual" v-if="selection.length > 0">
                    <span class="retorno-ind">
                      <b>Cotação:</b>
                    </span>
                    <span class="val-retorno-ind">{{
                      total_cotacao | formatCotacao()
                    }}</span>
                  </h4>
                  <h4>Escolha entre os valores mais apostados</h4>

                  <div class="btn-valor-mobile">
                    <button
                      class="btn-valor btn btn-primary"
                      @click="setValApostado(2)"
                    >
                      R$ 2,00
                    </button>
                    <button
                      class="btn-valor btn btn-primary"
                      @click="setValApostado(5)"
                    >
                      R$ 5,00
                    </button>
                    <button
                      class="btn-valor btn btn-primary"
                      @click="setValApostado(10)"
                    >
                      R$ 10,00
                    </button>
                    <button
                      class="btn-valor btn btn-primary"
                      @click="setValApostado(20)"
                    >
                      R$ 20,00
                    </button>
                    <button
                      class="btn-valor btn btn-primary"
                      @click="setValApostado(50)"
                    >
                      R$ 50,00
                    </button>
                  </div>

                  <br />
                  <!-- <div class="ganho-cupon">{{ retorno | formatMoeda() }}</div> -->

                  <div v-if="limitesUser.comissao_premio > 0">
                    <h4 class="retorno-individual" v-if="selection.length > 0">
                      <span class="retorno-ind">
                        <b>Site Paga:</b>
                      </span>
                      <span class="val-retorno-ind">{{
                        retorno | formatCotacao()
                      }}</span>
                    </h4>
                    <h4 class="retorno-individual" v-if="selection.length > 0">
                      <span class="retorno-ind">
                        <b>Cambista Paga:</b>
                      </span>
                      <span class="val-retorno-ind">{{
                        retornoCambista | formatCotacao()
                      }}</span>
                    </h4>
                  </div>

                  <div v-else>
                    <h4 class="retorno-individual" v-if="selection.length > 0">
                      <span class="retorno-ind">
                        <b>Retorno Possível:</b>
                      </span>
                      <span class="val-retorno-ind">{{
                        retorno | formatCotacao()
                      }}</span>
                    </h4>
                  </div>

                  <div class="form-group">
                    <label for="input-aposta">Valor da Aposta:</label>
                    <input
                      class="form-control"
                      type="number"
                      v-model="apostado"
                      @keyup="calculaCotacao()"
                    />

                    <label for>Apostador:</label>
                    <input
                      type="text"
                      class="input-apostador form-control"
                      v-model="cliente"
                    />
                  </div>

                  <button
                    v-if="selection.length > 0"
                    class="btn btn-danger btn-block"
                    @click="removePalpites(selection)"
                  >
                    <i class="fa fa-trash"></i> Limpar
                  </button>
                  <button
                    v-if="!live"
                    class="btn btn-success btn-block btnSendBet"
                    @click="enviarAposta"
                  >
                    <i class="fa fa-send"></i>&nbsp;&nbsp;Salvar
                  </button>
                  <button
                    v-if="live"
                    class="btn btn-success btn-block btnSendBet"
                    @click="enviarApostaLive()"
                  >
                    <i class="fa fa-send"></i>&nbsp;&nbsp;Salvar
                  </button>
                  <div class="loadSendBet" style="text-align: center">
                    <i class="fa fa-refresh fa-spin"></i> Validando Aposta...
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal -->

    <div class="modal fade in" id="modal-regulamento">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title"><i class="fa fa-star"></i> Regulamento</h4>
          </div>

          <div class="modal-body box box-primary">
            <div v-html="regulamento"></div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal -->

    <div class="modal fade in" id="modal-match">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-trophy"></i>
              {{ liga }} - {{ match.date | formatDate() }}
            </h4>
          </div>

          <div class="modal-body box box-primary">
            <table style="width: 100%" v-if="!live">
              <tr>
                <td align="left" width="45%">
                  <span class="timeMatch"> {{ match.home }} </span>
                </td>
                <td align="center" width="10%">
                  <span class="score-real-time">
                    <strong> X </strong>
                  </span>
                </td>
                <td align="right" width="45%">
                  <span class="timeMatch">{{ match.away }} </span>
                </td>
              </tr>
              <br />
            </table>
            <br />

            <div class="real-time" v-if="live">
              <div class="placar">
                <span class="score-real-time">
                  <strong v-if="live">{{ match.score }}</strong>
                </span>
                <div class="time-real-time" v-if="match.time == 0">
                  Não Iniciado {{ match.time }}
                  <span class="pisca">'</span>
                </div>
                <div
                  class="time-real-time"
                  v-if="match.time < 45 && match.time != 0"
                >
                  1º Tempo {{ match.time }}
                  <span class="pisca">'</span>
                </div>
                <div class="time-real-time" v-if="match.time == 45">
                  Intervalo
                  <span class="pisca">'</span>
                </div>
                <div class="time-real-time" v-if="match.time > 45">
                  2º Tempo {{ match.time }}
                  <span class="pisca">'</span>
                </div>

                <table class="tableInfo">
                  <thead>
                    <tr class="table-header">
                      <th class="left padding-10">
                        <span>{{ liga }}</span>
                      </th>
                      <th class="cell-soccer">1T</th>
                      <th class="cell-soccer">2T</th>
                      <th class="cell-soccer">
                        <label class="icon corner"></label>
                      </th>
                      <th class="cell-soccer">
                        <label class="icon yellow-card"></label>
                      </th>
                      <th class="cell-soccer">
                        <label class="icon red-card"></label>
                      </th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr class="table-row">
                      <td class="left padding-10">{{ match.home }}</td>
                      <td class="cell-soccer">{{ match.halfTimeScoreHome }}</td>
                      <td class="cell-soccer">{{ match.fullTimeScoreHome }}</td>
                      <td class="cell-soccer">
                        {{ match.numberOfCornersHome }}
                      </td>
                      <td class="cell-soccer">
                        {{ match.numberOfYellowCardsHome }}
                      </td>
                      <td class="cell-soccer">
                        {{ match.numberOfRedCardsHome }}
                      </td>
                    </tr>
                    <tr class="table-row">
                      <td class="left padding-10">{{ match.away }}</td>
                      <td class="cell-soccer">{{ match.halfTimeScoreAway }}</td>
                      <td class="cell-soccer">{{ match.fullTimeScoreAway }}</td>
                      <td class="cell-soccer">
                        {{ match.numberOfCornersAway }}
                      </td>
                      <td class="cell-soccer">
                        {{ match.numberOfYellowCardsAway }}
                      </td>
                      <td class="cell-soccer">
                        {{ match.numberOfRedCardsAway }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <clip-loader
              :loading="loading_odds"
              :color="color"
              :size="size"
            ></clip-loader>

            <div class="row" v-for="mercado in mercados" :key="mercado.id">
              <div class="titulo-grupo">{{ mercado.name }}</div>
              <div class="row">
                <div
                  class="col-md-12"
                  v-for="odd in mercado.odds"
                  :key="odd.id"
                >
                  <div class="odd-match-plus" v-if="odd.cotacao == 0">
                    <span class="odd-match-plus-left">
                      <strong>{{ odd.odd }}</strong>
                    </span>
                    <span
                      class="odd-match-plus-right"
                      :taxaJogo="match.event_id"
                      :taxa="odd.id"
                      @click="
                        addPalpite(
                          odd.uuid,
                          odd.id,
                          match.sport,
                          match.event_id,
                          odd.group_opp,
                          odd.odd,
                          odd.cotacao,
                          liga,
                          match.date,
                          match.home,
                          match.away,
                          odd.type,
                          odd.cotacaoOriginal
                        )
                      "
                    >
                      <i class="fa fa-lock"></i>
                    </span>
                  </div>
                  <div class="odd-match-plus" v-if="odd.cotacao > 0">
                    <span class="odd-match-plus-left">
                      <strong>{{ odd.odd }}</strong>
                    </span>
                    <span
                      class="odd-match-plus-right"
                      :taxaJogo="match.event_id"
                      :taxa="odd.id"
                      @click="
                        addPalpite(
                          odd.uuid,
                          odd.id,
                          match.sport,
                          match.event_id,
                          odd.group_opp,
                          odd.odd,
                          odd.cotacao,
                          liga,
                          match.date,
                          match.home,
                          match.away,
                          odd.type,
                          odd.cotacaoOriginal
                        )
                      "
                      >{{ odd.cotacao | formatCotacao() }}</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal -->

    <header class="main-header">
      <!-- Logo -->
      <a href="javascript:void(0)" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
          {{ server.logoMini }}
        </span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">
          {{ server.logo }}
        </span>
      </a>

      <!-- Header Navbar: style can be found in header.less -->
      <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a
          href="javascript:void(0)"
          class="sidebar-toggle"
          data-toggle="push-menu"
          role="button"
        >
          <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <!-- Control Sidebar Toggle Button -->
            <li>
              <!-- <a href="#">
                <i class="fa fa-user-plus"> Cadastre-se </i>
              </a>-->
            </li>
            <li v-if="logar">
              <a href="javascript:void(0)" @click="load_login()">
                <i class="fa fa-sign-in">&nbsp;&nbsp;Acessar</i>
              </a>
            </li>

            <li v-if="logout">
              <a href="javascript:void(0)" @click="sair()">
                <i class="fa fa-close">Sair</i>
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <nav class="navbar navbar-static-top" id="nav-mobile">
        <input
          class="form-control"
          id="input-mobile-top"
          placeholder="valor"
          type="number"
          v-model="apostado"
          @keyup="calculaCotacao()"
        />

        <span class="ganho-mobile" v-if="selection.length > 0">{{
          retorno | formatMoeda()
        }}</span>
        <span class="ganho-mobile" v-if="selection.length == 0">{{
          0 | formatMoeda()
        }}</span>
        <button
          class="btn btn-danger"
          id="btn-zerar-mobile"
          @click="removePalpites(selection)"
          v-if="selection.length > 0"
        >
          <i class="fa fa-trash"></i>
        </button>

        <button
          class="btn btn-success"
          id="btn-finalizar-mobile"
          @click="mostraPalpites"
        >
          ( {{ selection.length }} ) Finalizar
        </button>
      </nav>
    </header>

    <!-- Left side column. contains the logo and sidebar -->
    <!-- <main-sidebar-component></main-sidebar-component> -->
    <aside class="main-sidebar">
      <!-- sidebar: style can be found in sidebar.less -->
      <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
          <img
            style="max-width: 75%; border-radius: 5px"
            src="img/logo.png"
            alt="golbets"
          />
          <div class="dados-logado">
            <p v-if="logado">{{ name }}</p>
            <p v-if="logado">
              S. Simples: {{ caixaUser.saldo_simples | formatMoeda() }}
            </p>
            <p v-if="logado">
              S. Casadinha: {{ caixaUser.saldo_casadinha | formatMoeda() }}
            </p>
          </div>
        </div>
        <!--Form Busca-->
        <div class="sidebar-form">
          <div class="input-group">
            <input
              type="text"
              v-model="cupom"
              class="form-control"
              placeholder="Conferir bilhete"
            />
            <span class="input-group-btn">
              <button @click="searchBilhete()" class="btn btn-flat">
                <i class="fa fa-search"></i>
              </button>
            </span>
          </div>
        </div>

        <ul class="sidebar-menu tree" data-widget="tree">
          <li class="header"><i class="fa fa-list"></i> MENU PRINCIPAL</li>
          <li class="treeview">
            <a href="#" @click="loadRegulamento" class="sidebar-toggle">
              <i class="fa fa-map"></i>
              <span>Regulamento</span>
            </a>
          </li>
          <li>
            <a v-bind:href="`${server.linkApp}`">
              <i class="fa fa-android"></i>
              <span>Baixar Aplicativo</span>
            </a>
          </li>

          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="viewValidarPin()">
              <i class="fa fa-code"></i>
              <span>Validar PIN</span>
            </a>
          </li>

          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="loadCaixa()">
              <i class="fa fa-money"></i>
              <span>Meu Caixa</span>
            </a>
          </li>
          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="loadRelatorio()">
              <i class="fa fa-pie-chart"></i>
              <span>Relatório</span>
            </a>
          </li>
          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="loadBilhetes()">
              <i class="fa fa-tags"></i>
              <span>Bilhetes</span>
            </a>
          </li>
          <li class="header"><i class="fa fa-trophy"></i> JOGOS DO DIA</li>
          <li v-for="day in days" :key="day.id">
            <a href="javascript:void(0)" @click="seachDay(day.num)">
              <i class="fa fa-calendar"></i>
              <span>{{ day.day }}</span>
            </a>
          </li>
          <!-- <li >
            <a href="javascript:void(0)" @click="allMatchs()">
              <i class="fa fa-soccer-ball-o"></i>
              <span>Todos</span>
            </a>
          </li> -->

          <li class="header"><i class="fa fa-trophy"></i> ESPORTES</li>
          <li class="treeview" v-if="vivo">
            <a
              href="javascript:void(0)"
              class="sidebar-toggle"
              @click="loadVivo()"
            >
              <i class="fa fa-bullseye"></i>
              <span>Ao Vivo</span>
            </a>
          </li>

          <li class="treeview">
            <a
              href="javascript:void(0)"
              class="sidebar-toggle"
              @click="loadFutebol()"
            >
              <i class="fa fa-soccer-ball-o"></i>
              <span>Futebol</span>
            </a>
          </li>
          <!-- <li class="treeview" v-if="op_quininha == 'Sim'">
                <a href="javascript:void(0)" class="sidebar-toggle" @click="loadQuininha()">
                    <i class="fa fa-list-alt"></i>
                    <span>Quininha</span>
                </a>
            </li>
            <li class="treeview" v-if="op_seninha == 'Sim'">
                <a href="javascript:void(0)" @click="loadRegulamento" class="sidebar-toggle">
                    <i class="fa fa-list-alt"></i>
                    <span>Seninha</span>
                </a>
          </li>-->

          <li class="header"><i class="fa fa-trophy"></i> LIGAS PRINCIPAIS</li>

          <li v-for="league_main in leagues_main" :key="league_main.id">
            <a
              href="javascript:void(0)"
              @click="seachLeague(league_main.league)"
            >
              <i class="fa fa-trophy"></i>
              <span>{{ league_main.league }}</span>
            </a>
          </li>
          <li class="header">
            <i class="fa fa-trophy" @click="clique"></i> OUTRAS LIGAS
          </li>
          <li v-for="league in leagues" :key="league.id">
            <a href="javascript:void(0)" @click="seachLeague(league.league)">
              <i class="fa fa-trophy"></i>
              <span>{{ league.league }}</span>
            </a>
          </li>
        </ul>
      </section>
      <!-- /.sidebar -->
    </aside>

    <!--End Sidebar-->
    <!--<content-wrap-component></content-wrap-component>-->
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-8">
            <!-- <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="tabs-mobile">
                        <li class="active"><a href="#jogo-hoje"  data-toggle="tab" @click="loadMatchHoje()" aria-expanded="true"><i class="fa fa-trophy"></i> JOGOS DE HOJE</a></li>
                          <li class="pisca" v-if="futebol_ao_vivo =='Sim'"><a href="#" data-toggle="tab"  @click="loadLive()"   aria-expanded="false" > <i class="fa fa-bullseye"></i> AO VIVO</a></li>
                        <li class=""><a href="#jogo-amanha" data-toggle="tab"  @click="loadMatchAmanha()" aria-expanded="false"><i class="fa fa-trophy"></i> JOGOS DE AMANHÃ</a></li>
                    </ul>
            </div>-->
            <!--Content Bilhetes-->
            <div v-if="bilheteView && logado" class="bilhetes-content">
              <div class="form-inline relatorio">
                <div class="form-group">
                  <label>Data:</label>

                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input
                      type="date"
                      v-model="date1"
                      class="form-control pull-right"
                      id="datepicker"
                      @change="pesquisaBilhetes(date1)"
                    />
                  </div>
                  <!-- /.input group -->
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead class="tabela-class-home">
                    <tr>
                      <th>CUPOM</th>
                      <th>DATA</th>
                      <th>STATUS</th>
                      <th>APOSTADO</th>
                      <th>RETORNO</th>
                      <th>CLIENTE</th>
                      <th>COMISSÃO</th>
                      <th>COTAÇÃO</th>
                      <th>TIPO</th>
                      <th>AP. ABERTAS</th>
                      <th>MOSTRAR</th>
                      <th>CANCELAR</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-for="bilhete in bilhetesLogado" :key="bilhete.id">
                      <td v-bind:class="bilhete.tipo_aposta">
                        <b>{{ bilhete.cupom }}</b>
                      </td>
                      <td>{{ bilhete.created_at | formatDate() }}</td>
                      <td v-bind:class="bilhete.status">
                        {{ bilhete.status }}
                      </td>
                      <td>{{ bilhete.valor_apostado | formatMoeda() }}</td>
                      <td>{{ bilhete.retorno_possivel | formatMoeda() }}</td>
                      <td>{{ bilhete.cliente }}</td>
                      <td>{{ bilhete.comicao | formatMoeda() }}</td>
                      <td>{{ bilhete.cotacao | formatCotacao() }}</td>
                      <td>{{ bilhete.tipo }}</td>
                      <td>
                        {{ bilhete.andamento_palpites }}/{{
                          bilhete.total_palpites
                        }}
                      </td>
                      <td>
                        <button
                          class="btn btn-primary"
                          @click="
                            viewBilhete(
                              bilhete.id,
                              bilhete.status,
                              bilhete.cupom,
                              bilhete.created_at,
                              bilhete.vendedor,
                              bilhete.cliente,
                              bilhete.total_palpites,
                              bilhete.cotacao,
                              bilhete.valor_apostado,
                              bilhete.retorno_possivel,
                              bilhete.tipo
                            )
                          "
                        >
                          <i class="fa fa-eye"></i>
                        </button>
                      </td>
                      <td>
                        <button
                          v-if="bilhete.status == 'Cancelado'"
                          class="btn btn-defaul"
                          disabled
                        >
                          <i class="fa fa-exclamation-circle"></i>
                        </button>
                        <button
                          v-if="bilhete.status != 'Cancelado'"
                          class="btn btn-danger"
                          @click="alterarBilhete(bilhete.id, bilhete)"
                        >
                          <i class="fa fa-remove"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <clip-loader
                  :loading="loading"
                  :color="color"
                  :size="size"
                ></clip-loader>
              </div>
            </div>
            <!--End content Bilhetes-->

            <!--Content Jogos-->
            <div v-if="jogosView">
              <div class="row container-lista-jogos header-jogos">
                <div class="col-lg-6 col-md-6 col-xs-9 jogos">
                  <div class="input-group busca-time">
                    <div class="input-group-addon">
                      <i class="fa fa-search"></i>
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      v-model="search"
                      placeholder="Buscar por campeonato, times, data, horario"
                    />
                  </div>
                </div>
                <div class="col-lg-1 col-md-1 col-xs-3 data-hora">
                  <div class="cotacoes-principais">
                    <div class="btn-home-header"></div>
                  </div>
                </div>

                <div class="col-lg-5 col-md-5 col-xs-12 btn-apostas">
                  <div class="cotacoes-principais">
                    <div class="btn-home-header">CASA</div>
                    <div class="btn-home-header">EMPATE</div>
                    <div class="btn-home-header">FORA</div>
                    <div class="btn-home-header"><b>+</b> MERCADOS</div>
                  </div>
                </div>
              </div>

              <!--Content Carousel-->
              <div id="myCarousel"  class="carousel-slide carousel slide" data-ride="carousel">
                <ol class="carousel-indicators">
                  <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                  <li data-target="#myCarousel" data-slide-to="1" class=""></li>
                  <li data-target="#myCarousel" data-slide-to="2" class=""></li>
                </ol>
                <div class="carousel-inner" role="listbox">
                  <div class="item">
                    <img  class="first-slide" src="img/sliders/download1.jpg" style="min-width: 100%;"/>
                  </div>
                  <div class="item active left">
                    <img class="second-slide" src="img/sliders/download2.jpg" style="min-width: 100%;"/>
                  </div>
                  <div class="item next left">
                    <img class="second-slide" src="img/sliders/download3.jpg" style="min-width: 100%;"/>
                  </div>
                </div>
                <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                  <span class="glyphicon glyphicon-chevron-left" aria-hidden="true" ></span>
                  <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                  <span class="glyphicon glyphicon-chevron-right" aria-hidden="true" ></span>
                  <span class="sr-only">Next</span>
                </a>
              </div>
            <!--End content Carousel-->


              <clip-loader
                :loading="loading"
                :color="color"
                :size="size"
              ></clip-loader>

              <div
                class="content-jogos"
                v-for="event in filterLiegues"
                :key="event.id"
              >
                <h4 class="header-campeonato-matchs">
                  <i class="fa fa-trophy"></i>
                  {{ event.league }}
                </h4>

                <div class="jogo">
                  <div
                    class="row container-lista-jogos"
                    v-for="match in event.match"
                    :key="match.id"
                  >
                    <div class="col-lg-1 col-md-1 col-xs-2 data-hora">
                      <div v-if="live" style="text-align: center">
                        <strong>{{ match.score }}</strong>
                        <span class="badge bg-green pisca">Ao vivo</span>
                      </div>
                      <span v-if="!live">{{ match.date | formatDate() }}</span>
                    </div>
                    <div class="col-lg-5 col-md-5 col-xs-8 jogos">
                      <table style="width: 100%">
                        <tr>
                          <td width="50%">
                            <h4 style="margin: 1%; font-size: 1.5rem">
                              {{ match.home }}
                            </h4>
                          </td>
                        </tr>
                        <tr>
                          <td width="50%">
                            <h4 style="margin: 1%; font-size: 1.5rem">
                              {{ match.away }}
                            </h4>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-lg-1 col-md-1 col-xs-2 data-hora">
                      <span v-if="live">
                        {{ match.time }}
                        <span class="pisca">'</span>
                      </span>
                      <!-- <span v-else>{{ match.date | formatDate() }}</span> -->
                    </div>

                    <div class="col-lg-5 col-md-5 col-xs-12 btn-apostas">
                      <div
                        class="cotacoes-principais"
                        v-for="odd in match.odds"
                        :key="odd.id"
                      >
                        <div
                          class="btn-custom btn-home"
                          :taxaJogo="match.event_id"
                          :taxa="odd.id"
                          @click="
                            addPalpite(
                              odd.uuid,
                              odd.id,
                              match.sport,
                              match.event_id,
                              odd.group_opp,
                              odd.odd,
                              odd.cotacao,
                              event.league,
                              match.date,
                              match.home,
                              match.away,
                              odd.type,
                              odd.cotacaoOriginal
                            )
                          "
                          v-if="odd.cotacao == 0"
                        >
                          <i class="fa fa-lock"></i>
                        </div>
                        <div
                          class="btn-custom btn-home"
                          :taxaJogo="match.event_id"
                          :taxa="odd.id"
                          @click="
                            addPalpite(
                              odd.uuid,
                              odd.id,
                              match.sport,
                              match.event_id,
                              odd.group_opp,
                              odd.odd,
                              odd.cotacao,
                              event.league,
                              match.date,
                              match.home,
                              match.away,
                              odd.type,
                              odd.cotacaoOriginal
                            )
                          "
                          v-if="odd.cotacao > 0"
                        >
                          {{ odd.cotacao | formatCotacao() }}
                        </div>
                      </div>
                      <div class="btn-custom plus-odd" @click="loadOdd(event.league, match, event)">+{{ match.count_odd }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--End content Jogos-->



          <!--Right-->
          <div class="col-md-4" id="cupom-site">
            <ul class="cupon-title">
              <li class="cupon-title ativo">
                <a>
                  <i class="fa fa-fw fa-sticky-note"></i>
                  CUPOM DE APOSTAS ({{ selection.length }})
                </a>
              </li>
            </ul>

            <div class="box box-success">
              <div
                class="box-cupon"
                v-for="select in selection"
                :key="select.id"
              >
                <h5 class="header-campeonato-cupon">
                  <i class="fa fa-trophy"></i>
                  {{ select.league }}
                  <span
                    class="delete-palpite-cupon"
                    @click="removePalpite(select.idOdd)"
                  >
                    <i class="fa fa-trash"></i>
                  </span>
                </h5>
                <li class="cupon-confronto">
                  {{ select.home }} X {{ select.away }}
                </li>
                <li class="cupon-data">{{ select.date | formatDate() }} hs</li>
                <li>
                  {{ select.sport }}
                </li>
                <li>
                  <b>{{ select.group_opp }}</b>
                </li>
                <li>
                  <span class="cupon-left" v-if="select.type == 'ao-vivo'">
                    <b>{{ select.odd }} ({{ select.type }})</b>
                  </span>
                  <span class="cupon-left" v-else>
                    <b>{{ select.odd }}</b>
                  </span>

                  <span class="cupon-right">{{
                    select.cotacao | formatCotacao()
                  }}</span>
                </li>
              </div>

              <div class="box-rodape-cupon">
                <h4 class="retorno-individual" v-if="selection.length > 0">
                  <span class="retorno-ind">
                    <b>Cotação:</b>
                  </span>
                  <span class="val-retorno-ind">{{
                    total_cotacao | formatCotacao()
                  }}</span>
                </h4>
                <div class="btns-pricin">
                  <h4>Escolha entre os valores mais apostados</h4>
                  <!-- class="btn-valor btn btn-primary" -->
                  <button
                    class="btn-valor "

                    @click="setValApostado(2)"
                  >
                    R$ 2,00
                  </button>
                  <button
                    class="btn-valor"
                    @click="setValApostado(5)"
                  >
                    R$ 5,00
                  </button>
                  <button
                    class="btn-valor"
                    @click="setValApostado(10)"
                  >
                    R$ 10,00
                  </button>
                  <button
                    class="btn-valor"
                    @click="setValApostado(20)"
                  >
                    R$ 20,00
                  </button>
                  <button
                    class="btn-valor"
                    @click="setValApostado(50)"
                  >
                    R$ 50,00
                  </button>
                </div>

                <!-- <div
                  class="ganho-cupon"
                  v-if="selection.length > 0"
                >{{ retorno | formatMoeda() }}</div>
                <div class="ganho-cupon" v-if="selection.length == 0">{{ 0 | formatMoeda() }}</div> -->
                <div v-if="limitesUser.comissao_premio > 0">
                  <h4 class="retorno-individual" v-if="selection.length > 0">
                    <span class="retorno-ind">
                      <b>Site Paga:</b>
                    </span>
                    <span class="val-retorno-ind">{{
                      retorno | formatCotacao()
                    }}</span>
                  </h4>
                  <h4 class="retorno-individual" v-if="selection.length > 0">
                    <span class="retorno-ind">
                      <b>Cambista Paga:</b>
                    </span>
                    <span class="val-retorno-ind">{{
                      retornoCambista | formatCotacao()
                    }}</span>
                  </h4>
                </div>

                <div v-else>
                  <h4 class="retorno-individual" v-if="selection.length > 0">
                    <span class="retorno-ind">
                      <b>Retorno Possível:</b>
                    </span>
                    <span class="val-retorno-ind">{{
                      retorno | formatCotacao()
                    }}</span>
                  </h4>
                </div>

                <div class="form-group">
                  <label for="input-aposta">Valor da Aposta:</label>
                  <input
                    class="form-control"
                    type="number"
                    v-model="apostado"
                    @keyup="calculaCotacao()"
                  />

                  <label for>Apostador:</label>
                  <input
                    type="text"
                    class="input-apostador form-control"
                    v-model="cliente"
                  />
                </div>

                <button
                  v-if="selection.length > 0"
                  class="btn btn-danger btn-block"
                  @click="removePalpites(selection)"
                >
                  <i class="fa fa-trash"></i> Limpar
                </button>
                <button
                  v-if="!live"
                  class="btn btn-success btn-block btnSendBet"
                  @click="enviarAposta()"
                >
                  <i class="fa fa-send"></i>&nbsp;&nbsp;Salvar
                </button>
                <button
                  v-if="live"
                  class="btn btn-success btn-block btnSendBet"
                  @click="enviarApostaLive()"
                >
                  <i class="fa fa-send"></i>&nbsp;&nbsp;Salvar
                </button>
                <div class="loadSendBet" style="text-align: center">
                  <i class="fa fa-refresh fa-spin"></i> Validando Aposta...
                </div>
              </div>
            </div>
          </div>
          <!--End Right-->

          <!--Btn Apostas mobile-->

          <!--End Btn Aposta-->
        </div>
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
      <div class="pull-right hidden-xs"><b>Versão</b> 1.0.0</div>
      <strong>Copyright &copy; {{ server.year }}</strong>
      <a v-bind:href="`https://wwww.${server.host}`"> {{ server.logo }} </a>
      Todos os direitos reservados.
    </footer>
  </div>
  <!-- ./wrapper -->
</template>
<style>
@media only screen and (max-device-width: 480px) {
  .imgTeams {
    width: 20px;
    height: 20px;
  }
}

@font-face {
  font-family: "Antipasto";
  src: local("Antipasto"),
    url(./../../../../../public/fonts/Antipasto.woff) format("truetype");
}

.logo-lg {
  font-family: "Antipasto", Helvetica;
  font-size: 27px;
  font-weight: bold;
  letter-spacing: 5px;
}

.logo-mini {
  font-family: "Antipasto", Helvetica;
  font-size: 30px;
  font-weight: bold;
  letter-spacing: 3px;
}

.carousel-slide{
  margin: auto;
  max-width: 100%;
  min-width: 100%;
}

.timeMatch {
  color: #222d32;
  font-size: 20px;
}

.real-time {
  background-image: url("~/img/soccer.jpg");
  background-size: 100% 100%;
  color: #dcdcdc;
  margin-bottom: 20px;
  width: 100%;
  height: auto;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  text-align: center;
}
.placar {
  width: 100%;
  height: auto;
  padding: 6px;
  background-color: rgba(0, 0, 0, 0.5);
}
.score-real-time {
  font-size: 20px;
}

.center-loader {
  width: 100%;
  height: auto;
  padding: 4px;
  text-align: center !important;
}

.tabela-class-home {
  background: #3c8dbc;
  color: #fff;
}
.bilhetes-content {
  width: 100%;
  height: auto;
  padding: 8px;
  background: #fff;
  border-radius: 7px;
}

.pin {
  font-size: 25px;
}
.dados-logado p {
  margin-bottom: 0px;
  margin-top: 0px;
}

.relatorio {
  width: 100%;
  height: auto;
  padding: 4px;
  margin-bottom: 15px;
}
.header-print-share {
  width: 100%;
  height: 50px;
  background: #ffe4b5;
}

.share-float {
  float: right;
}

.pre-aposta-resul {
  padding: 8px;
  text-align: center;
  background: #333333;
  color: #dcdcdc;
}

.btn-valor-mobile {
  text-align: center;
}
.imgTeams {
  width: 40px;
  height: 40px;
}

.regras {
  width: 100%;
  height: auto;
  /* text-align: justify; */
  font-size: 12px;
  margin-top: 20px;
}

.send-whats {
  color: #008d4c;
  font-size: 40px;
}
.send-print {
  color: #333333;
  font-size: 40px;
  margin-right: 15px;
  cursor: pointer;
}

.busca-time {
  margin-bottom: 10px;
  margin-top: 10px;
  width: 100%;
  margin-right: 10px;
  height: 35px;
  padding: 12px;
  border: none;
  border-radius: 3px;
  color: #000;
}

/* Default
.titulo-grupo {
    width: 100%;
    height: auto;
    font-size: 20px;
    page-break-after: 10px;
    background: #dd4b39;
    color: #fff;
    text-align: center;
}
.titulo-grupo {
    width: 100%;
    height: auto;
    font-size: 20px;
    page-break-after: 10px;
    background: #2a6383;
    color: #fff;
    text-align: center;
}


.titulo-grupo {
    width: 100%;
    height: auto;
    font-size: 20px;
    page-break-after: 10px;
    background: #dd4b39;
    color: #fff;
    text-align: center;
}

.titulo-grupo {
    width: 100%;
    height: auto;
    font-size: 20px;
    page-break-after: 10px;
    background: #153d01;
    color: #fff;
    text-align: center;
}

.titulo-grupo {
    width: 100%;
    height: auto;
    font-size: 20px;
    page-break-after: 10px;
    background: #f39c12;
    color: #fff;
    text-align: center;
}


*/

.titulo-grupo {
  width: 100%;
  height: auto;
  font-size: 20px;
  page-break-after: 10px;
  background: #1d0053;
  color: #fff;
  text-align: center;
}

.odd-match-plus {
  width: 100%;
  height: auto;
  color: #222d32;
  float: left;
  margin-top: 10px;
  font-size: 15px;
  padding: 4px;
  border-bottom: 1px solid #dcdcdc;
}

.odd-match-plus-left {
  float: left;
}

.btns-pricin {
  text-align: center;
}

/* Default
Azul
.odd-match-plus-right {
  float: right;
  background: #333;
  color: #fff;
  border: 1px solid #333;
  padding: 8px;
  width: 60px;
  height: 40px;
  text-align: center;
  cursor: pointer;
}

Vermelho
.odd-match-plus-right {
    float: right;
    background: #dd4b39;
    color: #fff;
    border: 1px solid #dd4b39;
    padding: 8px;
    width: 60px;
    height: 40px;
    text-align: center;
    cursor: pointer;
}
.odd-match-plus-right {
    float: right;
    background: #333;
    color: #F28D3C;
    border: 1px solid #F28D3C;
    padding: 8px;
    width: 60px;
    height: 40px;
    text-align: center;
    cursor: pointer;
}
*/

.odd-match-plus-right {
  float: right;
  background: #07505e;
  color: #fff;
  border: 1px solid #fafafa;
  padding: 8px;
  width: 60px;
  height: 40px;
  text-align: center;
  cursor: pointer;
}

.odd-match-plus-right-selecionado {
  float: right;
  background: #d60000;
  color: #000;
  padding: 8px;
  width: 60px;
  height: 40px;
  text-align: center;
  cursor: pointer;
  border: 1px solid #000;
}

.match-odd-data {
  color: #014586;
}
#cod {
  text-align: center;
}

.body-bilhete {
  background: #f8ecc2;
  color: #000;
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
  border-bottom: 1px #000 dashed;
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
  height: 100px;
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

.loadSendBet {
  visibility: hidden;
  font-size: 25px;
}

/*Efeito piscas
  */

@keyframes pisca {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
  80% {
    opacity: 0;
  }
}
.pisca {
  -webkit-animation: pisca 0.75s linear infinite;
  -moz-animation: pisca 0.75s linear infinite;
  -ms-animation: pisca 0.75s linear infinite;
  -o-animation: pisca 0.75s linear infinite;
  animation: pisca 0.75s linear infinite;
}

/* UPDATE 28/07 - 13h - por: João */

.modal-bilhete {
  max-height: 100vh !important;
}

.tempo {
  width: 100%;
  border: 1px solid #eee;
  text-align: center;
}

.icon,
.sc1,
.sc2,
.sc3 {
  display: inline-block;
}

.info,
.sc1,
.sc2,
.sc3,
.tempo {
  vertical-align: middle;
  box-sizing: border-box;
}

.overflow-handle,
.sc1,
.sc2,
.sc3 {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.info,
.sc1,
.sc2,
.sc3,
.score,
.scoreContainer,
.scoreSpan,
.tempo,
.tempoSpan {
  box-sizing: border-box;
}

.scoreContainer {
  width: 100%;
  max-width: 640px;
  height: 280px;
  border: 1px solid #eee;
  font-family: verdana !important;
  margin: 0 auto;
}

.scoreContainer.tenis {
  height: 340px !important;
}

.score {
  width: 100%;
  height: 50px;
  border: 1px solid #eee;
}

.tempo {
  height: 30px;
}

.info {
  height: 200px;
}

.info.soccer {
  /* background: url(../img/widget_aovivo/soccer.jpg); */
  background-size: 100% 100%;
}

.info.basquete {
  /* background: url(../img/widget_aovivo/basquete.jpg); */
  background-size: 100% 100%;
}

.info.hockey {
  /* background: url(../img/widget_aovivo/hockey.jpg); */
  background-size: 100% 100%;
}

.info.tenis {
  /* background: url(../img/widget_aovivo/tenis.jpg); */
  background-size: 100% 100%;
}

.info.volei {
  /* background: url(../img/widget_aovivo/volei.jpg); */
  background-size: 100% 100%;
}

.icon {
  background: url("~/img/icons.svg") no-repeat;
  width: 12px;
  height: 12px;
  margin: 0 auto;
}

.left {
  text-align: left !important;
}

.cell-soccer,
.cell-volei,
.sc1,
.sc2,
.sc3 {
  text-align: center;
}

.icon.corner {
  background-position: -12px -12px;
}

.icon.yellow-card {
  background-position: -73px 0;
}

.icon.red-card {
  background-position: -48px 0;
}

.icon.yellow-ball {
  background-position: 0 0;
}

.tableInfo {
  margin: 0px auto;
  border: 1px solid #444;
  height: 100px;
  width: 95%;
  border-collapse: collapse;
}

.tableInfo th {
  padding-top: 5px;
  padding-bottom: 5px;
}

.table-header {
  background-color: #333;
  color: #fff;
  opacity: 0.95;
  border: 0;
}

.table-row {
  background-color: #444;
  color: #fff;
  opacity: 0.95;
  border-top: 1px solid #444;
  border-bottom: 1px solid #444;
}

.cell-soccer,
.cell-volei {
  width: 25px;
  border: 0;
}

.cell-basquete,
.cell-hockey,
.cell-tenis {
  width: 30px;
  text-align: center;
  border: 0;
}

.sc1 {
  width: 39%;
  height: 50px;
}

.sc2 {
  width: 20%;
  height: 48px;
  background-color: #b6041a;
  color: #fff;
}

.sc3 {
  width: 39%;
  height: 50px;
  float: right;
}

.scoreSpan {
  line-height: 50px;
}

.placar-soccer,
.placar-tenis {
  font-size: 30px;
}

.placar-basquete {
  font-size: 20px;
}

.placar-volei {
  font-size: 25px;
}

.placar-hockey {
  font-size: 30px;
}

.tempoSpan {
  line-height: 30px;
}

.padding-10 {
  padding-left: 10px;
}

@media only screen and (max-width: 480px) {
  .scoreContainer {
    font-size: 12px !important;
  }
  .placar-soccer,
  .placar-tenis {
    font-size: 20px;
  }
  .placar-basquete {
    font-size: 13px;
  }
  .placar-hockey {
    font-size: 20px;
  }
  .placar-volei {
    font-size: 16px;
  }
}

.table-row td {
  color: white !important;
}
</style>

<script>
export default {
  beforeCreate: function () {
    this.token = localStorage.getItem("token");
  },
  created() {
    this.noJogos = false;
    this.color = "#3C8DBC";
    this.loadLeagues();
    this.loadLeaguesMain();
    this.loadMatchHoje();
    // this.loadMatchAmanha();
    // this.loadMatchDepoisAmanha();
    this.loadRealtime();
    //this.getLive();
    this.loadLimites();
    this.loadDay();
    //this.loadRealtime();
    this.hoje = true;
    this.live = false;
    this.amanha = false;
    this.afetTerTomorow = false;
    this.errorLogin = false;
    this.jogosView = true;
    this.bilheteView = false;

    //Verifica usuário logado
    if (localStorage.getItem("token") == null) {
      this.logar = true;
      this.logout = false;
      this.logado = false;
    } else if (localStorage.getItem("token") != null) {
      this.logar = false;
      this.logout = true;
      this.userLogado();
      this.logado = true;
    }
    this.name = localStorage.getItem("nome");
    this.nivel = localStorage.getItem("nivel");
    // this.date1  = moment(new Date()).format('YYYY-MM-DD')
    // this.date2  = moment(new Date()).format('YYYY-MM-DD')

    // console.log('relatório', Object.values(this.relatorio).length);
  },
  mounted() {
    this.loadServer();
    if (window.innerHeight > window.innerWidth) {
      $("#modal-bilhete").css("max-height", "100vh");
      $("#modal-bilhete").css("overflow-y", "auto");
    }
  },
  data() {
    return {
      notEvent: false,
      link: "",
      leagues: [],
      liga: "",
      leagues_main: [],
      busca: "",
      search: "",
      search_time: "",
      alert_pesquisa: false,
      loading: false,
      loadingCaixa: false,
      loading_odds: false,
      regulamento: "",
      events: [],
      events_main: [],
      liaga: "",
      match: {},
      mercados: [],
      palpites: [],
      selecionados: [],
      selecionadosLive: [],
      selection: "",
      apostado: "",
      total_cotacao: 0,
      qtd_palpites: 0,
      cliente: "",
      cupom_pre_aposta: "",
      cupom: "",
      bilhetes: [],
      bilhetesLogado: [],
      //Valores finais
      retorno: 0,
      retornoCambista: 0,
      url: "",
      days: [],
      events_hoje: [],
      events_amanha: [],
      events_depois_amanha: [],
      events_vivo: [],
      events_all: [],
      live: "",
      hoje: "",
      amanha: "",
      afetTerTomorow: "",
      eventsAll: "",
      tipoAposta: "",
      noJogos: "",

      //Login
      text_btn_login: "Logar",
      nivel: "",
      name: "",
      type_user: "",
      password: "",
      username: "",
      token: "",
      messageError: "",
      errorLogin: "",
      logar: "",
      logout: "",
      logado: "",
      linkPrint: "",
      pin: "",
      date1: "",
      date2: "",

      caixaUser: {},
      limitesUser: {},
      relatorio: {},

      //loader
      color: "3C8DBC",
      size: "100px",
      margin: "auto",

      //Valores Limites
      max_cotacao: "",
      mini_cotacao: "",
      aposta_ativa: "",
      premio_max: "",
      max_jogos_bilehte: "",
      mini_jogos_bilhete: "",
      valor_max_aposta: "",
      valor_mini_aposta: "",
      op_ufcbox: "",
      op_basquete: "",
      op_tenis: "",
      futebol_ao_vivo: "",
      jogosView: "",
      bilheteView: "",
      vivo: "",
      leagueOp: "",
      //server
      server: {
        host: "",
        logo: "",
        logoMini: "",
        year: "",
        linkApp: "",
      },
      //App

      matchSelected: 0,
    };
  },
  filters: {
    formatDate(date) {
      return moment(date).format("DD/MM HH:mm");
    },
    formatDateHome(date) {
      return moment(date).format("DD/MM");
    },
    formatTime(date) {
      return moment(date).format("HH:mm");
    },
    formatCotacao(num) {
      var numero = parseFloat(num).toFixed(2).split(".");
      numero[0] = numero[0].split(/(?=(?:...)*$)/).join(".");
      return numero.join(",");
    },
    formatMoeda(numero) {
      return (
        "R$ " +
        parseFloat(numero)
          .toFixed(2)
          .replace(".", ",")
          .replace(/(\d)(?=(\d{3})+\,)/g, "$1.")
      );
    },
    verificaOdd(id) {},
    is_img(img) {
      let file = "https://assets.b365api.com/images/team/m/" + img + ".png";
      var img = new Image();
      img.src = file;

      return img.width;
      img.onload = function () {};
      img.onerror = function () {};
    },
  },
  watch: {
    selecionados(valorAnterios, valor) {
      this.selecionados;
    },
  },
  computed: {
    filterLiegues() {
      if (this.search != "") {
        return this.events.filter((x) =>
          x.match.some((g) =>
            g.confronto.toLowerCase().includes(this.search.toLowerCase())
          )
        );
      } else {
        return this.events.filter((league) => {
          return league.league
            .toLowerCase()
            .includes(this.search.toLowerCase());
        });
      }
    },
  },
  methods: {
    loadServer() {
      this.server.host = process.env.MIX_ECHO_HOST;
      this.server.logo = process.env.MIX_LOGO;
      this.server.logoMini = process.env.MIX_LOGO_MINI;
      this.server.year = process.env.MIX_YEAR;
      this.server.linkApp = process.env.MIX_LINK_APP;
    },
    printJogos(id) {
      this.link =
        "whatsapp://send?text=" + window.location.href + "api/bilhete/" + id;
      this.linkPrint = window.location.href + "api/bilhete/" + id;
      window.open(
        this.linkPrint,
        "1462709629777",
        "width=360,height=screen.height,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=0,left=0,top=0"
      );
      return false;
    },

    load_login() {
      $("#modal-login").modal("show");
    },
    sair() {
      localStorage.removeItem("token");
      localStorage.removeItem("nome");
      localStorage.removeItem("nivel");
      this.logar = true;
      this.logout = false;
      document.location.reload(true);
    },
    login() {
      if (this.username == "" || this.password == "") {
        this.errorLogin = true;
        this.messageError = "Preencha os campos!";
      }
      axios
        .post("/api/login", {
          username: this.username,
          password: this.password,
        })
        .then((response) => {
          this.text_btn_login = "Aguarde...";

          if (
            response.data.user.nivel == "adm" ||
            response.data.user.nivel == "gerente"
          ) {
            axios
              .post("/login", {
                username: this.username,
                password: this.password,
              })
              .then((response) => {
                window.location.href = "/admin/home";
                this.logar = false;
                this.logout = true;
              })
              .catch((error) => {
                console.log(error);
                this.errorLogin = true;
                this.messageError = "Acesso negado!";
              })
              .finally(() => {});
            return;
          } //else
          if (
            response.data.user.nivel == "cambista" ||
            response.data.user.nivel == "cliente"
          ) {
            localStorage.setItem("token", response.data.token);
            localStorage.setItem("nome", response.data.user.name);
            localStorage.setItem("nivel", response.data.user.nivel);

            document.location.reload(true);
          }
        })
        .catch((error) => {
          console.log(error);
          this.text_btn_login = "Entrar";
        })
        .finally(() => {});
    },
    userLogado() {
      axios
        .get("/api/user-logado")
        .then((response) => {
          this.caixaUser = response.data;
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {});
    },

    loadRealtime() {
      window.Echo.channel("live-futebol-hoje").listen(
        "LiveHojeFutebol",
        (e) => {
          if (this.vivo) {
            this.events_hoje = e;
            if (this.hoje && !this.leagueOp) {
              this.events = e;
            }
          }
        }
      );

      window.Echo.channel("live-futebol-amanha").listen(
        "LiveAmanhaFutebol",
        (e) => {
          this.events_amanha = e;
          if (this.amanha && !this.leagueOp) {
            this.events = e;
            this.calculaCotacao();
          }
        }
      );

      window.Echo.channel("live-futebol-after").listen(
        "LiveAfeterTomorowFutebol",
        (e) => {
          this.events_depois_amanha = e;
          if (this.afetTerTomorow && !this.leagueOp) {
            this.events = e;
            this.calculaCotacao();
          }
        }
      );

      window.Echo.channel("live-futebol-live").listen("LiveFutebol", (e) => {
        // console.log('live ', e)
        this.events_vivo = e;
        if (this.live && !this.leagueOp) {
          this.events = e;
          this.calculaCotacao();
        }
      });

      //Load dias da semana
      window.Echo.channel("load-day").listen("LoadDayEnvent", (data) => {
        this.days = data;
      });

      //Load ligas
      window.Echo.channel("load-league").listen("LoadLigas", (data) => {
        this.leagues = data;
      });

      window.Echo.channel("load-league-main").listen(
        "LoadLigasMain",
        (data) => {
          this.leagues_main = data;
        }
      );

      //Configurações real time
      window.Echo.channel("load-configurations").listen(
        "LoadConfiguration",

        (data) => {
          this.limitesUser = data;
          this.max_cotacao = data.cotacao_max_bilhete;
          this.mini_cotacao = data.cotacao_mini_bilhete;
          this.aposta_ativa = data.aposta_ativa;
          this.premio_max = data.premio_max;
          this.max_jogos_bilehte = data.quantidade_jogos_max_bilhete;
          this.mini_jogos_bilhete = data.quantidade_jogos_mini_bilhete;
          this.valor_max_aposta = data.valor_max_aposta;
          this.valor_mini_aposta = data.valor_mini_aposta;
          this.op_ufcbox = data.op_ufcbox;
          this.op_basquete = data.op_basquete;
          this.op_tenis = data.op_tenis;

          if (data.futebol_ao_vivo == "Sim") {
            this.vivo = true;
          } else {
            this.vivo = false;
            this.live = false;
          }
        }
      );
      window.Echo.channel("refreshmatch").listen("LoadRefreshOdd", (data) => {
        for (let i = 0; i < data.length; i++) {
          // console.log(data[i].event_id)
          if (data[i].event_id === this.matchSelected) {
            // this.match = {}
            // this.mercados = []
            // console.log('match selected', data[i], 'mercados', data[i].mercados)
            this.match = data[i];
            this.mercados = data[i].mercados;
          }
        }
      });

      // window.Echo.channel("match-load").listen("LoadMatchLiveScore", data => {

      //       //  console.log('score live',data);
      //     if (data.id == this.match.id && this.live) {
      //       this.match = data;
      //     //console.log('igual')
      //     }

      // });

      //Load match carregada
    },
    viewValidarPin() {
      $("#modal-validar-pin").modal("show");
    },
    validaPin() {
      this.selection = this.selecionados;
      if (this.pin == "") {
        this.errorLogin = true;
        this.messageError = "Preencha o campo com um PIN!";
        return;
      }

      axios
        .post("/api/print-bilhete-get-cod-site", { cupom: this.pin })
        .then((response) => {
          console.log("aposta", response);
          $("#modal-validar-pin").modal("hide");
          this.pin = "";
          this.apostado = response.data[0].valor_apostado;
          this.cliente = response.data[0].cliente;
          this.selection = response.data[0].palpites;
          this.calculaCotacao();
        })
        .catch((error) => {
          console.log("erro", error);

          if (error.response.status == 404) {
            $("#modal-validar-pin").modal("hide");
            alert("Bilhete não encontrado!");
            return;
          }
        })
        .finally(() => {});
    },
    loadDay() {
      axios.get("/api/dias-futebol").then((response) => {
        this.days = response.data;
      });
    },
    allMatchs() {
      this.live = false;
      this.eventsAll = true;
      //this.events = this.events_all;
      this.hoje = false;
      this.amanha = false;
      this.afetTerTomorow = false;
      this.selection = this.selecionados;
      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/all-matchs";
      } else {
        rota = "/api/site-all-matchs";
      }
      axios
        .get(rota)
        .then((response) => {
          this.events_all = response.data;
          this.events = this.events_all;
        })
        .catch((error) => {
          console.log(error);
        })

        .finally(() => {
          this.loading = false;

          for (var j = 0; j < this.selection.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          return;
        });
    },
    loadRelatorio() {
      $("#modal-relatorio").modal("show");
    },
    sendRelatorio() {
      if (this.date1 != "" && this.date2 != "") {
        this.loadingCaixa = true;
        axios
          .post("/api/relatorio-cambista", {
            date1: this.date1,
            date2: this.date2,
          })
          .then((response) => {
            if (response.data[0] != undefined) {
              this.loadingCaixa = false;
              this.relatorio = response.data[0];
            } else {
              this.loadingCaixa = false;
              this.relatorio = {};
              return;
            }
          })
          .catch((error) => {
            console.log("erro", error);
            this.loadingCaixa = true;
            if (error.response.status == 404) {
              this.loadingCaixa = true;

              return;
            }
          })
          .finally(() => {});
      } else {
      }
    },
    seachDay(data) {
      this.selection = this.selecionados;
      this.leagueOp = false;
      this.jogosView = true;
      this.bilheteView = false;

      if (data == 0) {
        this.live = false;
        this.events = this.events_hoje;
        this.hoje = true;
        this.amanha = false;
        this.afetTerTomorow = false;
        this.loadMatchHoje();
      }
      if (data == 1) {
        this.live = false;
        this.events = this.events_amanha;
        this.hoje = false;
        this.amanha = true;
        this.afetTerTomorow = false;
        this.loadMatchAmanha();
      }
      if (data == 2) {
        this.live = false;
        this.events = this.events_depois_amanha;
        this.hoje = false;
        this.amanha = false;
        this.afetTerTomorow = true;
        this.loadMatchDepoisAmanha();
      }

      this.calculaCotacao();
    },
    loadFutebol() {
      this.leagueOp = false;
      this.events = this.events_hoje;
      this.live = false;
      this.hoje = true;
      this.amanha = false;
      this.afetTerTomorow = false;
      this.jogosView = true;
      this.bilheteView = false;
      this.selection = this.selecionados;
      this.loadMatchHoje();
      this.calculaCotacao();
    },
    loadVivo() {
      this.selection = this.selecionadosLive;
      this.leagueOp = false;
      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      this.live = true;
      this.hoje = false;
      this.amanha = false;
      this.afetTerTomorow = false;
      axios
        .get("/api/site-live-futebol")
        .then((response) => {
          this.events_vivo = response.data;
          this.events = this.events_vivo;
          if (this.events.length === 0) {
            console.log(this.notEvent);
          }
        })
        .catch((error) => {
          console.log(error);
        })

        .finally(() => {
          this.loading = false;

          for (var j = 0; j < this.selection.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          this.calculaCotacao();
          return;
        });
    },
    clique() {
      alert("estou aqui dentro");
    },
    is_img(img) {
      let file = "https://assets.b365api.com/images/team/m/" + img + ".png";
      var img = new Image();
      img.src = file;

      return img.width;
    },
    loadLimites() {
      //toogle menu
      $(document).on("click", ".sidebar-menu", function () {
        $("body").removeClass("sidebar-open");
      });

      axios.get("/api/list-limites").then((response) => {
        this.limitesUser = response.data[0];

        if (this.limitesUser.futebol_ao_vivo == "Sim") {
          this.vivo = true;
        } else {
          this.vivo = false;
        }

        this.max_cotacao = response.data[0]["cotacao_max_bilhete"];
        this.mini_cotacao = response.data[0]["cotacao_mini_bilhete"];
        this.aposta_ativa = response.data[0]["aposta_ativa"];
        this.premio_max = response.data[0]["premio_max"];
        this.max_jogos_bilehte =
          response.data[0]["quantidade_jogos_max_bilhete"];
        this.mini_jogos_bilhete =
          response.data[0]["quantidade_jogos_mini_bilhete"];
        this.valor_max_aposta = response.data[0]["valor_max_aposta"];
        this.valor_mini_aposta = response.data[0]["valor_mini_aposta"];
        this.op_ufcbox = response.data[0]["op_ufcbox"];
        this.op_basquete = response.data[0]["op_basquete"];
        this.op_tenis = response.data[0]["op_tenis"];
        this.futebol_ao_vivo = response.data[0]["futebol_ao_vivo"];
      });
    },
    loadCaixa() {
      $("#modal-caixa").modal("show");
    },

    loadRegulamento() {
      $("#modal-regulamento").modal("show");
      axios.get("/api/regulamento").then((response) => {
        this.regulamento = response.data[0]["regulamento"];
      });
    },
    loadBilhetes() {
      this.loading = true;
      this.jogosView = false;
      this.bilheteView = true;
      this.bilhetesLogado = [];
      axios
        .get("/api/bilhetes")
        .then((response) => {
          this.loading = false;
          this.bilhetesLogado = response.data;
        })
        .catch((error) => {
          this.loading = false;
          console.log("erro", error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    viewBilhete(id) {
      $("#modal-bilhete").modal("show");
      this.bilhetes = [];
      axios
        .get("/api/print-bilhete-id/" + id)
        .then((response) => {
          this.link =
            "whatsapp://send?text=" +
            window.location.href +
            "api/bilhete/" +
            id;
          this.bilhetes = response.data;
        })
        .catch((err) => {
          console.log(err);
        })
        .finally(() => {});
    },
    alterarBilhete(id, bilhete) {
      var r = confirm("Deseja realmente excluir o bilhete?");
      if (r == true) {
        let value = "Aberto";

        axios
          .get("/api/cancela-bilhete/" + id)
          .then((response) => {
            this.$notify({
              group: "foo",
              title: "Sucesso!",
              text: "Bilhete cancelado  com sucesso!",
              type: "success",
              duration: 3000,
              speed: 1000,
            });
            bilhete.status = "Cancelado";
            this.userLogado();
          })
          .catch((error) => {
            this.$notify({
              group: "foo",
              title: "Erro!",
              text: "Erro ao cancelar o bilhete!",
              type: "error",
              duration: 3000,
              speed: 1000,
            });
          })
          .finally(() => {});
      } else {
        return;
      }
    },

    pesquisaBilhetes(date) {
      this.bilhetesLogado = [];
      this.loading = true;
      axios
        .post("/api/search-bilhetes", { date: date })
        .then((response) => {
          this.loading = false;
          this.bilhetesLogado = response.data;
        })
        .catch((error) => {
          this.loading = false;
          console.log("erro", error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    loadLeagues() {
      axios
        .get("/api/site-list-leagues")
        .then((response) => {
          this.leagues = response.data;
        })
        .catch(() => {})
        .finally(() => {});
    },
    loadLeaguesMain() {
      axios
        .get("/api/site-list-leagues-main")
        .then((response) => {
          //console.log(response);
          this.leagues_main = response.data;
        })
        .catch(() => {})
        .finally(() => {});
    },
    loadMatchHojeMain() {
      this.jogosView = true;
      this.bilheteView = false;
      this.events_main = [];
      this.loading = true;
      axios
        .get("/api/site-partidas-home")
        .then((response) => {
          this.events_main = response.data;
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.loading = false;

          for (var j = 0; j < this.selection.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          return;
        });
    },
    loadMatchHoje() {
      this.selection = this.selecionados;

      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/partidas-home";
      } else {
        rota = "/api/site-partidas-home";
      }
      axios
        .get(rota)
        .then((response) => {
          this.events_hoje = response.data;
          this.events = this.events_hoje;
          if (response.data.length == 0) {
            this.noJogos = true;
          }
        })
        .catch((error) => {
          console.log(error);
        })

        .finally(() => {
          this.loading = false;

          for (var j = 0; j < this.selection.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          return;
        });
    },

    loadMatchAmanha() {
      this.selection = this.selecionados;

      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/partidas-amanha";
      } else {
        rota = "/api/site-partidas-amanha";
      }

      axios
        .get(rota)
        .then((response) => {
          this.events_amanha = response.data;
          this.events = response.data;
        })
        .catch(() => {})
        .finally(() => {
          this.loading = false;

          for (var j = 0; j < this.selection.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          return;
        });
    },
    loadMatchDepoisAmanha() {
      this.selection = this.selecionados;
      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/partidas-depois-amanha";
      } else {
        rota = "/api/site-partidas-depois-amanha";
      }
      axios
        .get(rota)
        .then((response) => {
          this.events_depois_amanha = response.data;
          this.events = response.data;
        })
        .catch(() => {})
        .finally(() => {
          this.loading = false;

          for (var j = 0; j < this.selection.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          return;
        });
    },

    seachLeague(name) {
      this.selection = this.selecionados;

      this.leagueOp = true;
      this.jogosView = true;
      this.bilheteView = false;
      this.live = false;
      this.events = [];
      this.loading = true;
      let rota;
      if (localStorage.getItem("token") != null) {
        rota = "/api/search-league";
      } else {
        rota = "/api/site-search-league";
      }

      axios
        .post(rota, { league: name })
        .then((response) => {
          this.events = response.data;
        })
        .catch(() => {})
        .finally(() => {
          this.loading = false;

          for (var j = 0; j < this.selection.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          return;
        });
    },
    btnValorApostado(valor) {
      this.apostado = valor;
    },
    loadOdd(league, match, matchLive) {
      this.matchSelected = match.event_id;
      console.log("m", match);

      console.log("partida aberta", match.event_id);

      if (this.live) {
        var rota = "/api/site-list-odds-live/";
        this.selection = this.selecionadosLive;
      } else {
        this.selection = this.selecionados;
        var rota = "/api/site-list-odds/";
      }
      this.mercados = [];
      this.match = match;
      this.liga = league;
      this.loading_odds = true;
      $("#modal-match").modal("show");
      axios
        .get(rota + this.match.id)
        .then((response) => {
          if (this.live) {
            this.mercados = response.data[0].mercados;
          } else {
            this.mercados = response.data;
          }
        })
        .catch(() => {})
        .finally(() => {
          this.loading_odds = false;

          for (var j = 0; j < this.selecionados.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selecionados[j].partida +
                "][taxa=" +
                this.selecionados[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selecionados[j].partida +
                "][taxa=" +
                this.selecionados[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          return;
        });
    },
    //odd.id, match.event_id, odd.odd, odd.cotacao, event.league, match.date, match.home, match.away
    addPalpite(
      uuid,
      idOdd,
      sport,
      partida,
      group_opp,
      odd,
      cotacao,
      league,
      date,
      home,
      away,
      type,
      cotacaoOriginal
    ) {
      $("div[taxaJogo=" + partida + "]").removeClass("selecionado");
      $("span[taxaJogo=" + partida + "]").removeClass(
        "odd-match-plus-right-selecionado"
      );

      var newSelect = {
        uuid: uuid,
        idOdd: idOdd,
        partida: partida,
        odd: odd,
        cotacao: cotacao,
        league: league,
        date: date,
        home: home,
        away: away,
        sport: sport,
        group_opp: group_opp,
        type: type,
        selected: true,
        cotacaoOriginal: cotacaoOriginal,
      };

      console.log(newSelect);

      if (this.live == true && localStorage.getItem("token") == null) {
        return;
      }

      if (cotacao == 0) {
        return;
      }

      for (var i = 0; i < this.selection.length; i++) {
        if (
          this.selection[i].partida === partida &&
          this.selection[i].idOdd != idOdd
        ) {
          this.selection.splice(i, +1);

          this.selection.push(newSelect);
          $("div[taxaJogo=" + partida + "][taxa=" + idOdd + "]").addClass(
            "selecionado"
          );
          $("span[taxaJogo=" + partida + "][taxa=" + idOdd + "]").addClass(
            "odd-match-plus-right-selecionado"
          );

          this.calculaCotacao();
          return;
        }

        if (
          this.selection[i].partida === partida &&
          this.selection[i].idOdd === idOdd
        ) {
          this.selection.splice(i, +1);

          //this.selection.push(newSelect);

          //console.log('removido');
          this.calculaCotacao();
          return;
        }
      }

      this.selection.push(newSelect);

      $("div[taxaJogo=" + partida + "][taxa=" + idOdd + "]").addClass(
        "selecionado"
      );
      $("span[taxaJogo=" + partida + "][taxa=" + idOdd + "]").addClass(
        "odd-match-plus-right-selecionado"
      );

      this.calculaCotacao();
    },
    calculaCotacao() {
      this.total_cotacao = 1;

      for (var i = 0; i < this.selection.length; i++) {
        var premio = this.max_cotacao * this.apostado;

        this.total_cotacao = this.total_cotacao * this.selection[i].cotacao;

        this.retorno = this.apostado * this.total_cotacao;

        if (this.total_cotacao > this.max_cotacao) {
          this.total_cotacao = this.max_cotacao;
        }

        if (this.retorno > premio) {
          this.retorno = premio;
        }

        if (this.retorno > this.premio_max) {
          this.retorno = this.premio_max;
        }

        const valComission =
          this.retorno * (this.limitesUser.comissao_premio / 100);
        this.retornoCambista = this.retorno - valComission;
      }

      for (var j = 0; j < this.selection.length; j++) {
        $(
          "div[taxaJogo=" +
            this.selection[j].partida +
            "][taxa=" +
            this.selection[j].idOdd +
            "]"
        ).addClass("selecionado");
        $(
          "span[taxaJogo=" +
            this.selection[j].partida +
            "][taxa=" +
            this.selection[j].idOdd +
            "]"
        ).addClass("odd-match-plus-right-selecionado");
      }
      return;
    },
    removePalpite(id) {
      this.selection.map(function (atualItem, index, arr) {
        if (atualItem.idOdd == id) {
          $("div[taxaJogo=" + atualItem.partida + "]").removeClass(
            "selecionado"
          );
          $("span[taxaJogo=" + atualItem.partida + "]").removeClass(
            "odd-match-plus-right-selecionado"
          );
          arr.splice(index, 1);
        }
      });
      this.calculaCotacao();
    },

    //Aqui
    removePalpites(palpites) {
      this.selecionados = [];
      this.selection = [];

      this.calculaCotacao();
      for (var i = 0; palpites.length; i++) {
        $("div[taxaJogo=" + palpites[i].partida + "]").removeClass(
          "selecionado"
        );
        $("span[taxaJogo=" + palpites[i].partida + "]").removeClass(
          "odd-match-plus-right-selecionado"
        );
      }
    },
    zeraPalpites(palpites) {
      for (var i = 0; palpites.length; i++) {
        $("div[taxaJogo=" + palpites[i].partida + "]").removeClass(
          "selecionado"
        );
        $("span[taxaJogo=" + palpites[i].partida + "]").removeClass(
          "odd-match-plus-right-selecionado"
        );
      }
    },
    mostraPalpites() {
      $("#modal-cupon").modal("show");
    },

    setValApostado(val) {
      this.apostado = val;
      this.calculaCotacao();
    },

    enviarAposta() {
      this.bilhetes = [];
      if (this.cliente == "") {
        alert("Insira um nome no campo cliente!");
        return;
      }
      if (this.apostado < this.valor_mini_aposta) {
        alert("Valor apostado inferior ao permitido!");
        return;
      }
      if (this.apostado > this.valor_max_aposta) {
        alert("Valor apostado superior ao permitido!");
        return;
      }
      if (this.selection.length < this.mini_jogos_bilhete) {
        alert("Verifique a quantidade minima de jogos no bilehte!");
        return;
      }

      if (
        this.selection.length == 1 &&
        this.apostado > this.caixaUser.saldo_simples
      ) {
        alert("Verifique o saldo em apostas simples!");
        return;
      }
      if (
        this.selection.length > 1 &&
        this.apostado > this.caixaUser.saldo_casadinha
      ) {
        alert("Verifique o saldo em apostas casadas!");
        return;
      }

      if (this.total_cotacao <= this.mini_cotacao) {
        alert("Valor de cotação não permitido!");
        return;
      }

      if (this.selection.length > this.max_jogos_bilehte) {
        alert(
          `Quantidade permitida de jogos é de: (${this.max_jogos_bilehte})`
        );
        return;
      }
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/send-aposta-site";
      } else {
        rota = "/api/send-pre-aposta";
      }
      $(".btnSendBet").css("visibility", "hidden");
      $(".loadSendBet").css("visibility", "visible");
      axios
        .post(rota, {
          valor_apostado: this.apostado,
          retorno_possivel: this.retorno,
          retorno_cambista: this.retornoCambista,
          cliente: this.cliente,
          total_palpites: this.selection.length,
          cotacao: this.total_cotacao,
          palpites: this.selection,
        })
        .then((response) => {
          if (localStorage.getItem("token") != null) {
            $("#modal-cupon").modal("hide");

            //this.printJogos(response.data.id);

            this.bilhetes.push(response.data);
            $("#modal-bilhete").modal("show");
            this.userLogado();
            this.link =
              "whatsapp://send?text=" +
              window.location.href +
              "api/bilhete/" +
              response.data.id;
          } else {
            this.cupom_pre_aposta = response.data.cupom;
            this.link = "whatsapp://send?text=" + response.data.cupom;
            $("#modal-cupon").modal("hide");
            // $("#modal-pre-aposta").modal("show");
            this.bilhetes.push(response.data);
            $("#modal-bilhete").modal("show");
          }

          // console.log('resultado', response.status)
          $(".btnSendBet").css("visibility", "visible");
          $(".loadSendBet").css("visibility", "hidden");
          //this.zeraPalpites(this.selection);
          this.apostado = 0;
          this.retorno = 0;
          this.cliente = "";
          //this.selection = 0;
          this.total_cotacao = 1;
          //this.selection = [];
          this.removePalpites(this.selection);
        })
        .catch((error) => {
          if (error.response.status == 404) {
            alert("Erro ao enviar aposta!");
          }
        })
        .finally(() => {
          // // console.log('resultado', response.status)
          //       $(".btnSendBet").css("visibility", 'visible');
          //       $(".loadSendBet").css("visibility", 'hidden');
          //       //this.zeraPalpites(this.selection);
          //       this.apostado = 0;
          //       this.retorno = 0;
          //       this.cliente = "";
          //       //this.selection = 0;
          //       this.total_cotacao = 1;
          //       //this.selection = [];
          //       this.removePalpites(this.selection);
        });
    },
    enviarApostaLive() {
      this.bilhetes = [];
      if (this.cliente == "") {
        alert("Insira um nome no campo cliente!");
        return;
      }
      if (this.total_cotacao <= this.mini_cotacao) {
        alert("Valor de cotação não permitido!");
        return;
      }
      if (this.apostado < this.valor_mini_aposta) {
        alert("Valor apostado inferior ao permitido!");
        return;
      }
      if (this.apostado > this.valor_max_aposta) {
        alert("Valor apostado superior ao permitido!");
        return;
      }
      if (this.selection.length < this.mini_jogos_bilhete) {
        alert("Verifique a quantidade minima de jogos no bilehte!");
        return;
      }

      if (
        this.selection.length == 1 &&
        this.apostado > this.caixaUser.saldo_simples
      ) {
        alert("Verifique o saldo em apostas simples!");
        return;
      }
      if (
        this.selection.length > 1 &&
        this.apostado > this.caixaUser.saldo_casadinha
      ) {
        alert("Verifique o saldo em apostas casadas!");
        return;
      }

      if (this.selection > this.max_jogos_bilehte) {
        alert("Verifique a quantidade máxima de jogos no bilehte!");
        return;
      }
      let cota = this.total_cotacao;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/send-aposta-live";
      } else {
        rota = "/api/send-pre-aposta";
      }
      $(".btnSendBet").css("visibility", "hidden");
      $(".loadSendBet").css("visibility", "visible");
      axios
        .post(rota, {
          valor_apostado: this.apostado,
          retorno_possivel: this.retorno,
          cliente: this.cliente,
          total_palpites: this.selection.length,
          cotacao: this.total_cotacao,
          palpites: this.selection,
        })
        .then((response) => {
          // $("#modal-cupon").modal("hide");
          this.selection = response.data;
          this.calculaCotacao();
          if (cota <= this.total_cotacao) {
            axios
              .post("/api/send-valid-live", {
                valor_apostado: this.apostado,
                retorno_possivel: this.retorno,
                cliente: this.cliente,
                total_palpites: this.selection.length,
                cotacao: this.total_cotacao,
                palpites: this.selection,
              })
              .then((response) => {
                $("#modal-cupon").modal("hide");
                // $("#modal-pre-aposta").modal("show");
                this.bilhetes.push(response.data);
                $("#modal-bilhete").modal("show");
                $(".btnSendBet").css("visibility", "visible");
                $(".loadSendBet").css("visibility", "hidden");
                //this.zeraPalpites(this.selection);
                this.apostado = 0;
                this.retorno = 0;
                this.cliente = "";
                //this.selection = 0;
                this.total_cotacao = 1;
                //this.selection = [];
                this.removePalpites(this.selection);
                this.link =
                  "whatsapp://send?text=" +
                  window.location.href +
                  "api/bilhete/" +
                  response.data.id;
              })
              .catch((error) => {
                console.log(error);
              })
              .finally(() => {});
          } else {
            Alert("Alterações nas Odds!");
          }
          //  console.log(response.status);
          //         //console.log('status', response.status)

          //         if(response.status == 200) {

          //         // //this.printJogos(response.data.id);

          //         this.bilhetes.push(response.data.aposta);
          //         $("#modal-bilhete").modal("show");

          //         this.userLogado();
          //         //this.zeraPalpites(this.selection);
          //         this.apostado = 0;
          //         this.retorno = 0;
          //         this.cliente = "";
          //         //this.selection = 0;
          //         this.total_cotacao = 1;
          //         //this.selection = [];
          //         this.removePalpites(this.selection);

          //         } else {

          //         }

          // console.log('resultado', response.status)
          $(".btnSendBet").css("visibility", "visible");
          $(".loadSendBet").css("visibility", "hidden");
        })
        .catch((error) => {
          //if (error.response.status == 404) {
          alert("Alterações nas Odds!");
          //}
          console.log(error);
          // console.log('resultado', response.status)
          $(".btnSendBet").css("visibility", "visible");
          $(".loadSendBet").css("visibility", "hidden");
        })
        .finally(() => {
          // // console.log('resultado', response.status)
          //       $(".btnSendBet").css("visibility", 'visible');
          //       $(".loadSendBet").css("visibility", 'hidden');
          //       //this.zeraPalpites(this.selection);
          //       this.apostado = 0;
          //       this.retorno = 0;
          //       this.cliente = "";
          //       //this.selection = 0;
          //       this.total_cotacao = 1;
          //       //this.selection = [];
          //       this.removePalpites(this.selection);
        });
    },
    searchBilhete() {
      this.bilhetes = [];
      if (this.cupom == "") {
        alert("Preencha um PIN");
        return;
      }
      axios
        .post("/api/print-bilhete-cod", { cupom: this.cupom })
        .then((response) => {
          $("#modal-bilhete").modal("show");
          this.bilhetes = response.data;
          this.link =
            "whatsapp://send?text=" +
            window.location.href +
            "api/bilhete/" +
            response.data.id;
        })
        .catch((error) => {
          console.log("erro", error);
          if (error.response.status == 404) {
            $("#modal-bilhete").modal("hide");
            alert("Bilhete não encontrado!");
            return;
          }
        })
        .finally(() => {});
    },
  },
};
</script>

