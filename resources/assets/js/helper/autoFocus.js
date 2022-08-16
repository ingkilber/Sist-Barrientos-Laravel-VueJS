function autofocus(){

            setTimeout(
                function(){
                    $('form:first *:input[type=text]:first').focus();
                },200
            )
        }

export {autofocus}
