export default {

    data(){
        return{
            isActive: false,
            hidePreLoader: true,
            selectedItemId: '',
            deleteID:'',
            deleteIndex:'',
            updateIndex:'',
        }
    },

    mounted(){

        let instance = this;

        this.$hub.$on('selectedDeletableId',function (id,index){
            instance.deleteID = id;
            instance.deleteIndex = index;
        });
    },

    methods: {
        setPreLoader(value)
        {
            this.hidePreLoader = value;
        },
        getData(getDataURL)
        {
            let instance = this;

            this.setPreLoader(false);

            axios.get(window.appConfig.appUrl+getDataURL)
                .then(response => {

                    this.setData(response.data);

                }).then(function () {

                instance.setPreLoader(true);


            }).catch(({response}) => {

                instance.setPreLoader(true);

            });
        },

        postData(postRoute, data)
        {
            let instance = this;

            this.setPreLoader(false);

            axios.post(window.appConfig.appUrl+postRoute, data).then(function(response){
                instance.setPreLoader(true);
                instance.postDataThenFunctionality(response);

            }).catch(({response}) => {
                instance.postDataCatchFunctionality(response);
                instance.setPreLoader(true);
            });
        },

        addEditAction(id)
        {
            this.selectedItemId = id;
            this.isActive = true;
        },


        deleteData(deleteDataURL,deleteIndex)
        {
            let instance = this;

            $('#confirm-delete').modal('hide');

            this.deleteActionPreLoader(false);

            axios.post(window.appConfig.appUrl+deleteDataURL, {})
                .then(function(response){

                    instance.$hub.$emit('updateDataAfterDelete',deleteIndex);

                    instance.deleteActionPreLoader(true);

                    instance.showSuccessAlert(response.data.message);

                })
                .catch(({response}) => {

                    instance.deleteActionPreLoader(true);

                });
        },

        updateData(updateDataURL,updateIndex)
        {
            let instance = this;

            $('#confirm-admin-enable-disable').modal('hide');

            this.deleteActionPreLoader(false);

            axios.post(updateDataURL, {})
                .then(function(response){

                    instance.$hub.$emit('updateDataAfterDelete',updateIndex);

                    instance.deleteActionPreLoader(true);

                    instance.showSuccessAlert(response.data.message);

                })
                .catch(({response}) => {

                    instance.deleteActionPreLoader(true);

                });
        },

        deleteActionPreLoader(value)
        {
            this.$hub.$emit("deleteActionPreLoader",value);
        },

        modalCloseAction(modalID)
        {
            let instance = this;

            $(modalID).on('hidden.bs.modal', function (e)
            {
                instance.isActive = false;

            });
        },

        /*toaster success alert Massage*/
        showSuccessAlert(message)
        {
            this.$toasted.global.success({
                message : message
            });
        },
        /*toaster error alert Massage*/
        showErrorAlert(message)
        {
            this.$toasted.global.error({
                message : message
            });
        },
    },
}