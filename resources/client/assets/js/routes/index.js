import Vue from 'vue'
import VueRouter from 'vue-router'

import ChangePassword from '../components/ChangePassword.vue'
import BooksIndex from '../components/cruds/Books/Index.vue'
import BooksCreate from '../components/cruds/Books/Create.vue'
import BooksShow from '../components/cruds/Books/Show.vue'
import BooksEdit from '../components/cruds/Books/Edit.vue'
import BooksPreview from '../components/cruds/Books/Preview.vue'
import BooksCustomize from '../components/cruds/Books/CustomizeIndex.vue'
import BooksTest from '../components/cruds/Books/TestingIndex.vue'
import BooksDeploy from '../components/cruds/Books/DeployIndex.vue'
import BooksPDFCustomize from '../components/cruds/Books/PDFCustomize.vue'

import CharactersIndex from '../components/cruds/Characters/Index.vue'
import CharactersAll from '../components/cruds/Characters/All.vue'
import CharactersCreate from '../components/cruds/Characters/Create.vue'
import CharactersShow from '../components/cruds/Characters/Show.vue'
import CharactersEdit from '../components/cruds/Characters/Edit.vue'
import CharactersLayerEdit from '../components/cruds/Characters/Layer.vue'
import CharactersCheckLayer from '../components/cruds/Characters/CheckLayer.vue'

import PagesIndex from '../components/cruds/Pages/Index.vue'
import PagesCreate from '../components/cruds/Pages/Create.vue'
import PagesShow from '../components/cruds/Pages/Show.vue'
import PagesEdit from '../components/cruds/Pages/Edit.vue'
import PagesCheckLayer from '../components/cruds/Pages/CheckLayer.vue'
import PagesSort from '../components/cruds/Pages/Sort.vue'


import PermissionsIndex from '../components/cruds/Permissions/Index.vue'
import PermissionsCreate from '../components/cruds/Permissions/Create.vue'
import PermissionsShow from '../components/cruds/Permissions/Show.vue'
import PermissionsEdit from '../components/cruds/Permissions/Edit.vue'
import RolesIndex from '../components/cruds/Roles/Index.vue'
import RolesCreate from '../components/cruds/Roles/Create.vue'
import RolesShow from '../components/cruds/Roles/Show.vue'
import RolesEdit from '../components/cruds/Roles/Edit.vue'
import UsersIndex from '../components/cruds/Users/Index.vue'
import UsersCreate from '../components/cruds/Users/Create.vue'
import UsersShow from '../components/cruds/Users/Show.vue'
import UsersEdit from '../components/cruds/Users/Edit.vue'

import Dashboard from '../components/Dashboard.vue'
import Books from '../store/modules/Books';

Vue.use(VueRouter)

const routes = [
    { path: '/change-password', component: ChangePassword, name: 'auth.change_password' },

    { path: '/books', component: BooksIndex, name: 'books.index' },
    { path: '/books/create', component: BooksCreate, name: 'books.create' },
    { path: '/books/:id', component: BooksShow, name: 'books.show' },
    { path: '/books/:id/edit', component: BooksEdit, name: 'books.edit' },
    { path: '/books/:id/preview', component: BooksPreview, name: 'books.preview' },
    { path: '/book-customize', component: BooksCustomize, name: 'books_customize.index' },
    { path: '/book-test', component: BooksTest, name: 'books_test.index' },
    { path: '/book-deploy', component: BooksDeploy, name: 'books_deploy.index' },
    { path: '/books/:id/pdf-customize', component: BooksPDFCustomize, name: 'books.pdf_customize' },

    //*************for character**************/
    { path: '/characters', component: CharactersAll, name: 'characters.all' },
    { path: '/book/:id/characters', component: CharactersIndex, name: 'characters.index' },
    { path: '/book/:id/characters/create', component: CharactersCreate, name: 'characters.create' },
    { path: '/characters/:id', component: CharactersShow, name: 'characters.show' },
    { path: '/characters/:id/edit', component: CharactersEdit, name: 'characters.edit' },
    { path: '/characters/:id/layer', component: CharactersLayerEdit, name: 'characters.layer' },
    { path: '/characters/:id/layer-check', component: CharactersCheckLayer, name: 'characters.layer_check' },


    //*************for page**************/
    { path: '/book/:id/pages', component: PagesIndex, name: 'pages.index' },
    { path: '/book/:id/sort-pages', component: PagesSort, name: 'pages.sort' },
    { path: '/book/:id/pages/create', component: PagesCreate, name: 'pages.create' },
    { path: '/pages/:id', component: PagesShow, name: 'pages.show' },
    { path: '/pages/:id/edit', component: PagesEdit, name: 'pages.edit' },
    { path: '/pages/:id/layer-check', component: PagesCheckLayer, name: 'pages.layer_check' },


    { path: '/permissions', component: PermissionsIndex, name: 'permissions.index' },
    { path: '/permissions/create', component: PermissionsCreate, name: 'permissions.create' },
    { path: '/permissions/:id', component: PermissionsShow, name: 'permissions.show' },
    { path: '/permissions/:id/edit', component: PermissionsEdit, name: 'permissions.edit' },
    { path: '/roles', component: RolesIndex, name: 'roles.index' },
    { path: '/roles/create', component: RolesCreate, name: 'roles.create' },
    { path: '/roles/:id', component: RolesShow, name: 'roles.show' },
    { path: '/roles/:id/edit', component: RolesEdit, name: 'roles.edit' },
    { path: '/users', component: UsersIndex, name: 'users.index' },
    { path: '/users/create', component: UsersCreate, name: 'users.create' },
    { path: '/users/:id', component: UsersShow, name: 'users.show' },
    { path: '/users/:id/edit', component: UsersEdit, name: 'users.edit' },

    { path: '/dashboard', component: Dashboard, name: 'dashboard' },

    { path: '*', redirect: { name: 'dashboard' } },
]

export default new VueRouter({
    mode: 'history',
    base: '/admin',
    routes
})