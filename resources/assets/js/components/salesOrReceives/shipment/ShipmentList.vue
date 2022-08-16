<template>
    <div>
        <div v-if="!hasData(tableOptions)">
            <pre-loader/>
        </div>
        <div v-else>
            <div class="main-layout-card-header-with-button">
                <div class="main-layout-card-content-wrapper">
                    <div class="main-layout-card-header-contents">
                        <h5 class="m-0">{{ trans('lang.shipment_list') }}</h5>
                    </div>
                </div>
            </div>
            <datatable-component class="main-layout-card-content" :options="tableOptions"/>
    </div>
    </div>
</template>

<script>
    import axiosGetPost from "../../../helper/axiosGetPostCommon";

    export default {
        props: ["branch_id"],
        extends: axiosGetPost,
        data() {
            return {
                isActive: false,
                selectedItemId: "",
                hidePreLoader: false,
                tableOptions: {},
                dueModalPreloader: true,
                hasData: value => {
                    return !_.isEmpty(value) ? true : false;
                }
            };
        },
        created() {
            this.getShippingData();
        },
        mounted() {
            let instance = this;

            this.$hub.$on("setShipmentStatus", function(id, status) {
                instance.setShipmentStatus(id, status);
            });
        },
        methods: {
            getShippingData() {
                let instance = this;

                    instance.tableOptions = {
                        tableName: "shipping_information",
                        columns: [
                            {
                                title: "lang.invoice_id",
                                key: "invoice_id",
                                type: "text",
                                sortable: true
                            },
                            {
                                title: "lang.shipping_area",
                                key: "area_name",
                                type: "text",
                                sortable: true
                            },
                            {
                                title: "lang.address",
                                key: "shipping_address",
                                type: "text",
                                sortable: true
                            },
                            {
                                title: "lang.shipping_cost",
                                key: "price",
                                type: "text",
                                sortable: true
                            },
                            {
                                title: "lang.branch",
                                key: "branch_name",
                                type: "text",
                                sortable: true
                            },
                            {
                                title: "lang.status",
                                key: "status",
                                type: "text",
                                sortable: false,
                            },
                            {
                                title: "lang.action",
                                key: "action",
                                type: "component",
                                componentName: "shipment-list-action-component"
                            }

                        ],
                        source: "/sales-shipment-data/" + instance.branch_id,
                        search: true,
                        right_align: ['action'],
                        formatting : ['price'],
                        sortedBy: "id",
                        sortedType: "DESC",


                    };

            },

            setShipmentStatus(id, status) {
                this.postDataMethod('/shipping-order-status/'+id+'/'+ status);
            },
            postDataThenFunctionality(response)
            {
                this.$hub.$emit('reloadDataTable');
            },
        }
    };
</script>