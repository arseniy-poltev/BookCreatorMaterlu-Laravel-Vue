function initialState() {
    return {
        all: [],
        relationships: {

        },
        query: {},
        loading: false
    }
}

const getters = {
    data: state => {
        let rows = state.all

        if (state.query.sort) {
            rows = _.orderBy(state.all, state.query.sort, state.query.order)
        }

        return rows.slice(state.query.offset, state.query.offset + state.query.limit)
    },
    all: state => state.all,
    total: state => state.all.length,
    loading: state => state.loading,
    relationships: state => state.relationships
}

const actions = {
    fetchData({ commit, state }) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/books')
                .then(response => {
                    commit('setAll', response.data.data)
                    resolve();
                })
                .catch(error => {
                    message = error.response.data.message || error.message
                    commit('setError', message)
                    console.log(message)
                    reject(error)
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });
    },
    destroyData({ commit, state }, id) {
        axios.delete('/api/v1/books/' + id)
            .then(response => {
                commit('setAll', state.all.filter((item) => {
                    return item.id != id
                }))
            })
            .catch(error => {
                message = error.response.data.message || error.message
                commit('setError', message)
                console.log(message)
            })
    },
    generatePDF({ commit }, data) {

        return new Promise((resolve, reject) => {
            axios.get('/api/v1/books/' + data.id + '/generate-pdf/' + data.type)
                .then(response => {
                    console.log(response);
                    resolve();
                })
                .catch(error => {
                    message = error.response.data.message || error.message
                    reject(error)
                })
                .finally(() => {})
        });
    },
    sendBook({ state, commit, dispatch }, id) {

        return new Promise((resolve, reject) => {
            axios.get('/api/v1/books/' + id + '/send')
                .then(response => {
                    resolve()
                    alert(response.data.message);
                })
                .catch(error => {
                    console.log(error.response.data)
                    let message = error.response.data.message
                    let errors = error.response.data.errors
                    alert(message);
                    // dispatch(
                    //     'Alert/setAlert', { message: message, errors: errors, color: 'danger' }, { root: true })

                    reject(error)
                })
                .finally(() => {

                })
        });
    },
    setQuery({ commit }, value) {
        commit('setQuery', purify(value))
    },
    resetState({ commit }) {
        commit('resetState')
    }
}

const mutations = {
    setAll(state, items) {
        state.all = items
    },
    setLoading(state, loading) {
        state.loading = loading
    },
    setQuery(state, query) {
        state.query = query
    },
    resetState(state) {
        state = Object.assign(state, initialState())
    }
}

export default {
    namespaced: true,
    state: initialState,
    getters,
    actions,
    mutations
}