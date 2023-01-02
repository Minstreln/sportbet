import Vue from 'vue'
import Vuex from 'vuex'
import gerente from './modules/gerente'
import cambista from './modules/cambista'
import bilhete from './modules/bilhete'

Vue.use(Vuex)

const store = new Vuex.Store({

    modules: {
        gerente,
        cambista,
        bilhete,
    }
    
})


export default store