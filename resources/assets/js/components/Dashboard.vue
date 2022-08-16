<template>
    <div class="main-layout-wrapper">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col s12 m6 l6 xl3">
                    <div class="card">
                        <div class="card-content">
                            <div class="dashboard-card-parent">
                                <div class="dashboard-card-child">
                                    <div class="deep-purple accent-2 dashboard-top-row-parent">
                                        <i class="material-icons dp48 dashboard-top-row-child">Assignment</i>
                                    </div>
                                </div>
                                <div class="dashboard-card-child2">
                                    <h5 class="dashboard-card-child">{{totalAllBooking}}</h5>
                                    <small class="grey-text">{{ trans('lang.total_booking_for_next_30_days') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div class="card">
                        <div class="card-content">
                            <div class="dashboard-card-parent">
                                <div class="dashboard-card-child">
                                    <div class="orange lighten-2 dashboard-top-row-parent">
                                        <i class="material-icons dp48 dashboard-top-row-child">local_mall</i>
                                    </div>
                                </div>
                                <div class="dashboard-card-child2">
                                    <h5 class="dashboard-card-child">{{totalBooking}}</h5>
                                    <small class="grey-text">{{ trans('lang.confirmed_booking_for_next_30_days') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div class="card">
                        <div class="card-content">
                            <div class="dashboard-card-parent">
                                <div class="dashboard-card-child">
                                    <div class="cyan lighten-1 dashboard-top-row-parent">
                                        <i class="material-icons dp48 dashboard-top-row-child">stars</i>
                                    </div>
                                </div>
                                <div class="dashboard-card-child2">
                                    <h5 class="dashboard-card-child">{{todaysBooking}}</h5>
                                    <small class="grey-text">{{ trans('lang.today_total_booking') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 l6 xl3">
                    <div class="card">
                        <div class="card-content">
                            <div class="dashboard-card-parent">
                                <div class="dashboard-card-child">
                                    <div class="red accent-3 dashboard-top-row-parent">
                                        <i class="material-icons dp48 dashboard-top-row-child">content_paste</i>
                                    </div>
                                </div>
                                <div class="dashboard-card-child2">
                                    <h5 class="dashboard-card-child">{{todaysBookingPending}}</h5>
                                    <small class="grey-text">{{ trans('lang.today_pending_booking') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col s12 m12 l12 xl9">
                    <div class="card">

                        <div class="card-content">
                            <h5>{{ trans('lang.pos_overview') }}</h5>
                            <small class="grey-text">{{ trans('lang.last_12_months') }}</small>
                            <br>
                            <div class="chartjs-loader" v-if="showLineChartLoader">
                                <div class="row margin-fix">
                                    <div class="col s4 offset-s4 center-align">
                                        <circle-loader></circle-loader>
                                    </div>
                                </div>
                            </div>
                            <line-chart :width="400" :height="405"  v-on:setChartLoader="getLineChartLoader"></line-chart>
                        </div>
                    </div>
                </div>
                <div class="col s12 m12 l12 xl3">
                    <div class="card">
                        <div class="card-content">
                            <h5>{{ trans('lang.booking_type') }}</h5>
                            <small class="grey-text">{{ trans('lang.total_bookings_of_different_services') }}</small>
                            <br>
                            <br>
                            <div class="chartjs-loader" v-if="showDoughnutChartLoader">
                                <div class="row margin-fix">
                                    <div class="col s4 offset-s4 center-align">
                                        <circle-loader></circle-loader>
                                    </div>
                                </div>
                            </div>
                            <doughnut-chart :width="400" :height="382" v-on:setChartLoader="getDoughnutChartLoader"></doughnut-chart>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col s12 m12 l4 xl4">
                    <div class="card cyan lighten-1">
                        <div class="card-content">
                            <div class="white-text">
                                <h5>{{ trans('lang.total_booking') }}</h5>
                                <h6>{{ trans('lang.this_month') }}</h6>
                                <h3>{{curMonthTotBooking}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m12 l4 xl4">
                    <div class="card deep-purple accent-2">
                        <div class="card-content">
                            <div class="white-text">
                                <h5>{{ trans('lang.total_booking') }}</h5>
                                <h6>{{ trans('lang.last_month') }}</h6>
                                <h3>{{lastMonthTotBooking}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m12 l4 xl4">
                    <div class="card blue accent-3">
                        <div class="card-content">
                            <div class="white-text">
                                <h5>{{ trans('lang.total_booking') }}</h5>
                                <h6>{{ trans('lang.till_now') }}</h6>
                                <h3>{{allTimeTotBooking}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
