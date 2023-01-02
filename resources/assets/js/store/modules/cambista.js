export default {

    state: {
        cambistas:[],
    },

    mutations: {
        LOAD_CAMBISTAS (state, cambistas) {
            state.cambistas = cambistas
        }
    },

    actions: {
        loadCambistas (context) {
            return axios.get('/admin/list-cambistas')
                    .then(response => context.commit('LOAD_CAMBISTAS', response.data))
        },

        //sendGerentes (context, store)
    },

    getters: {

    }

 
}