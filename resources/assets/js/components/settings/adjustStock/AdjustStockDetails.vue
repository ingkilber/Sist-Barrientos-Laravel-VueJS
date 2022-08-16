<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_adjust_stock') }}</h4>
                    <h4 class="m-0" v-else>{{ trans('lang.add_adjust_stock') }}</h4>
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
                <span v-if="alertMessage.length>0" class="alertBranch">
                    <div class="alert alert-warning alertBranch" role="alert">
                        {{alertMessage}}
                    </div>
                </span>
                <div class="form-group col-md-12">
                    <label for="type-name">{{ trans('lang.title') }}</label>
                    <input type="text"
                           id="type-name"
                           class="form-control"
                           name="title"
                           v-validate="'required'"
                           v-model="title">
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('title')">
                            {{ errors.first('title') }}
                        </small>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">
                        {{ trans('lang.save') }}
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

        props: ['id', 'modalID', 'allAdjustStockTypes'],
        extends: axiosGetPost,
        data() {
            return {
                title: '',
                alertMessage: '',
            }
        },
        created() {
            if (this.id) {
                this.getAdjustStockData('/adjust-stock-details/' + this.id);
            }
        },
        methods: {
            save() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            title: this.title,
                        };
                        if (this.id) {
                            this.postDataMethod('/edit-adjust-stock/' + this.id, this.inputFields);
                        }
                        else {
                            this.postDataMethod('/add-adjust-stock', this.inputFields);
                        }
                    }
                });
            },
            getAdjustStockData(route) {
                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function (response) {
                        instance.title = response.data.title;
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
            postDataCatchFunctionality(error) {
                this.showErrorAlert(error.data.message);
            },
        },
    }
</script>