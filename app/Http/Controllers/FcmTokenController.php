<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use App\Models\FcmToken;
// use Google\Client as ;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
// use CloudMessage;


class FcmTokenController extends Controller
{
    private $factory;
    private $cloudMessaging;


    /**
     * 
     * 
     * Paquete de Firebase
     * composer require kreait/firebase-php
     * 
     * Hazlo todo con Fireabase, google lanzo el APN Manager para iOS el año pasado y permite gestionar tambien iOS
     * entonces todo bien ahí.
     * 
     * Debemos crear la instancia con el creador de instancias (factory pattern)
     * Debemos cargar el archivo de configuracion con la ruta absoluta de donde este (importante)
     * Cargamos el Cloud Message
     */
    public function __construct()
    {
        $this->factory = (new Factory)
            ->withServiceAccount('RUTAALARCHIVOCONFIG.json');

        $this->cloudMessaging = $this->factory->createMessaging();
    }


    /**
     * 
     * 
     * El token siempre lo enviaré como fcm-token en el get o en el body.
     * Debe de tener estar incluido en:
     * * Una ruta de inicio (puede ser esta)
     * * La ruta de login
     * * La ruta de registro
     * 
     * Para probar notificaciones, primero abre la aplicación y cierrala. 
     */
    public function store(Request $request)
    {
        $fcmToken = $request->get('fcm-token');
        if (empty($fcmToken)) {
            return response()->json(['message' => 'Token de Firebase no proporcionado.'], 400);
        }
        FcmToken::updateOrCreate(
            ['token' => $fcmToken]
        );

        return response()->json(['message' => 'Token registrado con éxito.']);
    }


    /**
     * Metodo que envia una notificacion. Se debe de indicar el token en cuestión del dispositivo
     */
    public function sendNotifications()
    {
        $tokens = FcmToken::pluck('token')->toArray();

        $message = CloudMessage::withTarget('token', $tokens[0])
            ->withNotification(Notification::create('Nombre de la Notificación', 'Descripción'))
            ->withData(['key' => 'value']);

        dd($this->cloudMessaging->send($message));
    }

    /**
     *  Es necesario validar tokens ocasionalmente para poder eliminarlos y no llenar la BD de basura
     *  Al validar devuelve un arreglo con los tokens validados.
     *  Se hace normalmente al iniciar sesion o al registrarse (el token te lo enviare en esos metodos)
     */
    public function validateTokens()
    {
        $tokens = FcmToken::pluck('token')->toArray();
        $result = $this->cloudMessaging->validateRegistrationTokens($tokens);
        dd($result);
    }

    /**
     * Metodo que envia una notificacion. Se debe de indicar el token en cuestión.
     */
    public function sendMultipleNotificationsTokens()
    {
        $tokens = FcmToken::pluck('token')->toArray();
        $sendReport = $this->cloudMessaging->sendAll([
            CloudMessage::withTarget('token', $tokens[0])
                ->withNotification(Notification::create('Nombre de la Notificación', 'Descripción'))
                ->withData(['key' => 'value'])
        ]);
        dd($sendReport);
    }

    /*****  TOPICOS  ******/
    /**
     * Los topicos son como listas de tokens. El enviar una notificacion a un topico, 
     * se enviará todo directamente.
     */

    /**
     * Agrega un token a un Topico
     * Aqui realmente eres libre de dedicir cuantos topicos y a quienes puedes agregar a esos 
     * tokens.
     * 
     * Puedes subscribir los tokens a listas de marketing o a listas de lo que necesites
     * 
     * (por ejemplo, cuando vaya a recibir notificaciones de su auto, neecsitas subscribirlo a la lista del auto, tipo
     * 
     * El topico auto_id_25 esta el iphone del dueño del carro, y la esposa (asociados a la misma cuenta)
     * El topico 
     */
    public function addTokenToTopic()
    {
        // El token de registro del dispositivo que quieres suscribir
        // $registrationToken = 'auto_test_id_1';
        $token = FcmToken::pluck('token')->first();
        // El nombre del tópico al cual quieres suscribir el token
        $topic = 'auto_id_25';

        // Suscribir el token al tópico
        $result = $this->cloudMessaging->subscribeToTopic($topic, $token);

        dd($result);
    }


    /**
     * Metodo que envia una notificacion. Se debe de indicar el token en cuestión.
     */
    public function sendToTopic()
    {
        // $tokens = FcmToken::pluck('token')->toArray();
        $topic = 'auto_id_25';

        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create('Nombre de la Notificación', 'Descripción'))
            ->withData(['key' => 'value']);

        dd($this->cloudMessaging->send($message));
    }

    /**
     * Metodo que envia una notificacion. Se debe de indicar el token en cuestión.
     */
    public function removeTokenFromTopic()
    {
        // El token de registro del dispositivo que quieres suscribir
        // $registrationToken = 'auto_test_id_1';
        $token = FcmToken::pluck('token')->first();
        // El nombre del tópico al cual quieres suscribir el token
        $topic = 'auto_id_25';

        // Suscribir el token al tópico
        $result = $this->cloudMessaging->unsubscribeFromTopic($topic, $token);

        dd($result);
    }


    public function sendNotificationsBasedOnConditions()
    {
        $condition = "'stock-GOOG' in topics && 'industry-tech' in topics";

        $message = CloudMessage::fromArray([
            'condition' => $condition,
            'notification' => [
                'title' => 'Test',
                'body' => 'Test',
            ],
        ]);
        dd($this->cloudMessaging->send($message));
    }
}
