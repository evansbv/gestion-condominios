<?php

namespace App\Services;

use App\Models\Comunicacion;
use App\Models\Reunion;
use App\Models\Aporte;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Enviar convocatoria a reunión
     */
    public function enviarConvocatoria(Reunion $reunion, array $destinatarios)
    {
        try {
            foreach ($destinatarios as $email) {
                Mail::raw($this->generarContenidoConvocatoria($reunion), function ($message) use ($email, $reunion) {
                    $message->to($email)
                        ->subject("Convocatoria: {$reunion->titulo}")
                        ->from(config('mail.from.address'), config('app.name'));
                });
            }

            return [
                'success' => true,
                'message' => 'Convocatoria enviada correctamente',
                'destinatarios' => count($destinatarios)
            ];
        } catch (\Exception $e) {
            Log::error('Error al enviar convocatoria: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar convocatoria: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar comunicación
     */
    public function enviarComunicacion(Comunicacion $comunicacion, array $destinatarios)
    {
        try {
            foreach ($destinatarios as $email) {
                Mail::raw($comunicacion->contenido, function ($message) use ($email, $comunicacion) {
                    $message->to($email)
                        ->subject($comunicacion->asunto)
                        ->from(config('mail.from.address'), config('app.name'));
                });
            }

            $comunicacion->enviado_por_email = true;
            $comunicacion->save();

            return [
                'success' => true,
                'message' => 'Comunicación enviada correctamente',
                'destinatarios' => count($destinatarios)
            ];
        } catch (\Exception $e) {
            Log::error('Error al enviar comunicación: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar comunicación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar notificación de pago pendiente
     */
    public function enviarNotificacionPago(Aporte $aporte, string $email)
    {
        try {
            $contenido = $this->generarContenidoNotificacionPago($aporte);

            Mail::raw($contenido, function ($message) use ($email) {
                $message->to($email)
                    ->subject('Recordatorio de Pago - Condominio')
                    ->from(config('mail.from.address'), config('app.name'));
            });

            return [
                'success' => true,
                'message' => 'Notificación de pago enviada correctamente'
            ];
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación de pago: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar notificación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar notificaciones masivas de pagos vencidos
     */
    public function enviarNotificacionesPagosVencidos()
    {
        $aportesVencidos = Aporte::with(['vivienda.residentes.user'])
            ->whereIn('estado', ['VENCIDO', 'PARCIAL'])
            ->get();

        $enviados = 0;
        $errores = 0;

        foreach ($aportesVencidos as $aporte) {
            $propietario = $aporte->vivienda->propietario;

            if ($propietario && $propietario->user) {
                $resultado = $this->enviarNotificacionPago($aporte, $propietario->user->email);

                if ($resultado['success']) {
                    $enviados++;
                } else {
                    $errores++;
                }
            }
        }

        return [
            'success' => true,
            'message' => "Notificaciones enviadas: {$enviados}, Errores: {$errores}",
            'enviados' => $enviados,
            'errores' => $errores
        ];
    }

    /**
     * Generar contenido de convocatoria
     */
    private function generarContenidoConvocatoria(Reunion $reunion)
    {
        return "CONVOCATORIA A REUNIÓN\n\n" .
            "Título: {$reunion->titulo}\n" .
            "Fecha: {$reunion->fecha_reunion->format('d/m/Y H:i')}\n" .
            "Lugar: {$reunion->lugar}\n\n" .
            "Descripción:\n{$reunion->descripcion}\n\n" .
            "Orden del día:\n{$reunion->orden_dia}\n\n" .
            "Su asistencia es importante.\n\n" .
            "Atentamente,\n" .
            "Administración del Condominio";
    }

    /**
     * Generar contenido de notificación de pago
     */
    private function generarContenidoNotificacionPago(Aporte $aporte)
    {
        $aporte->load(['actividad', 'vivienda']);

        $contenido = "RECORDATORIO DE PAGO\n\n" .
            "Estimado residente,\n\n" .
            "Le recordamos que tiene un aporte pendiente de pago:\n\n" .
            "Concepto: {$aporte->actividad->titulo}\n" .
            "Vivienda: {$aporte->vivienda->numero}\n" .
            "Monto: Bs. " . number_format($aporte->monto, 2) . "\n" .
            "Fecha de vencimiento: {$aporte->fecha_vencimiento->format('d/m/Y')}\n";

        if ($aporte->monto_mora > 0) {
            $contenido .= "Mora acumulada: Bs. " . number_format($aporte->monto_mora, 2) . "\n";
            $contenido .= "Total a pagar: Bs. " . number_format($aporte->saldo_pendiente, 2) . "\n";
        }

        $contenido .= "\nPor favor, realice su pago a la brevedad posible.\n\n" .
            "Atentamente,\n" .
            "Administración del Condominio";

        return $contenido;
    }
}
