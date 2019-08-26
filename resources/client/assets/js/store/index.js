import Vue from 'vue'
import Vuex from 'vuex'
import Alert from './modules/alert'
import ChangePassword from './modules/change_password'
import Rules from './modules/rules'
import BooksIndex from './modules/Books'
import BooksSingle from './modules/Books/single'
import CharactersIndex from './modules/Characters'
import CharactersSingle from './modules/Characters/single'
import PagesIndex from './modules/Pages'
import PagesSingle from './modules/Pages/single'
import PermissionsIndex from './modules/Permissions'
import PermissionsSingle from './modules/Permissions/single'
import RolesIndex from './modules/Roles'
import RolesSingle from './modules/Roles/single'
import UsersIndex from './modules/Users'
import UsersSingle from './modules/Users/single'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
    modules: {
        Alert,
        ChangePassword,
        Rules,
        BooksIndex,
        BooksSingle,
        PermissionsIndex,
        PermissionsSingle,
        RolesIndex,
        RolesSingle,
        UsersIndex,
        UsersSingle,
        CharactersIndex,
        CharactersSingle,
        PagesIndex,
        PagesSingle
    },
    strict: debug,
})