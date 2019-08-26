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
    // data: state => {
    //     let rows = state.all

    //     if (state.query.sort) {
    //         rows = _.orderBy(state.all, state.query.sort, state.query.order)
    //     }

    //     return rows.slice(state.query.offset, state.query.offset + state.query.limit)
    // },
    images: state => {
        let rows = state.all;
        var imgs = [];
        for (var item in rows) {
            imgs.push(rows[item].thumb_url);
        }
        return imgs;
    },
    data: state => state.all,
    total: state => state.all.length,
    loading: state => state.loading,
    relationships: state => state.relationships
}

const actions = {
    fetchData({ commit, state }, bId) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/get-pages/' + bId)
                .then(response => {
                    commit('setAll', response.data.data)
                    resolve()
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
        axios.delete('/api/v1/pages/' + id)
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
    changeOrder({ commit, state }, data) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            let params = new FormData();
            params.set('book_id', data['bookId'])
            params.set('pages_order', data['order'])
            axios.post('/api/v1/pages/change-order', params)
                .then(response => {
                    resolve()
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