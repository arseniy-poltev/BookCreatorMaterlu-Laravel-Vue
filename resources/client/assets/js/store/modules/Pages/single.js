function initialState() {
    return {
        item: {
            id: null,
            img: null,
            img_url: null,
            thumb_url: null,
            file_name: null,
            book_id: null,
            characters: [],
            svg: null
        },
        exceptField: ['characters', 'svg', 'status'],

        percentCompleted: 0,
        loading: false,
        axiosCancel: null,
    }
}

const getters = {
    item: state => state.item,
    loading: state => state.loading,
    percentCompleted: state => state.percentCompleted,
    axiosCancel: state => state.axiosCancel
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



            axios.post('/api/v1/pages', params)
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
                if (state.exceptField.indexOf(fieldName) != -1) {
                    continue;
                }
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
            params.set('status', 'not_check');

            const CancelToken = axios.CancelToken;

            commit('setPercent', 0)
            commit('setAxiosCancel', null)
            axios.post('/api/v1/pages/' + state.item.id, params, {
                    cancelToken: new CancelToken(function executor(c) {
                        // An executor function receives a cancel function as a parameter

                        commit('setAxiosCancel', c)
                    }),
                    onUploadProgress: (progressEvent) => {
                        if (progressEvent.lengthComputable) {
                            var percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
                                // I need to bind this to the progress bar
                                // ??? percentCompleted;
                            commit('setPercent', percentCompleted)
                        }
                    }
                })
                .then(response => {
                    commit('setItem', response.data.data)
                    resolve()
                })
                .catch(error => {
                    if (axios.isCancel(error)) {
                        console.log('Request canceled', error.message);
                    } else {
                        // handle error 
                        let message = error.response.data.message || error.message
                        let errors = error.response.data.errors

                        dispatch(
                            'Alert/setAlert', { message: message, errors: errors, color: 'danger' }, { root: true })
                        reject(error)
                    }


                })
                .finally(() => {
                    commit('setLoading', false)
                })
        })
    },
    updateStatus({ commit, state, dispatch }, param) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            let params = new FormData();
            params.set('id', param.id)
            params.set('status', param.status)
            axios.post('/api/v1/pages/change-status', params)
                .then(response => {
                    resolve()
                }).catch(error => {

                    let message = error.response.data.message || error.message
                    let errors = error.response.data.errors

                    dispatch(
                        'Alert/setAlert', { message: message, errors: errors, color: 'danger' }, { root: true })
                    reject(error)
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });
    },
    fetchData({ commit, dispatch }, id) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/pages/' + id)
                .then(response => {
                    commit('setItem', response.data.data)
                    resolve()
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });
    },
    fetchPageSVG({ commit, state }) {
        if (!state.item.img_url) {
            return;
        }
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get(state.item.img_url)
                .then(response => {
                    commit('setSVG', response.data)
                    resolve()
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });
    },
    checkPage({ commit }, id) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/pages/' + id + '/check-page')
                .then(response => {
                    resolve(response.data)
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });
    },

    setImage({ commit }, value) {
        commit('setImage', value)
    },
    setBookId({ commit }, value) {
        commit('setBookId', value)
    },

    resetState({ commit }) {
        commit('resetState')
    }
}

const mutations = {
    setItem(state, item) {
        state.item = item
    },
    setBookId(state, value) {
        state.item.book_id = Number(value);
    },
    setImage(state, value) {
        state.item.img = value
    },
    setPercent(state, value) {
        state.percentCompleted = value
    },
    setSVG(state, value) {
        state.item.svg = value;
    },
    setAxiosCancel(state, value) {
        state.axiosCancel = value
    },
    setLoading(state, loading) {
        state.loading = loading
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