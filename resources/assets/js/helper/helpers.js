import Vue from 'vue'
import moment from "moment";
import Collection from "./Collection";
import Permission from "./Permission";

export const optional = (obj, ...props) => {
    if(!obj || typeof obj !== 'object')
        return undefined;
    const val = obj[props[0]];
    if(props.length === 1 || !val) return val;
    const rest = props.slice(1);
    return optional.apply(null, [val, ...rest])
};

Vue.prototype.$optional = (obj, ...props) => {
    return optional(obj, ...props);
};

Vue.prototype.$errorMessage = (errorObject, field, isArray = true) => {
    if (!Object.keys(errorObject).length)
        return '';
    if (isArray){
        let error = errorObject[field]
        if (error){
            return error[0];
        }
        return '';
    }
    return  errorObject[field];
};

export const configFormatter = (format) => {
    return {
        id: format,
        value: Vue.prototype.$t(format)
    }
};

export const textTruncate = (str, length, ending) => {
    if (length == null) {
        length = 50;
    }
    if (ending == null) {
        ending = '...';
    }
    if (str.length > length) {
        return str.substring(0, length - ending.length) + ending;
    } else {
        return str;
    }
};

export const formDataAssigner = function (formData = new FormData, dataObject) {
    Object.keys(dataObject).map((key) => {
        if (dataObject[key] && !dataObject[key].length > 0 && Object.keys(dataObject[key]).length > 0) {
            Object.keys(dataObject[key]).map(childKey => {
                return formData.append(key+`[${childKey}]`, dataObject[key][childKey]);
            })
        }else if (Array.isArray(dataObject[key])) {
            dataObject[key].map((el, index) => {
                Object.keys(el).map(objectKeys => {
                    formData.append(key+`[${index}][${objectKeys}]`, el[objectKeys]);
                });
            })
        }else{
            return formData.append(key, dataObject[key]);
        }
    });
    return formData;
};

export const date_format = () => {
    return {
        'd-m-Y': 'DD-MM-YYYY',
        'm-d-Y': 'MM-DD-YYYY',
        'Y-m-d': 'YYYY-MM-DD',
        'm/d/Y': 'MM/DD/YYYY',
        'd/m/Y': 'DD/MM/YYYY',
        'Y/m/d': 'YYYY/MM/DD',
        'm.d.Y': 'MM.DD.YYYY',
        'd.m.Y': 'DD.MM.YYYY',
        'Y.m.d': 'YYYY.MM.DD',
    };
};

export const formatted_date = () => {
    return date_format()[optional(window.settings, 'date_format')];
};

export const formatted_time = () => {
    return optional(window.settings, 'time_format') === 'h' ? '12' : '24';
}

export const time_format = () => {
    const format = optional(window.settings, 'time_format');

    return format === 'h' ? `${window.settings.time_format}:mm A` : `${window.settings.time_format}:mm`;
}

export const formatDateToLocal = (date, withTime = false) => {
    if (!date)
        return '';
    const formatString = withTime ? `${formatted_date()} ${time_format()}` : formatted_date();

    return  moment(date).utc(false)
        .local()
        .format(formatString);
};

export const timeInterval = (date) => {
    return moment(date).utc(false).fromNow();
};

export const onlyTime = date => {
    return  moment(date).utc(false)
        .local()
        .format(time_format());
};

export const formatUtcToLocal = (time, format = false) => {
    return moment.utc(time, 'HH:mm:ss').local().format(format || `${window.settings.time_format}:mm A`);
}

export const isValidDate = string => {
    if (!string)
        return false;

    if (typeof parseInt(string) === 'number' && string.split('-').length <= 1)
        return false;

    return !isNaN(new Date().getTime());
}

export const calenderTime = date => {
    const days = moment(date).diff(moment.now(), 'days');
    if (moment(date).format('YYYY') < moment().format('YYYY')) {
        return  moment(date).format('DD MMM, YY')
    }
    if (days < -7) {
        return  moment(date).format('DD MMM')
    }

    moment.updateLocale(window.appLanguage, {})

    return moment(formatDateToLocal(date, true), formatted_date()).calendar({
        sameDay: '['+Vue.prototype.$t('today')+']',
        lastDay: '['+Vue.prototype.$t('yesterday')+']',
        lastWeek: '['+Vue.prototype.$t('last')+'] dddd',
        nextDay: '['+Vue.prototype.$t('tomorrow')+']',
        nextWeek: '['+Vue.prototype.$t('next_week')+']',
        sameElse: `${date_format()[window.settings.date_format]}`
    });
};

export const getThousandSeparator = () => {
    return window.settings.thousand_separator ? window.settings.thousand_separator : ' ';
}

export const getNumberOfDecimal = () => {
    return window.settings.number_of_decimal ? window.settings.number_of_decimal : 0
}

export const numberFormatter = number => {
    if (number && (String(number).indexOf('.') > -1)) {
        number = number.toFixed(getNumberOfDecimal())
    }
    if (!isNaN(parseFloat(number))) {
        let parts = number.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, getThousandSeparator());
        return parts.join(".");
    }
    return 0;
}

export const addChooseInSelectArray = (array, valueField = 'name', label = '') => {
    let obj = {id: '', disabled: true};
    obj[valueField] = Vue.prototype.$t('choose', {field: Vue.prototype.$t(label).toLowerCase()})

    return [obj].concat(array);
}

export const dropZoneErrorGenerator = (errors, file_name = 'attachments') => {
    const file_errors = Object.keys(errors).filter(key => {
        return String(key).includes(file_name);
    });
    let first_part_of_error = '';
    let last_part_of_error = '';
    const message = file_errors.map(key => {
        const errorArray = errors[key][0].split(key)
        first_part_of_error = errorArray[0];
        last_part_of_error = errorArray[1];
        return key.split('.').join(' ');
    }).join(', ')

    return `${first_part_of_error} ${message} ${last_part_of_error}`
}

export const collection = list => new Collection(list);

Vue.prototype.collection = list => collection(list);

export const studly = string => {
    string = String(string).replace('-', ' ');
    string = string.replace('_', ' ');
    return string.split(' ')
        .map(str => str[0].toUpperCase()+str.substr(1).toLowerCase())
        .join('')
}

export const textEditorHints = tags => {
    return {
        words: tags,
        match: /\B{(\w*)$/,
        search: function (keyword, callback) {
            callback($.grep(this.words, function (item) {
                return item.includes(keyword);
            }));
        }
    }
}

Vue.prototype.$can = ability => (new Permission()).can(ability);

export const ucWords = string => {
    return String(string).toLowerCase()
        .replace(/\b[a-z]/g, (l) => l.toUpperCase())
}

export const  columnStringify = string => {
    if (string) {
        return ucWords(String(string).split('_').join(' '))
    }
}
export const ucFirst = string => {
    if (string) {
        return String(string)[0].toUpperCase() + String(string).substring(1)
    }
}
