let delayTimer;
export const delayCall = function(callback) {
    clearTimeout(delayTimer);
    delayTimer = setTimeout(function() {
        callback();
    }, 400);
}