function initialState() {
    return {
        item: {
            id: null,
            name: null,
            img: null,
            img_url: null,
            user_id: null,
            user: null,
        },

        characters: [],
        pages: [],
        usersAll: [],
        loading: false,
        loadingPage: false,
        paramJson: null,
    }
}

const getters = {
    item: state => state.item,
    loading: state => state.loading,
    loadingPage: state => state.loadingPage,
    usersAll: state => state.usersAll,
    characters: state => state.characters,
    pages: state => state.pages,
    paramJson: state => state.paramJson

}

const actions = {
    storeData({ commit, state, dispatch }) {
        commit('setLoading', true)
        dispatch('Alert/resetState', null, { root: true })

        return new Promise((resolve, reject) => {
            let params = new FormData();

            for (let fieldName in state.item) {
                if (fieldName === "user") {
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

            if (state.item.user != null) {
                params.set('user_id', state.item.user.id);
            }

            axios.post('/api/v1/books', params)
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
                if (fieldName === "user") {
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

            if (state.item.user != null) {
                params.set('user_id', state.item.user.id);
            }

            axios.post('/api/v1/books/' + state.item.id, params)
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
    fetchData({ commit, dispatch }, id) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/books/' + id)
                .then(response => {
                    commit('setItem', response.data.data)
                    resolve()
                })
                .finally(() => {
                    commit('setLoading', false)
                })
        });
    },
    fetchCharactersInfo({ commit }, id) {
        commit('setLoading', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/books/' + id + '/get-characters-info')
                .then(response => {
                    commit('setCharacters', response.data.data)
                    resolve()
                }).catch(error => {
                    console.log(error.response.data)
                    let message = error.response.data.message
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
    fetchPagesSVG({ commit }, id) {
        commit('setLoading_1', true)
        return new Promise((resolve, reject) => {
            axios.get('/api/v1/books/' + id + '/get-pages-svg')
                .then(response => {
                    commit('setPages', response.data.data)
                    resolve(response.data.data)
                }).catch(error => {
                    console.log(error.response.data)
                    let message = error.response.data.message
                    let errors = error.response.data.errors

                    dispatch(
                        'Alert/setAlert', { message: message, errors: errors, color: 'danger' }, { root: true })

                    reject(error)
                })
                .finally(() => {
                    commit('setLoading_1', false)
                })
        });
    },
    applyPageSVG({ commit, state, dispatch }, index) {
        if (index >= state.pages.length) {
            return;
        }
        commit('setPageLoading', {
            index: index,
            loading: true
        })

        return new Promise((resolve, reject) => {

            let params = new FormData();
            params.set("id", state.pages[index].id);
            params.set("index", index);
            params.set("json", state.paramJson);

            axios.post("/api/v1/pages/apply-svg", params)
                .then(response => {
                    console.log(response);
                    commit('setPageImgContent', response.data)
                    resolve()
                }).catch(error => {
                    console.log(error.response.data)
                    let message = error.response.data.message
                    let errors = error.response.data.errors

                    dispatch(
                        'Alert/setAlert', { message: message, errors: errors, color: 'danger' }, { root: true })

                    reject(error)
                })
                .finally(() => {
                    commit('setPageLoading', {
                        index: index,
                        loading: false
                    })
                })
        });
    },


    fetchUsersAll({ commit }) {
        axios.get('/api/v1/users')
            .then(response => {
                commit('setUsersAll', response.data.data)
            })
    },
    generatePDF({ commit, state }, param) {
        return new Promise((resolve, reject) => {
            if (!state.item.id) {
                reject("no book!");
            } else {
                let params = new FormData();
                params.set("id", state.item.id);
                params.set("type", param.type);
                params.set("language", param.language);
                params.set("font_style", param.fontStyle);
                params.set("characters", param.characters);

                axios.post('/api/v1/books/generate-pdf', params)
                    .then(response => {
                        console.log(response.data.data);
                        resolve(response.data.data);
                    })
                    .catch(error => {
                        message = error.response.data.message || error.message
                        reject(error)
                    })
                    .finally(() => {})
            }

        });
    },
    resetPageContent({ commit }) {
        commit('resetPage');
    },

    // setPageImgPath({ commit }, value) {
    //     commit('setPageImgPath', value)
    // },
    // setPageLoading({ commit }, value) {
    //     commit('setPageLoading', value)
    // },
    setName({ commit }, value) {
        commit('setName', value)
    },
    setLogo({ commit }, value) {
        commit('setLogo', value)
    },
    setArtist({ commit }, value) {
        commit('setArtist', value)
    },
    updateCustomize({ commit }, value) {
        commit('setCustomize', value)
    },
    setParamJSON({ commit }, value) {
        commit('setParamJSON', value)
    },
    resetState({ commit }) {
        commit('resetState')
    },
    setItem({ commit }, item) {
        commit('setItem', item)
    }
}

const mutations = {
    setItem(state, item) {
        state.item = item
    },
    setName(state, value) {
        state.item.name = value
    },
    setLogo(state, value) {
        state.item.img = value
    },
    setArtist(state, value) {
        state.item.user = value;
    },
    setLoading(state, loading) {
        state.loading = loading
    },
    setLoading_1(state, loading) {
        state.loadingPage = loading
    },
    setPages(state, value) {
        state.pages = value;
    },
    resetState(state) {
        state = Object.assign(state, initialState())
    },
    setUsersAll(state, value) {
        state.usersAll = value;
    },
    setParamJSON(state, value) {
        state.paramJson = value;
    },
    setCharacters(state, value) {
        state.characters = value
    },
    setCustomize(state, value) {
        //find character
        for (var i in state.characters) {
            if (state.characters[i]['id'] == value.id) {
                state.characters[i]['result'] = _.cloneDeep(value.result);
                console.log(value.result);
                break;
            }
        }
    },
    setPageImgPath(state, value) {
        console.log(value);
        // state.pages[value.index]['img_path'] = value.path + "?t=" + new Date().getTime();
        Vue.set(state.pages, value.index, {...state.pages[value.index], img_path: value.path + "?t=" + new Date().getTime() })
    },
    setPageImgContent(state, value) {
        Vue.set(state.pages, value.index, {...state.pages[value.index], content: value.content })
    },
    setPageLoading(state, value) {
        console.log(value);
        //state.pages[value.index]['loading'] = value.loading
        Vue.set(state.pages, value.index, {...state.pages[value.index], loading: value.loading })
    },
    resetPage(state) {
        for (var i in state.pages) {
            state.pages[i]['content'] = null;
        }
    }
}

export default {
    namespaced: true,
    state: initialState,
    getters,
    actions,
    mutations
}