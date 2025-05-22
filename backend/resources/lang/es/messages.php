<?php

return [
    // General
    'success' => '¡Éxito!',
    'error' => '¡Error!',
    'warning' => '¡Advertencia!',
    'info' => 'Información',
    
    // Authentication
    'auth' => [
        'login_success' => 'Inicio de sesión exitoso',
        'login_failed' => 'Credenciales de inicio de sesión inválidas',
        'logout_success' => 'Cierre de sesión exitoso',
        'register_success' => 'Registro exitoso',
        'password_reset_sent' => 'Se ha enviado un enlace para restablecer la contraseña a su dirección de correo electrónico',
        'password_reset_success' => 'La contraseña se ha restablecido con éxito',
        'unauthorized' => 'Acceso no autorizado',
        'two_factor_enabled' => 'Autenticación de dos factores habilitada con éxito',
        'two_factor_disabled' => 'Autenticación de dos factores deshabilitada con éxito',
    ],
    
    // Events
    'events' => [
        'created' => 'Evento creado con éxito',
        'updated' => 'Evento actualizado con éxito',
        'deleted' => 'Evento eliminado con éxito',
        'not_found' => 'Evento no encontrado',
        'featured_toggled' => 'Estado destacado del evento actualizado',
    ],
    
    // Bookings
    'bookings' => [
        'created' => 'Reserva creada con éxito',
        'cancelled' => 'Reserva cancelada con éxito',
        'not_found' => 'Reserva no encontrada',
        'insufficient_tickets' => 'No hay suficientes entradas disponibles',
        'max_tickets_exceeded' => 'Se ha excedido el máximo de entradas por pedido',
        'event_ended' => 'No se pueden reservar entradas para un evento que ya ha finalizado',
    ],
    
    // Tickets
    'tickets' => [
        'checked_in' => 'Entrada registrada con éxito',
        'already_checked_in' => 'La entrada ya ha sido registrada',
        'check_in_reverted' => 'Registro de entrada revertido con éxito',
    ],
    
    // Payments
    'payments' => [
        'success' => 'Pago procesado con éxito',
        'failed' => 'Error al procesar el pago',
        'refunded' => 'Pago reembolsado con éxito',
    ],
    
    // Discount Codes
    'discount_codes' => [
        'created' => 'Código de descuento creado con éxito',
        'updated' => 'Código de descuento actualizado con éxito',
        'deleted' => 'Código de descuento eliminado con éxito',
        'applied' => 'Código de descuento aplicado con éxito',
        'invalid' => 'Código de descuento inválido o caducado',
    ],
    
    // Users
    'users' => [
        'created' => 'Usuario creado con éxito',
        'updated' => 'Usuario actualizado con éxito',
        'deleted' => 'Usuario eliminado con éxito',
        'not_found' => 'Usuario no encontrado',
    ],
    
    // Notifications
    'notifications' => [
        'marked_read' => 'Notificación marcada como leída',
        'all_marked_read' => 'Todas las notificaciones marcadas como leídas',
        'deleted' => 'Notificación eliminada',
        'all_deleted' => 'Todas las notificaciones eliminadas',
    ],
    
    // Offline
    'offline' => [
        'package_generated' => 'Paquete sin conexión generado con éxito',
        'sync_success' => 'Datos sin conexión sincronizados con éxito',
    ],
    
    // Exports
    'exports' => [
        'generated' => 'Exportación generada con éxito',
    ],
];