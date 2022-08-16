<template>
    <div>
        <nav aria-label="breadcrumb" class="position-fixed navbar-dashboard">
            <ol class="breadcrumb bg-transparent m-0">
                <li class="breadcrumb-item">
                    <span>{{trans('lang.supplier_details')}}</span>
                    <span><a href="#" @click="goBack">({{trans('lang.back_page')}})</a></span>
                </li>
            </ol>
        </nav>
        <div class="main-layout-wrapper">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-12 col-md-4 col-lg-4 col-xl-3 pr-md-0">
                        <div class="main-layout-card mb-sm-2">
                            <div class="main-layout-card-header">
                                <div class="main-layout-card-content-wrapper">
                                    <div class="main-layout-card-header-contents">
                                        <h5 class="bluish-text m-0">{{ trans('lang.supplier_details') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="main-layout-card-content pb-0 mb-0">
                                <div class="main-layout-card-header-contents">
                                    <div class="text-center profile-image-container position-relative">
                                        <div class="input-file-wrap">
                                            <input type="file" class="file-input input-position" name="avatar" @change="onImageChange"
                                                   accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <img class="img-fluid profile-image rounded-circle avatar mb-4" :src="avatar" alt=""
                                         width="50%" v-if="avatar">
                                    <img class="img-fluid profile-image rounded-circle avatar mb-4"
                                         :src="publicPath+'/uploads/profile/'+supplier.avatar" alt="" width="50%" v-else>
                                </div>
                            </div>
                            <div class="main-layout-card-content pt-0 mt-0">
                                <div class="text-center user-name-text">
                                    <div class="card text-center">
                                        <div class="card-header">
                                            <h5>{{ trans('lang.supplier_info') }}</h5>
                                        </div>
                                        <div class="contact-person-info">
                                            <p class="cursor-text" v-if="supplier.fullName">
                                                <i class="fa fa-check"></i> {{ supplier.fullName }}
                                            </p>
                                            <p class="cursor-text" v-if="supplier.company">
                                                <i class="la la-building"></i> {{supplier.company }}
                                            </p>
                                            <p class="cursor-text" v-if="supplier.email">
                                                <i class="la la-envelope"></i> {{supplier.email }}
                                            </p>
                                            <p class="cursor-text" v-if="supplier.phone_number">
                                                <i class="la la-phone"></i> {{supplier.phone_number }}
                                            </p>
                                            <p class="cursor-text" v-if="supplier.customer_code">
                                                <i class="la la-barcode"></i> {{supplier.customer_code }}
                                            </p>
                                            <p class="cursor-text" v-if="supplier.customer_group_title">
                                                <i class="la la-group"></i> {{supplier.customer_group_title }}
                                            </p>
                                            <p class="cursor-text" v-if="supplier.tin_number">
                                                <i class="fa fa-check"></i> {{supplier.tin_number }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-8 col-xl-9">
                        <div class="main-layout-card">
                            <div class="main-layout-card-header">
                                <div class="main-layout-card-content-wrapper">
                                    <div class="main-layout-card-header-contents">
                                        <h5 class="bluish-text m-0">{{ trans('lang.supplier_delivery_records') }}</h5>
                                    </div>
                                    <div class="main-layout-card-header-contents text-right d-flex justify-content-end">
                                        <div class="p-1">
                                            <common-submit-button :buttonLoader="buttonLoader"
                                                                  :isDisabled="isDisabled"
                                                                  buttonText="export"
                                                                  v-on:submit="exportStatus">
                                            </common-submit-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="main-layout-card-content">
                                <datatable-component class="main-layout-card-content"
                                                     :options="tableOptions"
                                                     :exportData="exportToVue"
                                                     exportFileName="supplier_record"
                                                     @resetStatus="resetExportValue">
                                </datatable-component>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="due-amount-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <cart-due-payment
                        class="modal-content"
                        v-if="isActive"
                        :rowdata="selectedItemId"
                        :orderType="order_type"
                        :modalID="modalID"
                        :modalTitle="trans('lang.due_total')"
                        @cartItemsToStore = "cartItemsToStore">
                </cart-due-payment>
            </div>
        </div>
    </div>
</template>
<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    export default {

        props: ["supplier", "tab_name", "route_name"],

        extends: axiosGetPost,

        data() {
            return {
                profile:[],
                avatar:'',
                isActive:false,
                selectedItemId: '',
                order_type : '',
                modalID: '#due-amount-edit-modal',
                tabName: '',
                routeName: '',
                isActiveAttributeModal:false,
                hidePreLoader: false,
                tableOptions: {
                    tableName: 'products',
                    columns: [
                        // {title: 'lang.receiving_id',        key: 'id',              type: 'clickable_link', source: '/reports/receiving', uniquefield: 'id', sortable: true},
                        {title: 'lang.receiving_id',        key: 'id',              type: 'text', sortable: true},
                        {title: 'lang.supplier_date',       key: 'date',            type: 'text', sortable: true},
                        {title: 'lang.received_branch',     key: 'received_branch', type: 'text', sortable: false},
                        // {title: 'lang.received_by',         key: 'received_by',     type: 'clickable_link', source: '/user', uniquefield: 'user_id', sortable: true},
                        {title: 'lang.received_by',         key: 'received_by',     type: 'text', sortable: true},
                        {title: 'lang.item_received',       key: 'item_received',   type: 'text', sortable: false},
                        {title: 'lang.subtotal',            key: 'sub_total',       type: 'text', sortable: false},
                        {title: 'lang.tax',                 key: 'tax',             type: 'text', sortable: false},
                        {title: 'lang.discount',            key: 'discount',        type: 'text', sortable: false},
                        {title: 'lang.total',               key: 'total',           type: 'text', sortable: false},
                        {title: 'lang.due', key: 'due_amount', type: 'text', sortable: false},
                        {
                            title: 'lang.action',
                            type: 'component',
                            componentName: 'sales-report-action-component'
                        }
                    ],
                    source: '/supplier-delivery-report/'+this.supplier.id,
                    search: true,
                    formatting : ['total','sub_total','tax','discount','item_received', 'due_amount'],
                    dateFormatting : ['date'],
                    right_align: ['sub_total','item_received','total', 'due_amount'],
                    summary: true,
                    sortedBy:'id',
                    sortedType:'DESC',
                    summation: ['item_received','sub_total','total', 'due_amount'],
                    summationKey: ['id'],
                    filters: [
                        {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
                        {title:'lang.payment_type', type:'dropdown',key:'payment_type',options:[
                                {text: 'lang.all', value: 'all', selected: true},
                                {text: 'lang.paid', value: 'paid'},
                                {text: 'lang.due', value: 'due'},
                            ]
                        }
                    ]
                },
                buttonLoader:false,
                isDisabled:false,
                exportToVue: false,
            }
        },

        mounted(){

            let instance = this;

            this.modalCloseAction(this.modalID);

            $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e)
            {
                instance.isActiveAttributeModal = false;
                $('body').addClass('modal-open');
            });

            this.tabName = this.tab_name;
            this.routeName = this.route_name;

            this.$hub.$on('viewSalesReportEdit', function (rowdata) {
                instance.addEditAction(rowdata);
            });


        },

        methods: {
            cartItemsToStore(cartItemsToStore){
                let instance = this;
                instance.hideSalesReturnsPreLoader = false;
                cartItemsToStore.paymentType = 'credit';

                instance.axiosGETorPOST(
                    {
                        url: '/save-due-amount',
                        postData: {cartItemsToStore},
                    },
                    (success, responseData) => {

                        if (success) //response after then function
                        {
                            instance.hideSalesReturnsPreLoader = true;
                            instance.showSuccessAlert(responseData.message);

                            $(`${this.modalID}`).modal('hide');
                            instance.$hub.$emit('reloadDataTable');

                        } else {
                            instance.hideSalesReturnsPreLoader = true;
                            $(`${this.modalID}`).modal('hide');
                        }
                    }
                );
            },
            onImageChange() {
                // Reference to the DOM input element
                let input = event.target;
                // Ensure that you have a file before attempting to read it
                if (input.files && input.files[0]) {
                    // create a new FileReader to read this image and convert to base64 format
                    var reader = new FileReader();
                    // Define a callback function to run, when FileReader finishes its job
                    reader.onload = (e) => {
                        // Note: arrow function used here, so that “this.imageData” refers to the imageData of Vue component
                        // Read image as base64 and set to imageData
                        this.avatar = e.target.result;
                        this.updateAvatar(e.target.result);
                    }
                    // Start the reader job - read file as a data url (base64 format)
                    reader.readAsDataURL(input.files[0]);
                    //send to controller this.avatar

                }
                else {
                    this.supplier.avatar = '';
                }

            },


            getActiveAttributeModal(isActive)
            {
                this.isActiveAttributeModal = isActive;
            },
            updateAvatar(data) {
                let instance = this;
                instance.axiosPost('/update-supplier-avatar/' + this.supplier.id,
                    {
                        avatar: data,
                    },
                    function (response) {
                        window.location.reload();
                    },
                    function (error) {

                    }
                );
            },
            exportStatus() {
                this.exportToVue = true;
                this.buttonLoader = true;
                this.isDisabled = true;
            },
            resetExportValue(value) {
                this.exportToVue = value;
                this.buttonLoader = false;
                this.isDisabled = false;
            },
            goBack() {
                let instance = this;
                instance.redirect(`/${this.routeName}?tab_name=${this.tabName}&&route_name=${this.routeName}`);
            }

        }
    }

</script>