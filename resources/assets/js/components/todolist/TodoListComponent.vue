<template>
    <div class="todo-layout-content">
        <div class="form-row">
            <div class="col">
                <span class="align-middle">{{ trans('lang.task_list') }}</span>
            </div>
            <div class="col">
                <div class="sort-by">
                    <label class="mr-2" for="sortBy">{{ trans('lang.sort_by') }}:</label>
                    <select
                        class="custom-select my-1 mr-sm-2"
                        id="sortBy"
                        v-model="sortValue"
                        @change="getTodoLists"
                    >
                        <option :value="'most_recent'" selected>{{ trans('lang.most_recent') }}</option>
                        <option :value="'due_date'">{{ trans('lang.due_date') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-12">
                <span class="horizontal-line"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="col">
                <div class="input-group mb-3 task-input-group">
                    <input
                        type="text"
                        ref="taskInput"
                        class="form-control"
                        v-model="newTask"
                        :readonly="isTaskInputDisabled"
                        @keyup.enter="addTask"
                        :placeholder="trans('lang.add_new_task')"
                    />
                    <div class="input-group-append">
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            :disabled="isTaskInputDisabled"
                            @click.prevent="addTask"
                        >
                            <i class="la la-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <pre-loader class="small-loader-container" v-if="!incompleTaskPreLoader"></pre-loader>
        <span v-else>
            <div class="form-row">
                <div class="col">
                    <div class="incomplete-task-list">
                        <div class="single-incomplete-task" v-for="(task, index) in tasksList">
                            <div class="row pb-2" v-if="!parseInt(task.completed)">
                                <div class="col-9">
                                    <div class="custom-control custom-checkbox">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            :id="'task'+index"
                                            @click.prevent="completeTask(task, index)"
                                        />
                                        <label :for="'task'+index" class="custom-control-label"></label>
                                        <span>{{ task.title }}</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="row">
                                        <div class="col-9 pl-0">
                                            <datepicker class="cursor-pointer"
                                                :id="'taskDueDate'+index"
                                                :format="customFormatter"
                                                :placeholder="trans('lang.add_due_date')"
                                                input-class="add-due-date"
                                                v-model="task.due_date"
                                                @input="setDueDate(task)"
                                            ></datepicker>
                                        </div>
                                        <div class="col-2 text-right pl-1 pr-1 delete-icon-wrapper">
                                            <a
                                                href
                                                class="delete"
                                                @click.prevent="removeTask(task,index)"
                                            >
                                                <i class="la la-times"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="text-center"
                            v-if="tasksList.length === 0"
                        >{{ trans('lang.no_task_found') }}</div>
                    </div>
                </div>
            </div>
            <div class="form-row" v-if="tasksList.filter(task => task.completed).length">
                <div class="hide-completed-task mx-auto">
                    <button class="btn app-color my-2" @click.prevent="showOrHideCompletedTask">
                        <span v-if="!viewCompletedTask">{{ trans('lang.show_completed_task') }}</span>
                        <span v-else>{{ trans('lang.hide_completed_task') }}</span>
                    </button>
                </div>
            </div>
            <div class="form-row">
                <div class="col">
                    <div class="complete-task-list" v-if="viewCompletedTask">
                        <div v-for="(task, index) in tasksList">
                            <div class="single-complete-task" v-if="parseInt(task.completed)">
                                <div class="row pb-2">
                                    <div class="col">
                                        <div class="custom-control custom-checkbox">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                :id="'task'+index"
                                                checked
                                                @click.prevent="completeTask(task, index)"
                                            />
                                            <label class="custom-control-label" :for="'task'+index"></label>
                                            <span>{{ task.title }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </span>
    </div>
</template>

<script>
import Datepicker from "vuejs-datepicker";
import axiosGetPost from "../../helper/axiosGetPostCommon";

export default {
    props: ["user_profile"],
    extends: axiosGetPost,
    components: {
        Datepicker
    },
    data() {
        return {
            newTask: "",
            selectedDate: [],
            tasksList: [],
            viewCompletedTask: false,
            incompleTaskPreLoader: false,
            sortValue: "most_recent",
            isTaskInputDisabled: false
        };
    },
    created() {
        this.getTodoLists();
    },
    mounted() {
        let instance = this;
        $("#todo-list-modal").on("hidden.bs.modal", function(e) {
            instance.incompleTaskPreLoader = false;
        });
        this.$refs.taskInput.focus();
    },
    methods: {
        addTask() {
            let instance = this;
            if (this.newTask) {
                let data = {
                    title: this.newTask,
                    due_date: "",
                    completed_date: "",
                    completed: false
                };
                instance.incompleTaskPreLoader = false;
                instance.isTaskInputDisabled = true;
                instance.axiosGETorPOST(
                    {
                        url: "/todo/store",
                        postData: data
                    },
                    (success, responseData) => {
                        if (success) {
                            instance.newTask = "";
                            instance.isTaskInputDisabled = false;
                            instance.getTodoLists();
                            this.$refs.taskInput.focus();
                        }
                    }
                );
            }
        },
        getTodoLists() {
            let instance = this;
            instance.incompleTaskPreLoader = false;
            instance.axiosGETorPOST(
                {
                    url: "/todo/list",
                    postData: {
                        sortBy: this.sortValue
                    }
                },
                (success, responseData) => {
                    if (success) {
                        instance.tasksList = responseData.data;
                        instance.incompleTaskPreLoader = true;
                    }
                }
            );
        },
        completeTask(task, index) {
            let instance = this;
            task.completed_date = moment().format(this.dateFormat);
            instance.incompleTaskPreLoader = false;
            instance.axiosGETorPOST(
                {
                    url: "/todo/update-status",
                    postData: task
                },
                (success, responseData) => {
                    if (success) {
                        instance.getTodoLists();
                    }
                }
            );
        },
        setDueDate(task) {
            let instance = this;
            instance.incompleTaskPreLoader = false;
            instance.axiosGETorPOST(
                {
                    url: "/todo/set-due-date",
                    postData: task
                },
                (success, responseData) => {
                    if (success) {
                        instance.getTodoLists();
                    }
                }
            );
        },
        removeTask(task, index) {
            let instance = this;
            instance.incompleTaskPreLoader = false;
            instance.axiosGETorPOST(
                {
                    url: "/todo/delete",
                    postData: task
                },
                (success, responseData) => {
                    if (success) {
                        instance.getTodoLists();
                    }
                }
            );
        },
        showOrHideCompletedTask() {
            this.viewCompletedTask = !this.viewCompletedTask;
        },
        customFormatter(date) {
            return moment(date).format(this.dateFormat);
        }
    }
};
</script>