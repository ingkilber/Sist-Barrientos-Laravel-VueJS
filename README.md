# Sistema de Gestión de Inventario y Ventas

# 🚀 Tecnología
Laravel + vue / aplicación basada en POS!

# Home - Iniciar Sesión

![Home](https://github.com/ingkilber/Sist-Barrientos-Laravel-VueJS/blob/main/public/images/Capture/inicio.png)

# Panel de Admistración

![dashboard](https://github.com/ingkilber/Sist-Barrientos-Laravel-VueJS/blob/main/public/images/Capture/Panel-de-Administracion.png)

![dashboard](https://github.com/ingkilber/Sist-Barrientos-Laravel-VueJS/blob/main/public/images/Capture/Panel-de-Administracion2.png)

## ✋🏻 Cómo ejecutar la aplicación

* Cambie el nombre del archivo .env.example a .env dentro de la raíz de su proyecto o (comando mac terminal cd el directorio raíz de su proyecto y ejecute - "cp .env.example .env" usted obtiene .env file) y complete la información de la base de datos.
* Abra la consola y cd el directorio raíz de su proyecto
* Run `composer install` o `php composer.phar install` (si el directorio de proveedores no está disponible )
* Run `php artisan key:generate`
* Ahora abra el directorio del proyecto en la raíz, busque el `.env` expediente. En el archivo .env complete el `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, y `DB_PASSWORD` opciones para que coincidan con las credenciales de la base de datos que acaba de crear. Esto nos permitirá ejecutar migraciones y sembrar la base de datos en el siguiente paso.
* Run `php artisan migrate`
* Run `php artisan db:demo` para ejecutar seeders.

##Iniciar sesión, admin user/pass:
* login url: 127.0.0.1:8000 (Puede Variar)
* Email: admin@admin.com
* Pass: 123456

## 🔥 Configuración completa
* Llegué al menú de configuració
* Configuración de la aplicación: configure todos los campos como desee
* Configuración de correo electrónico: establezca el nombre de la aplicación como prefiera.
  
  - Establezca su dirección de correo electrónico como correo electrónico [N.B: acceda a su dirección de correo electrónico para esta aplicación desde su correo electrónico si es necesario].
  
  - Seleccione el controlador de correo electrónico. Si SMTP:- 
  
    - Aquí, configuramos el controlador como smtp, el host para gmail como smtp.googlemail.com, el puerto smtp para gmail como 587 y el método de encriptación para ssl. Asegúrate de haber reemplazado tu nombre de usuario y contraseña de Gmail. A continuación, debe realizar algunos cambios en la configuración de Gmail. Inicie sesión en su cuenta de Google y haga clic en Mi cuenta. Una vez que esté en la página Mi cuenta, haga clic en Inicio de sesión y seguridad. En la página siguiente, desplácese hacia abajo y encontrará la configuración "Permitir aplicaciones menos seguras". Establézcalo en ENCENDIDO
    
    - escriba el nombre del host de correo electrónico. [ejemplo: para gmail `smtp.gmail.com`]
  
    - Escriba el número de puerto: - [ejemplo: para el puerto de Gmail `587`]
    
    - Escriba la contraseña: la contraseña de ese correo electrónico se utiliza como dirección de correo electrónico.

    - Seleccione el tipo de cifrado: - Puerto `465` (requiere SSL), Puerto `587` (requiere TLS).
                                                
**K.M:-** ✋🏻 Tenga cuidado con la `Configuración de correo electrónico`, que es necesaria para enviar notificaciones automáticas por correo electrónico. Si no completa la `Configuración de correo electrónico`, algunas funciones no funcionarán correctamente.

Otros ajustes establecidos según sus necesidades, para cualquier tipo de soporte contáctame 👍 
