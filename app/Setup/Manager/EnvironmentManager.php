<?php


namespace App\Setup\Manager;


use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnvironmentManager
{

    public function copyEnv()
    {
        if (!file_exists($this->getEnvPath())) {
            return copy(base_path('.env.example'), $this->getEnvPath());
        }
        return true;
    }


    public function saveFileWizard(Request $request)
    {
        $envFileData =
            /*App*/
            'APP_NAME=\''.$request->get('app_name', config('app.name'))."'\n".
            'APP_ENV='.$request->get('environment', 'production')."\n".
            'APP_KEY='.'base64:'.base64_encode(Str::random(32))."\n".
            'APP_DEBUG='.$request->get('app_debug', 'false')."\n".
            'PURCHASED_CODE='.$request->get('code')."\n".
            'APP_URL='.$request->get('app_url', $request->root())."\n\n".
            /*Database*/
            'DB_CONNECTION='.$request->database_connection."\n".
            'DB_HOST='.$request->database_hostname."\n".
            'DB_PORT='.$request->database_port."\n".
            'DB_DATABASE='.$request->database_name."\n".
            'DB_USERNAME='.$request->database_username."\n".
            'DB_PASSWORD='.$request->database_password."\n\n".
            /*Driver*/
            'LOG_CHANNEL='.$request->get('log_channel', 'stack')."\n\n".
            'BROADCAST_DRIVER='.$request->get('broadcast_driver', 'log')."\n".
            'CACHE_DRIVER='.$request->get('cache_driver', 'file')."\n".
            'QUEUE_CONNECTION='.$request->get('queue_connection', 'sync')."\n".
            'SESSION_DRIVER='.$request->get('session_driver', 'file')."\n".
            'SESSION_LIFETIME='.$request->get('session_lifetime', '120')."\n".
            /*Redis*/
            'REDIS_HOST='.$request->get('redis_hostname', '127.0.0.1')."\n".
            'REDIS_PASSWORD='.$request->get('redis_password', 'null')."\n".
            'REDIS_PORT='.$request->get('redis_port', '6379')."\n\n".
            /*Mail*/
            'MAIL_DRIVER='.$request->get('mail_driver', 'smtp')."\n".
            'MAIL_HOST='.$request->get('mail_host', 'smtp.mailtrap.io')."\n".
            'MAIL_PORT='.$request->get('mail_port', '2525')."\n\n".
            'MAIL_USERNAME='.$request->get('mail_user_name', 'null')."\n".
            'MAIL_PASSWORD='.$request->get('mail_password', 'null')."\n".
            'MAIL_ENCRYPTION='.$request->get('mail_encryption', 'null')."\n\n\n".
            /*Pusher*/
            'PUSHER_APP_ID='.$request->get('pusher_app_id', 'null')."\n".
            'PUSHER_APP_KEY='.$request->get('pusher_app_key', 'null')."\n".
            'PUSHER_APP_SECRET='.$request->get('pusher_app_secret', 'null')."\n".
            'PUSHER_APP_CLUSTER='.$request->get('pusher_app_cluster', 'mt1')."\n\n".
            'MIX_PUSHER_APP_KEY='.$request->get('pusher_app_cluster', '"${PUSHER_APP_KEY}"')."\n\n".
            'MIX_PUSHER_APP_CLUSTER='.$request->get('mail_host', '"${PUSHER_APP_CLUSTER}"')."\n";

        if ($this->copyEnv()) {
            return file_put_contents($this->getEnvPath(), $envFileData);
        }
    }

    public function setEnvironmentValue($envKey, $envValue)
    {
        $value = strtok(file_get_contents($this->getEnvPath(), "$envKey="));

        if (gettype($value) == 'boolean') {
            $value = $value ? 'true' : 'false';
        }

        file_put_contents($this->getEnvPath(), str_replace(
            $envKey.'='.$value, $envKey.'='.$envValue, file_get_contents($this->getEnvPath())
        ));

        return true;
    }



    public function getEnvPath()
    {
        return base_path('.env');
    }
}
