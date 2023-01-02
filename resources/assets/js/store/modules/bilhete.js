export default {

    state: {
        bilhetes:[],
        palpites:[],
    },

    mutations: {
        LOAD_BILHETES (state, bilhetes) {
            state.bilhetes = bilhetes
        },
        LOAD_PALPITES (state, palpites) {
            state.palpites = palpites
        }
    },

    actions: {
        loadBilhetes(context) {
            return axios.get('mostra-bilhetes')
                    .then(response => context.commit('LOAD_BILHETES', response.data))
        },

        loadPalpites(context) {

            return axios.get('palpites/bilhete/')
                        .then(response => context.commit('LOAD_PALPITES', response.data))
                        
        }

        //sendGerentes (context, store)
    },

    getters: {

    }

 
}