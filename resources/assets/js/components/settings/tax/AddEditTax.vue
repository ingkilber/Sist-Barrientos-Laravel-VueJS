<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_tax') }}</h4>
                    <h4 class="m-0" v-else="id">{{ trans('lang.add_tax') }}</h4>
                </div>
                <div class="col-2 text-right">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                        <i class="la la-close icon-modal-cross"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader" class="small-loader-container"></pre-loader>
            <form class="form-row" v-else>
                <div class="form-group col-md-12">
                    <label for="name">{{ trans('lang.name') }}</label>
                    <input v-validate="'required'" name="name" class="form-control" id="name" type="text"
                           v-model="name">
                    <div class="heightError"><small class="text-danger"
                                                    v-show="errors.has('name')">{{ errors.first('name') }}</small></div>
                </div>
                <div class="form-group margin-top col-md-6">
                    <label for="percentage">{{ trans('lang.percentage') }}</label>
                    <common-input v-validate="'required'"
                                  name="percentage"
                                  id="percentage"
                                  :inputValue="decimalFormat(percentage)"
                                  @input="setPercentageValue"
                                  :disabled="taxUsed">
                    </common-input>
                    <div class="heightError"><small class="text-danger" v-show="errors.has('percentage')">{{
                            errors.first('percentage')
                        }}</small></div>
                </div>
                <div class="form-group margin-top offset-md-1 col-md-5">
                    <label>{{ trans('lang.is_default') }}</label><br>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" name="isDefault" class="custom-control-input" id="default" checked="checked"
                               value="1" v-model="isDefault">
                        <label class="custom-control-label" for="default">{{ trans('lang.yes') }}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" name="isDefault" :disabled="tempIsDefault==1" class="custom-control-input"
                               id="not_default" value="0" v-model="isDefault">
                        <label class="custom-control-label" for="not_default">{{ trans('lang.no') }}</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">{{
                            trans('lang.save')
                        }}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">
                        {{ trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>

import axiosGetPost from '../../../helper/axiosGetPostCommon';

export default {

    props: ['id', 'modalID'],
    extends: axiosGetPost,
    data() {
        return {
            name: '',
            percentage: '',
            isDefault: 0,
            taxUsed: false,
            tempIsDefault: '',
        }
    },

    created() {
        if (this.id) {
            this.getTaxData('/edit-tax/' + this.id);
        }
    },

    methods: {
        save() {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.inputFields = {
                        name: this.name,
                        percentage: this.percentage,
                        is_default: this.isDefault,
                    };

                    if (this.id) {
                        this.postDataMethod('/edit-tax/' + this.id, this.inputFields);
                    } else {
                        this.postDataMethod('/add-tax', this.inputFields);
                    }
                }
            });
        },
        getTaxData(route) {

            let instance = this;
            this.setPreLoader(false);
            this.axiosGet(route,
                function (response) {
                    instance.name = response.data.name;
                    instance.percentage = response.data.percentage;
                    instance.isDefault = response.data.is_default;
                    instance.taxUsed = response.data.used;
                    instance.tempIsDefault = response.data.is_default;
                    instance.setPreLoader(true);
                },
                function () {
                    instance.setPreLoader(true);
                },
            );
        },
        postDataThenFunctionality() {
            $(this.modalID).modal('hide');

            this.$hub.$emit('reloadDataTable');

        },
        setPercentageValue(amount) {
            this.percentage = amount;
        }
    },
}
</script>