<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        I'm an example component.
                    </div>
                </div>
            </div>
        </div>
        <button class="btn btn-default" @click="showAlert">Click to use vue-alert</button>
        <br>
    </div>
</template>

<script>
    export default {
        methods: {
            showAlert () {
                this.$alert.show({
                    message: 'Clicked the button!'
                })
            }
        },
        mounted() {

        }
    }
</script>
