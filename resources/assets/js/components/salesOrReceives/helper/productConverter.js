export const productConverter = (products, product_variants) => {
    


    return products.map((product) => {
    
        product.attributeName = typeof product.attributeName === 'string' ? product.attributeName.split(','): product.attributeName;
    
        let variants = product_variants.filter((variant) => {
            if (product.productID === variant.product_id) {
                variant.attribute_values = typeof variant.variant_title === 'string' ? variant.variant_title.split(','): [];
                variant.attributeName = product.attributeName;
                return true;
            }
            return false;
        });

        
        product.variants = variants;
        
        return product;
    });

};

export const concatProductArray = (responses) => {
    let products = [];
    for(let response of responses) {
       products.push(productConverter(response.data.products, response.data.variants));
    }
    return products.flat();
};