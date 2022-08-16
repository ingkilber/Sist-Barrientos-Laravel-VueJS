<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_shipping') }}</h4>
                    <h4 class="m-0" v-else="id">{{ trans('lang.add_shipping') }}</h4>
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
                    <label for="area">{{ trans('lang.area_name') }}</label>
                    <input v-validate="'required'" name="area" class="form-control" id="area" type="text" v-model="area">
                    <div class="heightError"><small class="text-danger" v-show="errors.has('area')">{{ errors.first('area') }}</small></div>
                </div>
                <div class="form-group col-md-12">
                    <label for="price">{{ trans('lang.price') }}</label>
                    <input v-validate="'required'" name="price" class="form-control" id="price" type="number" v-model="price">
                    <div class="heightError"><small class="text-danger" v-show="errors.has('price')">{{ errors.first('price') }}</small></div>
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">{{ trans('lang.save') }}</button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">{{ trans('lang.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props:['id','modalID'],
        extends: axiosGetPost,
        data(){
            return{
                area: '',
                price:'',
            }
        },

        created(){

            if(this.id)
            {
                this.getShippingData('/edit-shipping-area/'+this.id);
            }
        },

        methods : {
            save()
            {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            area: this.area,
                            price: this.price,
                        };

                        if(this.id)
                        {
                            this.postDataMethod('/edit-shipping-area/'+this.id, this.inputFields);
                        }
                        else
                        {
                            this.postDataMethod('/add-shipping-area',this.inputFields);
                        }
                    }
                });
            },
            getShippingData(route){

                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function(response){
                        instance.area = response.data.area;
                        instance.price = response.data.price;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            postDataThenFunctionality(response)
            {
                $(this.modalID).modal('hide');

                this.$hub.$emit('reloadDataTable');

            },
            setPercentageValue(amount){
                this.percentage = amount;
            }


        },
    }
</script>