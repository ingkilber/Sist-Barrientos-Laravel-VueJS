export default class AppFunction{

    static getBaseUrl() {
        return window.appConfig.appUrl;

    }

    static getAppUrl(path) {
        return `${this.getBaseUrl()}/${path.split('/').filter(d => d).join('/')}`;
    }

   /* static getQueryStringValue(key){
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(key);
    }

    static backToPreviousUrl(){
        history.back();
    }
*/
    static goToNextUrl(){
        history.forward();
    }
}
