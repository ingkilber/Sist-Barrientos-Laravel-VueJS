import axios from 'axios'

export const range = (start, end) => {
    const length = (end - start) + 1;
    return Array.from({ length }, (_, i) => start + i);
};

export const productRequestGenerator = (totalProducts, data) => {
    const limit = 1800;
    const steps = Math.ceil(totalProducts / limit);
    
    return Promise.all(range(1, steps).map((step, index) => {
        return axios.get(
            `${window.appConfig.appUrl}/get-sales-product/${limit}/${(limit * index)}?currentBranch=${data.currentBranch}&orderType=${data.orderType}`,
        )
    }));
};