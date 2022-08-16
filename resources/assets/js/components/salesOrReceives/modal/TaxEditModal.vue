<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" >{{ trans('lang.tax_settings') }}</h4>
                </div>
                <div class="col-2 text-right">
                    <button type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            @click.prevent="">
                        <i class="la la-close icon-modal-cross"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader"></pre-loader>
            <form v-else class="form-row">
                <div class="form-group col-md-6">
                    <label>{{ trans('lang.product_price_tax') }}</label>
                    <div class="form-check">
                        <input
                                class="form-check-input"
                                type="radio"
                                id="tax_excluded_yes"
                                value="1"
                                v-model="taxType"
                        />
                        <label
                                for="tax_excluded_yes"
                                class="radio-button-label"
                        >{{ trans('lang.excluded') }}</label>
                        <input
                                class="form-check-input"
                                type="radio"
                                id="tax_excluded_no"
                                value="0"
                                v-model="taxType"
                        />
                        <label
                                for="tax_excluded_no"
                                class="radio-button-label"
                        >{{ trans('lang.included') }}</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">
                        {{ trans('lang.save') }}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">
                        {{trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    import { VueTelInput } from 'vue-tel-input';
    export default {
        props: ['id', 'modalID', 'order_type', 'customerGroups', 'user'],
        extends: axiosGetPost,
        components: {VueTelInput},
        data() {
            return {
                taxType: '',
            }
        },
        created() {
           this.taxType = this.user.tax_excluded;
        },
        methods: {
            save() {
                this.inputFields = {
                    tax_excluded : this.taxType
                };
                this.postDataMethod('/edit-tax/' + this.user.id, this.inputFields);
            },
            postDataThenFunctionality(response) {
                $(this.modalID).modal('hide');
                window.location.reload();
            },
        }
    }
</script>

<style scoped>

</style>