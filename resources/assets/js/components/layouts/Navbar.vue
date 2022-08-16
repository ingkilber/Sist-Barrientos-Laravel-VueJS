<template>
    <div>
        <nav class="navbar navbar-expand-lg navbar-light fixed-top">
            <a href="#"
               class="d-lg-none app-color-text"
               @click.prevent="showSideNavAction(true)"
               v-if="!showSideNav">
                <i class="la la-navicon la-2x"></i>
            </a>
            <a href="#"
               class="d-lg-none app-color-text"
               @click.prevent="showSideNavAction(false)"
               v-else>
                <i class="la la-close la-2x"></i>
            </a>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link open-todo-icon"
                       href="#"
                       @click.prevent="openTodo"
                       :class="{disabled:!isConnected && offline == 1}">
                        <i class="la la-list-ul la-2x"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle hide-dropdown-icon"
                       href="#"
                       id="navbar-profile-dropdown"
                       data-toggle="dropdown"
                       aria-haspopup="true"
                       aria-expanded="false"
                       @click.prevent="showSideNavAction(false)">
                        <img :src="appUrl+'/uploads/profile/'+profile.avatar" alt class="rounded-circle avatar"/>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right animated bounceInDown profile-dropdown border-0 p-0"
                         aria-labelledby="navbar-profile-dropdown">
                        <div class="ticker"></div>
                        <img :src="appUrl+'/uploads/profile/'+profile.avatar" alt class="rounded-circle avatar-large"/>
                        <div class="user-name">
                            <div>{{ profile.first_name+' '+profile.last_name }}</div>
                        </div>
                        <div class="dropdown-divider m-0"></div>
                        <a :href="publicPath+'/profile-view'"
                           class="dropdown-item d-flex align-items-center p-2"
                           :class="{disabled:!isConnected && offline == 1}">
                            <i class="la la-user la-2x pr-3"></i>
                            {{ trans('lang.profile_title') }}
                        </a>
                        <a href="#"
                           v-if="shortcutStatus == 1"
                           class="dropdown-item d-flex align-items-center p-2"
                           data-toggle="modal"
                           data-target="#shortcutListModal"
                           :class="{disabled:!isConnected && offline == 1}">
                            <i class="fa fa-keyboard-o fa-2x pr-3"></i>
                            {{ trans('lang.shortcut_list') }}
                        </a>
                        <a :href="publicPath+'/logout'"
                           class="dropdown-item d-flex align-items-center p-2"
                           :class="{disabled:!isConnected && offline == 1}">
                            <i class="la la-sign-out la-2x pr-3"></i>
                            {{ trans('lang.logout_nv') }}
                        </a>
                    </div>
                </li>
            </ul>
            <div class="d-lg-none mobile-app-logo">
                <a :href="publicPath+'/dashboard'">
                    <img :src="appUrl+'/uploads/logo/'+applogo" alt/>
                </a>
            </div>
            <div class="side-nav"
                 :class="{'side-nav-animate-show':showSideNav, 'side-nav-animate-hide': showSideNav===false}">
                <ul>
                    <li>
                        <a :href="publicPath+'/dashboard'" class="app-color-text">
                            <i class="la la-desktop la-2x"></i>
                            {{ trans('lang.dashboard') }}
                        </a>
                    </li>
                    <li>
                        <a :href="publicPath+'/contacts'" class="app-color-text">
                            <i class="la la-users la-2x"></i>
                            {{
                            trans('lang.contacts') }}
                        </a>
                    </li>
                    <li>
                        <a :href="publicPath+'/products'" class="app-color-text">
                            <i class="la la-share-alt la-2x"></i>
                            {{ trans('lang.products') }}
                        </a>
                    </li>
                    <li>
                        <a :href="publicPath+'/sales'" class="app-color-text">
                            <i class="la la-credit-card la-2x"></i>
                            {{ trans('lang.sales') }}
                        </a>
                    </li>
                    <li>
                        <a :href="publicPath+'/receives'" class="app-color-text">
                            <i class="la la-truck la-2x"></i>
                            {{
                            trans('lang.receives') }}
                        </a>
                    </li>
                    <li>
                        <a :href="publicPath+'/reports'" class="app-color-text">
                            <i class="la la-pie-chart la-2x"></i>
                            {{ trans('lang.reports') }}
                        </a>
                    </li>
                    <li>
                        <a :href="publicPath+'/settings'" class="app-color-text">
                            <i class="la la-gear la-2x"></i>
                            {{
                            trans('lang.settings') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="side-nav-close"
                 :class="{'d-none':!showSideNav}"
                 @click.prevent="showSideNavAction(false)">
            </div>
        </nav>

        <!--Shortcut List Modal-->
        <div class="modal fade"
             id="shortcutListModal"
             tabindex="-1"
             role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="row">
                            <div class="col-10">
                                <h5 class="m-0">{{ trans('lang.shortcut_list') }}</h5>
                            </div>
                            <div class="col-2 text-right">
                                <button type="button"
                                        class="close"
                                        data-dismiss="modal"
                                        aria-label="Close"
                                        @click.prevent>
                                    <i class="la la-close icon-modal-cross"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-layout-content">
                        <table class="table table-borderless shortcut-list">
                            <tbody>
                            <tr v-for="(el, key) in shortcutKeyInfo">
                                <th>
                                    <span class="border rounded px-2 py-1 m-2">{{ el.shortCutKey }}</span>
                                </th>
                                <td>
                                    <span v-if="key === 'loadSalesPage'">
                                        {{ trans('lang.go_to_sales_page') }}
                                    </span>
                                    <span v-else-if="key === 'productSearchShortcut'">
                                        {{ trans('lang.search_product') }}
                                    </span>
                                    <span v-else-if="key === 'addCustomerShortcut'">
                                        {{ trans('lang.add_customer_label') }}
                                    </span>
                                    <span v-else-if="key === 'payShortcut'">
                                        {{ trans('lang.make_payment') }}
                                    </span>
                                    <span v-else-if="key === 'holdCardShortcut'">
                                        {{ trans('lang.hold_cart') }}
                                    </span>
                                    <span v-else-if="key === 'cancelCardShortcut'">
                                        {{ trans('lang.cancel_order') }}
                                    </span>
                                    <span v-else>
                                        {{ trans('lang.done_payment') }}
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="text-center">
                            <a :href="publicPath+'/profile-view?is_active=3'"
                               class="btn app-color">
                                {{ trans('lang.you_can_change_the_shortcut_settings_from_here') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Shortcut List Modal -->
        <!--Todo-->
        <div id="todoWrapper" class="todo-wrapper">
            <div class="todo-header-content">
                <span class="align-middle">{{ trans('lang.todo') }}</span>
                <a href="#" class="close-btn close float-right" @click.prevent="closeTodo()">
                    <i class="la la-close"></i>
                </a>
            </div>
            <user-todo-list v-if="isTodoActive" :user_profile="profile"></user-todo-list>
        </div>
        <div id="mySidenavClose"
             class="todo-overlay"
             :class="{'d-none':isTodoActive === false}"
             @click.prevent="closeTodo()">
        </div>

    </div>
</template>
<script>
    import axiosGetPost from "../../helper/axiosGetPostCommon";

    export default {
        props: ["profile", "applogo"],
        extends: axiosGetPost,
        data() {
            return {
                first_name: "",
                last_name: "",
                avatar: "",
                id: "",
                read_by: [],
                items: [],
                count: [],
                showSideNav: "",
                isTodoActive: false
            };
        },
        created() {
        },
        mounted() {
            let instance = this;
            this.readNotifi();
            this.notifyCount();
            $("#todo-list-modal").on("hidden.bs.modal", function (e) {
                instance.isTodoActive = false;
            });
        },
        methods: {
            showSideNavAction(value) {
                if (value === false && this.showSideNav == "") {
                    this.showSideNav = "";
                } else {
                    this.showSideNav = value;
                }
            },
            readNotifi() {
                axios.get(this.appUrl + "/notifications").then(response => {
                    this.items = response.data;
                });
            },
            upNofify(id) {
                axios.post(this.appUrl + "/up-notify/" + id, {
                        read_by: this.profile.id
                    })
                    .then(response => {
                    });
                location.href = this.appUrl + "/booking/" + id;
            },
            notifyCount() {
                axios.get(this.appUrl + "/count").then(response => {
                    this.count = response.data;
                });
            },
            upCount(id) {
                axios.post(this.appUrl + "/count-up/" + id, {
                        read_by: this.profile.id
                    })

                this.count = 0;
            },
            allNoti() {
                location.href = this.appUrl + "/notifications";
            },
            openTodo() {
                this.isTodoActive = true;
                if (window.innerWidth > 667) {
                    $('#todoWrapper').width('500px');
                    $('html, body').css('overflowY', 'hidden');
                } else {
                    $('#todoWrapper').width('100%');
                }
            },
            closeTodo() {
                $('#todoWrapper').width('0');
                $('html, body').css('overflowY', 'auto');
                this.isTodoActive = false;
            }
        }
    };
</script>
