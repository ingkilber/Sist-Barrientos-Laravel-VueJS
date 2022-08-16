import  axios from "axios";

export const axiosGETorPOST = (options, callback ) => {

    let axiosCall;

    if(options.postData) //if post method
    {
        axiosCall = axios.post(options.url,options.postData)
    }
    else //else get method
    {
        axiosCall = axios.get(options.url)
    }
    axiosCall.then(response => {
        if(callback)  callback(true, response.data); //returns response data

    }).catch(({response}) => {
        if(callback) callback(false, response.data); //returns error response data
    });

};


