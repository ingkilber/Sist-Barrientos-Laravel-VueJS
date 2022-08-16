<template>
    <div>
        <div class="fixed-action-btn horizontal action-com-button">
            <a href="#" @click.prevent="">
                <i class="material-icons purple-grey-text text-darken-1 icon-vertically-middle">more_vert</i>
            </a>
            <ul>
                <li @click.prevent="selectedDeletableId(rowData.id,rowIndex)" v-if="deleteAble(rowData.used)">
                    <a class="btn-floating waves-effect materialize-red modal-trigger tooltipped" href="#confirm-delete" @click.prevent="renderTooltip('remove')"
                       data-position="bottom" data-delay="50" :data-tooltip="trans('lang.delete')">
                        <i class="material-icons white-text">delete_forever</i>
                    </a>
                </li>
                <li @click.prevent="setActive(4,''),setId(rowData.id)">
                    <a class="btn-floating waves-effect cyan lighten-1 modal-trigger tooltipped" href="#service-modal"
                       data-position="bottom" data-delay="50" :data-tooltip="trans('lang.setting')" @click.prevent="renderTooltip('remove')">
                        <i class="material-icons white-text">settings</i>
                    </a>
                </li>
                <li @click.prevent="setActive(2,''),setId(rowData.id)">
                    <a class="btn-floating waves-effect bluish modal-trigger tooltipped" href="#service-modal"
                       data-position="bottom" data-delay="50" :data-tooltip="trans('lang.edit')" @click.prevent="renderTooltip('remove')">
                        <i class="material-icons white-text">mode_edit</i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
export default {
    props: ['rowData','rowIndex'],
    data(){
        return{

        }
    },
    mounted(){
        this.renderTooltip('');
        let instance = this;
        $('#confirm-delete-'+this.rowIndex).modal({
                dismissible: true, // Modal can be dismissed by clicking outside of the modal
                opacity: .5, // Opacity of modal background
                inDuration: 300, // Transition in duration
                outDuration: 200, // Transition out duration
                startingTop: '0', // Starting top style attribute
                endingTop: '0', // Ending top style attribute
                complete: function()
                {
                    instance.renderTooltip('');
                },
            }
        );
    },
    methods:{
        setActive(newActiveComponent,action){
            this.$hub.$emit("setActiveService", newActiveComponent,action);
        },
        setId(id){
            this.$hub.$emit("serviceSetId",id);
        },
        renderTooltip(action){
            this.$hub.$emit('renderTooltip',action);
        },
        setPreloader(type,activity){
            this.$emit('setPreloader',type,activity);
        },
        deleteAble(used){
            if(used !== 0) return false;
            else return true;
        },
        selectedDeletableId(id,index)
        {
            this.$hub.$emit('selectedDeletableId',id,index);
        },

    }
}
</script>
