// import axios from "axios";

const {format} = require('number-currency-format');

export default {
    data() {
        return {
            inputFields: [],
            hidePreLoader: true,
            isActive: false,
            selectedItemId: '',
            deleteID: '',
            deleteIndex: '',
            updateIndex: '',
            accounting: '',
            isShortcutAbleKey: true,
            isConnected: true,
        }
    },
    mounted() {
        window.addEventListener('online', () => {
            this.isConnected = true;
        });
        window.addEventListener('offline', () => {
            this.isConnected = false;
        });
        let instance = this;
        this.$hub.$on('selectedDeletableId', function (id, index) {
            instance.deleteID = id;
            instance.deleteIndex = index;
        });
    },
    methods: {
        axiosGet(url, onSuccess, onError) {
            axios.get(window.appConfig.appUrl + url)
                .then(function (response) {
                    if (onSuccess) onSuccess(response);

                }.bind(this))
                .catch(function (error) {
                    if (onError) onError(error);
                })
        },
        axiosPost(url, postData, onSuccess, onError) {
            const headers = {
                'Content-Type': 'application/json',
            }

            axios.post(window.appConfig.appUrl + url, postData, {
                headers: headers
            }).then(function (response) {
                if (onSuccess) onSuccess(response);
                this.showSuccessAlert(response.data.message);

            }.bind(this))
                .catch(({response}) => {
                    if (onError) onError(response);
                    this.showErrorAlert(response.data.message);
                });
        },
        loginAxiosPost(url, postData, onSuccess, onError) {
            axios.post(window.appConfig.appUrl + url, postData)
                .then(function (response) {
                    if (onSuccess) onSuccess(response);

                }.bind(this))
                .catch(({response}) => {
                    if (onError) onError(response);
                });
        },
        postDataMethod(route, fields) {
            let instance = this;
            instance.setPreLoader(false);
            instance.axiosPost(route, fields,
                function (response) {
                    instance.setPreLoader(true);
                    instance.postDataThenFunctionality(response);
                    instance.$hub.$emit('reloadDataTable');
                },
                function (error) {
                    instance.setPreLoader(true);
                    instance.postDataCatchFunctionality(error);
                    instance.$hub.$emit('reloadDataTable');
                });
        },
        loginPostMethod(route, fields) {
            let instance = this;
            instance.setPreLoader(false);
            instance.loginAxiosPost(route, fields,
                function (response) {
                    instance.setPreLoader(true);
                    instance.loginPostSucces(response);
                },
                function (error) {
                    instance.setPreLoader(true);
                    instance.loginPostError(error);

                });
        },
        deleteDataMethod(deleteDataURL, deleteIndex) {
            let instance = this;
            $('#confirm-delete').modal('hide');
            this.deleteActionPreLoader(false);
            axios.post(window.appConfig.appUrl + deleteDataURL, {})
                .then(function (response) {
                    instance.deleteActionPreLoader(true);
                    instance.showSuccessAlert(response.data.message);
                    instance.$hub.$emit('reloadDataTable');
                })
                .catch(({response}) => {
                    instance.showErrorAlert(response.data.message);
                    instance.deleteActionPreLoader(true);
                    instance.showErrorAlert(response.data.message);

                });
        },
        deleteActionPreLoader(value) {
            this.$hub.$emit("deleteActionPreLoader", value);
        },
        redirect(route) {
            location.href = window.appConfig.appUrl + route;
        },
        axiosGETorPOST(options, callback) {
            let axiosCall;

            if (options.postData) //if post method
            {
                axiosCall = axios.post(window.appConfig.appUrl + options.url, options.postData)
            } else //else get method
            {
                axiosCall = axios.get(window.appConfig.appUrl + options.url)
            }
            axiosCall.then(response => {
                if (callback) callback(true, response.data); //returns response data

            }).catch(({response}) => {
                if (callback) callback(false, response.data); //returns error response data
            });
        },
        modalCloseAction(modalID) {
            let instance = this;
            $(modalID).on('hidden.bs.modal', function (e) {
                instance.isActive = false;
            });

        },
        /*toaster success alert Massage*/
        showSuccessAlert(message) {
            this.$toasted.global.success({
                message: message,
            });
        },
        /*toaster error alert Massage*/
        showErrorAlert(message) {
            this.$toasted.global.error({
                message: message,
            });
        },
        showOfflineAlert(message) {
            this.$toasted.global.offlineToast({
                message: message,
            });
        },
        showWarningAlert(message) {
            this.$toasted.global.warningToast({
                message: message,
            });
        },
        showWarningAlertForOutOfStock(message) {
            this.$toasted.global.outOfStockToast({
                message: message,
            });
        },
        setPreLoader(value) {
            let instance = this;
            instance.hidePreLoader = value;
        },
        addEditAction(id) {
            this.selectedItemId = id;
            this.isActive = true;
        },
        //Excel Files data converted into json objects
        ExcelToJSON(file, callback) {
            let reader = new FileReader(),
                result;
            reader.onload = function (e) {
                let data = e.target.result;
                let workbook = XLSX.read(data, {
                    type: 'binary'
                });
                workbook.SheetNames.forEach(function (sheetName) {
                    let XL_row_object = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {raw: true, defval: null});
                    result = JSON.stringify(XL_row_object);
                });
                let result = JSON.parse("[" + result + "]");
                let parsedData = [];
                //removing unwanted empty cells after the data from excel
                _.forEach(result, function (value) {
                    _.forIn(value, function (value, key) {
                        if (_.compact(Object.values(value)).length == 0) {
                            parsedData.push(key);
                        }
                    });
                });
                for (let i = 0; i < result.length; i++) {
                    result[0].splice(parsedData[i], parsedData.length);
                }
                if (callback && result) callback(result[0]);
            };
            reader.onerror = function (error) {
            };
            reader.readAsBinaryString(file[0]);
        },
        numberFormat(number) {
            let position, spacing = false, thousandSeparator, decimalSeparator = false;
            if (this.currencyPosition === 'left') {
                position = 'LEFT';

            } else if (this.currencyPosition === 'right') {
                position = "RIGHT";

            } else if (this.currencyPosition === 'left-space') {
                position = "LEFT";
                spacing = true;
            } else {
                position = "RIGHT";
                spacing = true;
            }
            if (this.thousandSeparator === "space") {
                thousandSeparator = " "
            } else {
                thousandSeparator = this.thousandSeparator;
            }
            if (this.numDec == 0) {
                decimalSeparator = "";
            } else {
                decimalSeparator = this.decimalSeparator;
            }
            return format(number, {
                currency: this.currencySymbol,
                spacing: spacing,
                currencyPosition: position,
                thousandSeparator: thousandSeparator,
                decimalSeparator: decimalSeparator,
                decimalsDigits: this.numDec,
            });
        },
        percentFormatting(number) {

            let data, spacing = false, thousandSeparator, decimalSeparator = false;
            if (this.thousandSeparator === "space") {
                thousandSeparator = " "
            } else {
                thousandSeparator = this.thousandSeparator;
            }
            if (this.numDec == 0) {
                decimalSeparator = "";
            } else {
                decimalSeparator = this.decimalSeparator;
            }
            data = format(number, {
                spacing: spacing,
                thousandSeparator: thousandSeparator,
                decimalSeparator: decimalSeparator,
                decimalsDigits: this.numDec,
            });

            return data + '%';
        },
        numberFormatting(number) {

            let position, spacing = false, thousandSeparator, decimalSeparator = false;

            if (this.thousandSeparator === "space") {
                thousandSeparator = " "
            } else {
                thousandSeparator = this.thousandSeparator;
            }
            if (this.numDec == 0) {
                decimalSeparator = "";
            } else {
                decimalSeparator = this.decimalSeparator;
            }
            return format(number, {
                spacing: spacing,
                currencyPosition: position,
                thousandSeparator: thousandSeparator,
                decimalSeparator: decimalSeparator,
                decimalsDigits: this.numDec,
            });
        },
        zeroFormatForOpening(number) {
            let position, spacing = false, thousandSeparator, decimalSeparator = false;
            if (this.currencyPosition === 'left') {
                position = 'LEFT';
            } else if (this.currencyPosition === 'right') {
                position = "RIGHT";
            } else if (this.currencyPosition === 'left-space') {
                position = "LEFT";
                spacing = true;
            } else {
                position = "RIGHT";
                spacing = true;
            }
            if (this.thousandSeparator === "space") {
                thousandSeparator = " "
            } else {
                thousandSeparator = this.thousandSeparator;
            }
            if (this.numDec == 0) {
                decimalSeparator = "";
            } else {
                decimalSeparator = this.decimalSeparator;
            }
            return format(number, {
                currency: this.currencySymbol,
                spacing: spacing,
                currencyPosition: position,
                thousandSeparator: thousandSeparator,
                decimalSeparator: decimalSeparator,
                decimalsDigits: this.numDec,
            });
        },
        zeroFormat(number, check) {
            if (number == "" && check == null) {
                return null;
            } else {

                let position, spacing = false, thousandSeparator, decimalSeparator = false;
                if (this.currencyPosition === 'left') {
                    position = 'LEFT';
                } else if (this.currencyPosition === 'right') {
                    position = "RIGHT";
                } else if (this.currencyPosition === 'left-space') {
                    position = "LEFT";
                    spacing = true;
                } else {
                    position = "RIGHT";
                    spacing = true;
                }
                if (this.thousandSeparator === "space") {
                    thousandSeparator = " "
                } else {
                    thousandSeparator = this.thousandSeparator;
                }
                if (this.numDec == 0) {
                    decimalSeparator = "";
                } else {
                    decimalSeparator = this.decimalSeparator;
                }
                return format(number, {
                    currency: this.currencySymbol,
                    spacing: spacing,
                    currencyPosition: position,
                    thousandSeparator: thousandSeparator,
                    decimalSeparator: decimalSeparator,
                    decimalsDigits: this.numDec,
                });
            }
        },
        dateFormats(dt) {
            return moment(dt).format(this.dateFormat);
        },

        datePickerDateFormatter(date) {

            if (date == "DD/MM/YYYY") return format('dd/MM/yyyy');
            if (date == "MM/DD/YYYY") return format('MM/dd/yyyy');
            if (date == "YYYY/MM/DD") return format('yyyy/MM/dd');

            if (date == "DD-MM-YYYY") return format('dd-MM-yyyy');
            if (date == "MM-DD-YYYY") return format('MM-dd-yyyy');
            if (date == "YYYY-MM-DD") return format('yyyy-MM-dd');

            if (date == "DD.MM.YYYY") return format('dd.MM.yyyy');
            if (date == "MM.DD.YYYY") return format('MM.dd.yyyy');
            if (date == "YYYY.MM.DD") return format('yyyy.MM.dd');
        },

        dateFormatsWithTime(time) {

            if (this.timeFormat == '12h') {
                return moment(time).format("hh:mm A");
            } else {
                return moment(time).format("HH:mm:ss");
            }
        },
        timeFormateForDatetime(time) {
            if (this.timeFormat == '12h') {
                return moment(time, "HH:mm:ss").format("hh:mm A");
            } else {
                return moment(time, "HH:mm:ss").format("HH:mm:ss");
            }
        },
        keyboardAscciValueReader(id) {
            let instance = this;
            $(id).on("keydown", function (event) {
                if (navigator.appVersion.indexOf("Win") != -1) {
                    if (event.which == 16) {
                        $(id).val('shift+');
                    } else if (event.which == 17) {
                        $(id).val('ctrl+');
                    }
                }
                if (navigator.appVersion.indexOf("Mac") != -1) {
                    if (event.which == 16) {
                        $(id).val('shift+');
                    } else if (event.which == 17) {
                        $(id).val('ctrl+');
                    }
                }
                // if (navigator.appVersion.indexOf("Linux") != -1) {
                // }
            });
        },
        capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
        globalShortcutMethod() {
            let instance = this;
            if (instance.shortcutStatus == 1) {
                instance.redirect('/sales');
            }
        },
        shortCutKeyConversion() {
            if (this.shortcutKeyInfo.loadSalesPage.status == 1) {
                if (this.shortcutKeyInfo.loadSalesPage.shortCutKey.includes("+")) return this.shortcutKeyInfo.loadSalesPage.shortCutKey.split("+");
                else return [this.shortcutKeyInfo.loadSalesPage.shortCutKey];
            }
        },
        find_duplicate_in_array(arra1) {
            let object = {},
                result = [];
            arra1.forEach(function (item) {
                if (!object[item])
                    object[item] = 0;
                object[item] += 1;
            })
            for (let prop in object) {
                if (object[prop] >= 2) {
                    result.push(prop);
                }
            }
            return result;
        },
        PrintElem(elem) {
            var mywindow = window.open('', 'PRINT', 'height=400,width=600');
            mywindow.document.write('<html><head><title>' + document.title + '</title>');
            mywindow.document.write('</head><body >');
            mywindow.document.write('<h1>' + document.title + '</h1>');
            mywindow.document.write(document.getElementById(elem).innerHTML);
            mywindow.document.write('</body></html>');
            mywindow.document.close(); // necessary for IE >= 10
            mywindow.focus(); // necessary for IE >= 10*/
            mywindow.print();
            mywindow.close();
            return true;
        },
        decimalFormat(number) {
            if (number != null) {
                return number.toString().replace(/[.]/, this.decimalSeparator);
            }
        },
        storeLocalStorage() { // sales product data
            let instance = this;
            let localStorageData = localStorage.getItem('salesProduct');
            localStorageData = JSON.parse(localStorageData);

            let postURL = '/offline-sales';
            if (localStorage.length > 0) {
                instance.axiosGETorPOST(
                    {
                        url: postURL, //set url
                        postData: localStorageData //set post data
                    },
                    (success, responseData) => {
                        if (success) {
                            localStorage.removeItem('salesProduct');
                            instance.lastInvoiceNumber = responseData.lastInvoiceNumber;
                            instance.getHoldOrders();
                            instance.showSuccessAlert(responseData.message);
                        } else {
                            instance.showErrorAlert(responseData.message);
                        }
                    },
                );
            }
        },

        /* new*/
        checkBrowser() {
            return navigator.userAgent.split(')').reverse()[0].match(/(?!Gecko|Version|[A-Za-z]+?Web[Kk]it)[A-Z][a-z]+/g)[0];
        },
        isUndefined(obj) {
            return typeof obj === "undefined";
        },
        parseToFloat(x){
            return Number.parseFloat(x).toFixed(parseInt(this.numDec));
        }
    }
};
