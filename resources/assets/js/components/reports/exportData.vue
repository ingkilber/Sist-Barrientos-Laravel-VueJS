<template>
</template>
<script>
    import XLSX from 'xlsx'

    export default {

        props: ['exportExcelShow', 'exportExcelData', 'columnHeader', 'excelFileName', 'isImport'],

        data: () => ({
            data: [],
            headers: [],
        }),
        watch: {
            exportExcelShow: function (newVal) {

                if (newVal) {
                    this.dataExport();
                }
            }
        },
        methods: {
            dataExport() {
                this.data = [];
                if (this.isImport) {
                    this.headers = this.columnHeader;
                    this.data = this.exportExcelData;
                } else {
                    let header ;
                    header = this.columnHeader.filter(function (element) {
                        return element['key'] !== "action";
                    });

                    this.exportExcelData.forEach((dataValue, dataIndex) => {
                        let object = [];

                        header.forEach((value, index) => {
                            let title = this.trans(value.title);

                            if (this.headers.indexOf(title) === -1) {
                                this.headers.push(title);
                            }
                            object[title] = dataValue[value.key];
                        });

                        this.data.push(Object.assign({}, object));
                    });
                }

                let name = this.excelFileName + '.xlsx';
                let posWS = XLSX.utils.json_to_sheet(this.data);

                //Excel width set
                let columnWidth = [];

                this.headers.forEach((value, index) => {

                    const max = this.data.reduce((prev, current) => {
                        if (prev[value] === undefined || prev[value] === null) prev[value] = '';
                        if (current[value] === undefined || current[value] === null) current[value] = '';
                        return prev[value].length > current[value].length ? prev : current
                    });

                    if (max[value] === undefined || max[value] === null) {
                        max[value] = '';
                    }

                    let maxLength = ((max[value].toString()).length > value.length) ? (max[value].toString()).length : value.length;
                    columnWidth.push(Object.assign({wch: maxLength + 2}));
                });

                posWS['!cols'] = columnWidth;
                let wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, posWS, 'POS');
                XLSX.writeFile(wb, name);
                this.$emit('resetExport', false);
            }
        }
    }
</script>
