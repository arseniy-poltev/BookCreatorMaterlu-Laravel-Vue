function initialState() {
    return {
        item: {
            id: null,
            name: null,
            img: null,
            img_url: null,
            book_id: null,
            svg: null,
            json: null,
            json_path: null,
        },


        loading: false,
        jsonLoading: false
    }
}

const getters = {
    item: state => state.item,
    loading: state => state.loading,
    jsonLoading: state => state.jsonLoading
}

const actions = {
    storeData({ commit, state, dispatch }) {
        commit('setLoading', true)
        dispatch('Alert/resetState', null, { root: true })

        return new Promise((resolve, reject) => {
            let params = new FormData();

            for (let fieldName in state.item) {
                let fieldValue = state.item[fieldName];
                if (typeof fieldValue !== 'object') {
                    params.set(fieldName, fieldValue);
                } else {
                    if (fieldValue && typeof fieldValue[0] !== 'object') {
                        params.set(fieldName, fieldValue);
                    } else {
                        for (let index in fieldValue) {
                            params.set(fieldName + '[' + index + ']', fieldValue[index]);
                        }
                    }
                }
            }



            axios.post('/api/v1/characters', params)
                .then(response => {
                    commit('resetState')
                    resolve()
                })
                .catch(error => {
                    let message = error.response.data.message || error.message
                    let errors = error.response.data.errors

                    dispatch(
                        'Alert/setAlert', { message: message, errors: errors, color: 'danger' }, { root: true })

                    reject(error)
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        })
    },
    updateData({ commit, state, dispatch }) {
        commit('setLoading', true)
        dispatch('Alert/resetState', null, { root: true })

        return new Promise((resolve, reject) => {
            let params = new FormData();
            params.set('_method', 'PUT')

            for (let fieldName in state.item) {
                let fieldValue = state.item[fieldName];
                if (typeof fieldValue !== 'object') {
                    params.set(fieldName, fieldValue);
                } else {
                    if (fieldValue && typeof fieldValue[0] !== 'object') {
                        params.set(fieldName, fieldValue);
                    } else {
                        for (let index in fieldValue) {
                            params.set(fieldName + '[' + index + ']', fieldValue[index]);
                        }
                    }
                }
            }



            axios.post('/api/v1/characters/' + state.item.id, params)
                .then(response => {
                    commit('setItem', response.data.data)
                    resolve()
                })
                .catch(error => {
                    let message = error.response.data.message || error.message
                    let errors = error.response.data.errors

                    dispatch(
                        'Alert/setAlert', { message: message, errors: errors, color: 'danger' }, { root: true })

                    reject(error)
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        })
    },
    updateJson({ commit, state, dispatch }, data) {
        return new Promise((resolve, reject) => {
            let params = new FormData();
            params.set("id", data.id);
            params.set("json", data.json);

            commit('setLoading', true)
            axios
                .post("/api/v1/characters/make-json", params)
                .then(response => {
                    resolve()
                })
                .catch(error => {
                    reject(error)
                })
                .finally(() => {
                    commit('setLoading', true)
                });
        });
    },
    fetchData({ commit, dispatch }, id) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/characters/' + id)
                .then(response => {
                    commit('setItem', response.data.data)
                    resolve()
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });
    },
    fetchCharacterSVG({ commit }, id) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/characters/' + id + '/load-svg')
                .then(response => {
                    commit('setSVG', response.data)
                    resolve()
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });

    },
    fetchCharacterJSON({ commit }, id) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/characters/' + id + '/get-json')
                .then(response => {
                    commit('setJSON', response.data.data)
                    resolve()
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });
    },
    applySVG({ commit }, data) {
        return new Promise((resolve, reject) => {
            let params = new FormData();
            params.set("id", data.id);
            params.set("json", JSON.stringify(data.json));

            axios.post('/api/v1/characters/apply-svg', params)

            .then(response => {
                    // commit('setJSON', response.data.data)
                    resolve(response.data)
                })
                .finally(() => {
                    // commit('setJsonLoading', false)
                })
        });
    },

    setName({ commit }, value) {
        commit('setName', value)
    },
    setImage({ commit }, value) {
        commit('setImage', value)
    },
    setBookId({ commit }, value) {
        commit('setBookId', value)
    },
    setLoading({ commit }, value) {
        commit('setLoading', value);
    },

    resetState({ commit }) {
        commit('resetState')
    }
}

const mutations = {
    setItem(state, item) {
        state.item = item
    },
    setName(state, value) {
        state.item.name = value
    },
    setBookId(state, value) {
        state.item.book_id = Number(value);
    },
    setImage(state, value) {
        state.item.img = value
    },
    setLoading(state, loading) {
        state.loading = loading
    },
    setJsonLoading(state, loading) {
        state.jsonLoading = loading
    },
    setSVG(state, value) {
        state.item.svg = value;
    },
    setJSON(state, value) {
        state.item.json = JSON.parse(value.json);
        state.item.json_path = value.path;
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