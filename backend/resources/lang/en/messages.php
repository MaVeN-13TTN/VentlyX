<?php

return [
    // General
    'success' => 'Success!',
    'error' => 'Error!',
    'warning' => 'Warning!',
    'info' => 'Information',
    
    // Authentication
    'auth' => [
        'login_success' => 'Successfully logged in',
        'login_failed' => 'Invalid login credentials',
        'logout_success' => 'Successfully logged out',
        'register_success' => 'Registration successful',
        'password_reset_sent' => 'Password reset link has been sent to your email address',
        'password_reset_success' => 'Password has been successfully reset',
        'unauthorized' => 'Unauthorized access',
        'two_factor_enabled' => 'Two-factor authentication enabled successfully',
        'two_factor_disabled' => 'Two-factor authentication disabled successfully',
    ],
    
    // Events
    'events' => [
        'created' => 'Event created successfully',
        'updated' => 'Event updated successfully',
        'deleted' => 'Event deleted successfully',
        'not_found' => 'Event not found',
        'featured_toggled' => 'Event featured status updated',
    ],
    
    // Bookings
    'bookings' => [
        'created' => 'Booking created successfully',
        'cancelled' => 'Booking cancelled successfully',
        'not_found' => 'Booking not found',
        'insufficient_tickets' => 'Not enough tickets available',
        'max_tickets_exceeded' => 'Maximum tickets per order exceeded',
        'event_ended' => 'Cannot book tickets for an event that has already ended',
    ],
    
    // Tickets
    'tickets' => [
        'checked_in' => 'Ticket checked in successfully',
        'already_checked_in' => 'Ticket has already been checked in',
        'check_in_reverted' => 'Check-in successfully reverted',
    ],
    
    // Payments
    'payments' => [
        'success' => 'Payment processed successfully',
        'failed' => 'Payment processing failed',
        'refunded' => 'Payment refunded successfully',
    ],
    
    // Discount Codes
    'discount_codes' => [
        'created' => 'Discount code created successfully',
        'updated' => 'Discount code updated successfully',
        'deleted' => 'Discount code deleted successfully',
        'applied' => 'Discount code applied successfully',
        'invalid' => 'Invalid or expired discount code',
    ],
    
    // Users
    'users' => [
        'created' => 'User created successfully',
        'updated' => 'User updated successfully',
        'deleted' => 'User deleted successfully',
        'not_found' => 'User not found',
    ],
    
    // Notifications
    'notifications' => [
        'marked_read' => 'Notification marked as read',
        'all_marked_read' => 'All notifications marked as read',
        'deleted' => 'Notification deleted',
        'all_deleted' => 'All notifications deleted',
    ],
    
    // Offline
    'offline' => [
        'package_generated' => 'Offline package generated successfully',
        'sync_success' => 'Offline data synced successfully',
    ],
    
    // Exports
    'exports' => [
        'generated' => 'Export generated successfully',
    ],
];