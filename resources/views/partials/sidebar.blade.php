@inject('request', 'Illuminate\Http\Request')
<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <ul class="sidebar-menu">

            {{--  <li>
                <a href="{{ url('/') }}">
            <i class="fa fa-wrench"></i>
            <span class="title">@lang('quickadmin.qa_dashboard')</span>
            </a>
            </li> --}}

            <li v-bind:class="$route.name == 'dashboard' ? 'active' : ''">
                <router-link :to="{ name: 'dashboard' }">
                    <i class="fa fa-wrench"></i>
                    <span class="title">Dashboard</span>
                </router-link>
            </li>

            <li v-if="$can('book_access')">
                <router-link :to="{ name: 'books.index' }">
                    <i class="fa fa-book"></i>
                    <span>@lang('quickadmin.books.title')</span>
                </router-link>
            </li>

            <li v-if="$can('character_access')">
                <router-link :to="{ name: 'characters.all' }">
                    <i class="fa fa-child"></i>
                    <span>Characters</span>
                </router-link>
            </li>

            <li>
                <router-link :to="{ name: 'books_customize.index' }">
                    <i class="fa fa-check-square"></i>
                    <span>Customization</span>
                </router-link>
            </li>

            <li>
                <router-link :to="{ name: 'books_test.index' }">
                    <i class="fa fa-file-pdf-o"></i>
                    <span>Testing</span>
                </router-link>
            </li>

            <li>
                <router-link :to="{ name: 'books_deploy.index' }">
                    <i class="fa fa-upload"></i>
                    <span>Deployment</span>
                </router-link>
            </li>

            <li class="treeview" v-if="$can('user_management_access')"
                v-bind:class="$route.name == 'permissions.index' || $route.name == 'roles.index' || $route.name == 'users.index' ? 'active' : ''">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>User Management</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li v-bind:class="$route.name == 'permissions.index' ? 'active' : ''">
                        <router-link :to="{ name: 'permissions.index' }">
                            <i class="fa fa-briefcase"></i>
                            <span>Permissions</span>
                        </router-link>
                    </li>
                    <li v-bind:class="$route.name == 'roles.index' ? 'active' : ''">
                        <router-link :to="{ name: 'roles.index' }">
                            <i class="fa fa-briefcase"></i>
                            <span>Roles</span>
                        </router-link>
                    </li>
                    <li v-bind:class="$route.name == 'users.index' ? 'active' : ''">
                        <router-link :to="{ name: 'users.index' }">
                            <i class="fa fa-user"></i>
                            <span>Users</span>
                        </router-link>
                    </li>
                </ul>
            </li>

            <li>
                <router-link :to="{ name: 'auth.change_password' }">
                    <i class="fa fa-key"></i>
                    <span class="title">@lang('quickadmin.qa_change_password')</span>
                </router-link>
            </li>

            <li>
                <a href="#logout" onclick="$('#logout').submit();">
                    <i class="fa fa-arrow-left"></i>
                    <span class="title">@lang('quickadmin.qa_logout')</span>
                </a>
            </li>
        </ul>
    </section>
</aside>