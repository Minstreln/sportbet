export default {

    state: {
        gerentes:[],
    },

    mutations: {
        LOAD_GERENTES (state, gerentes) {
            state.gerentes = gerentes
        }
    },

    actions: {
        loadGerentes (context) {
            return axios.get('/admin/list-gerentes')
                    .then(response => context.commit('LOAD_GERENTES', response.data))
        },

        //sendGerentes (context, store)
    },

    getters: {

    }

 
}